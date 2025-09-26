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
            $article = Article::findOrFail($request->article_id);
            $this->authorize('canStorePaymentVoucher', $article);

            $fields = $request->validated();
             if (isset($fields['requires_invoice'])) {
            $fields['requires_invoice'] = $fields['requires_invoice'] === 'yes' ? 1 : 0;
            }
            $fields['payment_voucher_status_id'] = 1; // enviado a validar
            $fields['user_id'] = Auth::id();
            $paymentVoucher = PaymentVoucher::create($fields);
            $article->update(['payment_voucher_id' => $paymentVoucher->id]);

            $this->handleFileVoucher($request, $paymentVoucher);
            // **CORRECCIÓN: Llamar al método para manejar la constancia**
            $this->handleFileConstancia($request, $paymentVoucher);

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
        $fields = $request->validated();
        $fields['payment_voucher_status_id'] = 1; // se reinicia el proceso, enviado a validar

        $paymentVoucher->update($fields);

        $this->handleFileVoucher($request, $paymentVoucher);
        // **CORRECCIÓN: Llamar al método para manejar la constancia**
        $this->handleFileConstancia($request, $paymentVoucher);

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

    private function handleFileVoucher(Request $request, PaymentVoucher $paymentVoucher)
{
    if ($request->hasFile('file')) {
        $fileStorage = $request->file('file');
        
        $originalName = $fileStorage->getClientOriginalName();

        $hashedPath = $fileStorage->store($this->storage_path, 'private');

        $fileData = [
            'original_name' => $originalName,
            'name'          => basename($hashedPath),
            'path'          => $hashedPath,
            'size'          => $fileStorage->getSize(),
            'mime_type'     => $fileStorage->getMimeType(),
            'category'      => 'voucher',
        ];
        
        $fileModel = $paymentVoucher->file()->first(); 

        if ($fileModel) {
            // Si ya existe un archivo, borra el físico anterior
            Storage::disk('private')->delete($fileModel->path);
            // Y actualiza el registro en la BD
            $fileModel->update($fileData);
        } else {
            // Si no existe, crea un nuevo registro de archivo asociado
            $paymentVoucher->file()->create($fileData);
        }
    }
}

    private function handleFileConstancia(Request $request, PaymentVoucher $paymentVoucher)
    {
        if ($request->hasFile('constancia')) {
            $fileStorage = $request->file('constancia');
            $fileConstancia = $paymentVoucher->constancia()->first();

            // Eliminar archivo anterior si existe
            if ($fileConstancia && Storage::disk('private')->exists($fileConstancia->path)) {
                Storage::disk('private')->delete($fileConstancia->path);
            }

            // **MEJORA: Usar store() para un nombre de archivo único y seguro**
            $filePath = $fileStorage->store($this->storage_path, 'private');
            $fileName = basename($filePath);

            $fileData = [
                'name'      => $fileName,
                'path'      => $filePath,
                'size'      => $fileStorage->getSize(),
                'mime_type' => $fileStorage->getMimeType(),
                'category'  => 'constancia',
            ];

            // Actualizar o crear el registro del archivo
            $paymentVoucher->constancia()->updateOrCreate([], $fileData);
        }
    }
}