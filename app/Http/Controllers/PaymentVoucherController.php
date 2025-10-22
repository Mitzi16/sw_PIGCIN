<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Http\Requests\StorePaymentVoucherRequest;
use App\Http\Requests\UpdatePaymentVoucherRequest;
use App\Http\Requests\ValidatePaymentVoucherRequest;
use App\Models\Article;
use App\Models\File;
use App\Models\PaymentVoucherStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class PaymentVoucherController extends Controller
{
    private PaymentVoucher $model;
    private string $source;
    private string $routeName;
    private string $module = 'paymentVoucher';
    private string $storage_path = 'paymentVouchers';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'PaymentVoucher/';
        $this->model = new PaymentVoucher();
        $this->routeName = 'paymentVoucher.';

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
        $this->middleware("permission:{$this->module}.validate")->only(['showValidate', 'handleValidate']);

        $this->authorizeResource(PaymentVoucher::class, $this->module);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';
        $query = Article::query()->with('paymentVoucher.paymentVoucherStatus');
        $user = User::findOrFail(Auth::id());

        $query->where('article_status_id', 4); // solo articulos aceptados
        if (!$user->canPermission('paymentVoucher.validate')) {
            $query->where('postulant_id', Auth::id());
        }

        $query->when($request->input('search'), function ($query, $search) {
            if ($search != '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('article_folio', 'LIKE', '%' . $search . '%')
                        ->orWhere('type', 'LIKE', '%' . $search . '%')
                        ->orWhere('abstract', 'LIKE', '%' . $search . '%')
                        ->orWhere('key_works', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('knowledgeArea', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        })
                        ->orWhereHas('editor', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        })
                        ->orWhereHas('articleStatus', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }
        });

        $articles = $query->paginate(8)->withQueryString()->through(
            fn ($article) => [
                'id' => $article->id,
                'article_folio' => $article->article_folio,
                'title' => $article->title,
                'editor' => $article->editor->name,
                'status' => $article->articleStatus,
                'paymentVoucher' => $article->paymentVoucher ?? null
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'articles'          =>  $articles,
            'title'             => 'Gestión de pagos y comprobantes',
            'routeName'         => $this->routeName,
            'search'            => $request->search ?? '',
            'direction'         => $direction,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Article $article)
    {
        $this->authorize('canStorePaymentVoucher', $article);
        return Inertia::render("{$this->source}Create", [
            'title'      => 'Cargar comprobante de pago',
            'article'    => $article->load('articleType', 'articleStatus', 'editor:id,name', 'postulant.institution.country', 'postulant.institution.state'),
            'routeName'  => $this->routeName,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentVoucherRequest $request)
    {
       try {
            // 1. Encontrar el artículo
            $article = Article::findOrFail($request->article_id);
            $this->authorize('canStorePaymentVoucher', $article);

            // 2. Actualizar el Artículo con sus datos
            $article->update([
                'references' => $request->input('references'), // <-- ¡AQUÍ SE GUARDA!
            ]);

            $this->handleEditableFile($request, $article); // <-- ¡NUEVO!

            // 4. Preparar y crear el PaymentVoucher
            $fields = $request->validated();
            
            // --- Es más seguro ser explícito ---
            $voucherData = [
                'reference'         => $fields['reference'],
                'amount'            => $fields['amount'],
                'requires_invoice'  => ($fields['requires_invoice'] ?? 'no') === 'yes' ? 1 : 0,
                'rfc'               => $fields['rfc'] ?? null,
                'direccion_fiscal'  => $fields['direccion_fiscal'] ?? null,
                'regimen_fiscal'    => $fields['regimen_fiscal'] ?? null,
                'uso_cfdi'          => $fields['uso_cfdi'] ?? null,
                'payment_voucher_status_id' => 1, // enviado a validar
                'user_id'           => Auth::id(),
            ];
            
            $paymentVoucher = PaymentVoucher::create($voucherData); // <-- Más seguro
            // --- Fin de la creación segura ---

            // 5. Vincular el voucher al artículo
            $article->update(['payment_voucher_id' => $paymentVoucher->id]);

            // 6. Manejar los archivos del voucher
            $this->handleFileVoucher($request, $paymentVoucher, $article);
            $this->handleFileConstancia($request, $paymentVoucher, $article);

            return redirect()->route("{$this->routeName}index")->with('success', 'Comprobante generado con éxito');
        } catch (\Exception $e) {
            Log::error('Error en guardar comprobante: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Ocurrió un error al cargar el comprobante.');
            //dd($e); 
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        // **CORRECCIÓN: Cargar la relación 'constancia'**
        $paymentVoucher->load(
            'article.postulant.institution.country',
            'article.postulant.institution.state',
            'article.editor',
            'article.articleStatus',
            'article.articleType',
            'paymentVoucherStatus',
            'file',
            'constancia' // <-- Cargar relación
        );

        return Inertia::render("{$this->source}Show", [
            'title'                  => 'Visualizar comprobante de pago',
            'paymentVoucher'         => $paymentVoucher,
            'filePath'               => $paymentVoucher->file ? $this->getFile($paymentVoucher->file) : null,
            // **CORRECCIÓN: Pasar la URL de la constancia a la vista**
            'constanciaPath'         => $paymentVoucher->constancia ? $this->getFile($paymentVoucher->constancia) : null,
            'paymentVoucherStatuses' => PaymentVoucherStatus::all(),
            'routeName'              => $this->routeName,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        // **CORRECCIÓN: Cargar la relación 'constancia'**
        $paymentVoucher->load('article.editor', 'article.articleStatus', 'paymentVoucherStatus', 'file', 'constancia');

        return Inertia::render("{$this->source}Edit", [
            'title'          => 'Editar comprobante de pago',
            'paymentVoucher' => $paymentVoucher,
            'filePath'       => $paymentVoucher->file ? $this->getFile($paymentVoucher->file) : null,
            // **CORRECCIÓN: Pasar la URL de la constancia a la vista**
            'constanciaPath' => $paymentVoucher->constancia ? $this->getFile($paymentVoucher->constancia) : null,
            'routeName'      => $this->routeName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentVoucherRequest $request, PaymentVoucher $paymentVoucher)
    {
        $article = $paymentVoucher->article; // Asume que la relación existe
        if ($article) {
            $article->update([
                'references' => $request->input('references'),
            ]);

            // 2. Manejar el archivo editable del artículo
            $this->handleEditableFile($request, $article);
        }

        // 3. Actualizar el PaymentVoucher
        $fields = $request->validated();
        $fields['payment_voucher_status_id'] = 1; // se reinicia el proceso

        // 4. Ser explícito para no guardar 'references' o 'editable_file' en el voucher
        unset($fields['references']);
        unset($fields['editable_file']);
        // (Sería mejor crear el array $voucherData como en el método 'store')

        $paymentVoucher->update($fields);

        // 5. Manejar los archivos del voucher
        $this->handleFileVoucher($request, $paymentVoucher, $article);
        $this->handleFileConstancia($request, $paymentVoucher, $article);

        return redirect()->route("{$this->routeName}index")->with('success', 'Comprobante actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        // Lógica para eliminar si es necesario
    }

    public function showValidate(PaymentVoucher $paymentVoucher)
    {
        // **CORRECCIÓN: Cargar la relación 'constancia'**
        $paymentVoucher->load(
            'article.postulant.institution.country',
            'article.postulant.institution.state',
            'article.editor',
            'article.articleStatus',
            'paymentVoucherStatus',
            'file',
            'constancia' // <-- Cargar relación
        );

        return Inertia::render("{$this->source}Validate", [
            'title'                  => 'Validar comprobante de pago',
            'paymentVoucher'         => $paymentVoucher,
            'filePath'               => $paymentVoucher->file ? $this->getFile($paymentVoucher->file) : null,
            // **CORRECCIÓN: Pasar la URL de la constancia a la vista**
            'constanciaPath'         => $paymentVoucher->constancia ? $this->getFile($paymentVoucher->constancia) : null,
            'paymentVoucherStatuses' => PaymentVoucherStatus::all(),
            'routeName'              => $this->routeName,
        ]);
    }

    public function handleValidate(ValidatePaymentVoucherRequest $request, PaymentVoucher $paymentVoucher)
    {
        $this->authorize('handleValidate', $paymentVoucher);
        $paymentVoucher->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Comprobante revisado con éxito');
    }

    private function getFile(File $file)
    {
        return URL::signedRoute('file.serve', $file, now()->addMinutes(60));
    }

    /**
     * @param PaymentVoucher $paymentVoucher
     * @param string $requestKey      // La clave del archivo en el Request (ej: 'file', 'constancia')
     * @param string $relationName    // El nombre del método de relación (ej: 'file', 'constancia')
     * @param string $category        // La categoría para guardar en la BD (ej: 'voucher', 'constancia')
     * @param bool $saveOriginalName  // Si se debe guardar el nombre original o no
     */
    private function handleRelatedFileUpload(
        Request $request,
        PaymentVoucher $paymentVoucher,
        string $requestKey,
        string $relationName,
        string $category,
        Article $article,
        bool $saveOriginalName = false
    ) {
        // 1. Verificar si el archivo está presente en la solicitud
        if (!$request->hasFile($requestKey)) {
            return; 
        }

        $fileStorage = $request->file($requestKey);

        //Obtener el modelo del archivo existente (si hay uno)
        $existingFile = $paymentVoucher->{$relationName}()->first();

        // 3. Eliminar el archivo físico anterior si existe
        if ($existingFile && Storage::disk('private')->exists($existingFile->path)) {
            Storage::disk('private')->delete($existingFile->path);
        }

        $safeBaseName = str_replace(['/', '\\'], '-', $article->article_folio);
        $extension = $fileStorage->getClientOriginalExtension();
        $customFileName = $safeBaseName . '_' . $category . '.' . $extension;

        // 4. Almacenar el nuevo archivo de forma segura
        $folder = '';
        if ($category === 'constancia') {
            $folder = 'Constancias';
        } else {
            $folder = $this->storage_path; // (paymentVouchers)
        }
        $filePath = $fileStorage->storeAs($folder, $customFileName, 'private');
        $fileName = $customFileName;

        // 5. Preparar los datos del archivo para la base de datos
        $fileData = [
            'name'      => $fileName,
            'path'      => $filePath,
            'size'      => $fileStorage->getSize(),
            'mime_type' => $fileStorage->getMimeType(),
            'category'  => $category,
        ];

        // 6. Añadir el nombre original si se especificó
        if ($saveOriginalName) {
            $fileData['original_name'] = $fileStorage->getClientOriginalName();
        }

        // 7. Usar updateOrCreate para actualizar el registro existente o crear uno nuevo.
        $paymentVoucher->{$relationName}()->updateOrCreate(
            [], 
            $fileData 
        );
    }

    /**
     * Maneja el archivo 'file' (voucher).
     */
    private function handleFileVoucher(Request $request, PaymentVoucher $paymentVoucher, Article $article)
    {
        $this->handleRelatedFileUpload(
            $request,
            $paymentVoucher,
            'file',           // $requestKey (nombre en el form)
            'file',           // $relationName (nombre de la relación Eloquent)
            'voucher',     
            $article,   
            true              
        );
    }

    /**
     * Maneja el archivo 'constancia'.
     */
    private function handleFileConstancia(Request $request, PaymentVoucher $paymentVoucher, Article $article)
    {
        $this->handleRelatedFileUpload(
            $request,
            $paymentVoucher,
            'constancia',     // $requestKey
            'constancia',     // $relationName
            'constancia', 
            $article,    // $category
            false             
        );
    } 

    /**
     * Maneja el archivo editable asociado al artículo.
     */private function handleEditableFile(Request $request, Article $article)
    {
        // 1. Verificar si el archivo está presente
        if (!$request->hasFile('editable_file')) {
            return;
        }

        $fileStorage = $request->file('editable_file');
        $relationName = 'editableFile'; // Nombre de la relación en el modelo Article
        $category = 'editable'; // Categoría en la BD

        // 2. Obtener el archivo existente (si hay)
        $existingFile = $article->{$relationName}()->first();

        // 3. Eliminar el archivo físico anterior
        if ($existingFile && Storage::disk('private')->exists($existingFile->path)) {
            Storage::disk('private')->delete($existingFile->path);
        }

        $safeBaseName = str_replace(['/', '\\'], '-', $article->article_folio);
        //Obtenemos la extensión original del archivo
        $extension = $fileStorage->getClientOriginalExtension();
        // Creamos el nombre: "FOLIO_editable.zip"
        $customFileName = $safeBaseName . '_' . $category . '.' . $extension;
        $folderPath = 'Editables';
        $filePath = $fileStorage->storeAs($folderPath, $customFileName, 'private');
        $fileName = $customFileName;

        // 5. Preparar datos
        $fileData = [
            'name'          => $fileName,
            'original_name' => $fileStorage->getClientOriginalName(), // Guardar nombre original
            'path'          => $filePath,
            'size'          => $fileStorage->getSize(),
            'mime_type'     => $fileStorage->getMimeType(),
            'category'      => $category,
        ];

        // 7. Actualizar o crear el registro del archivo
        $article->{$relationName}()->updateOrCreate([], $fileData);
    }
}