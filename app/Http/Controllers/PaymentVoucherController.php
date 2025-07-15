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
            fn($article) => [
                'id' => $article->id,
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
            'article'    => $article->load('articleStatus', 'editor:id,name', 'postulant.institution.country', 'postulant.institution.state'),
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
            $fields['payment_voucher_status_id'] = 1; // enviado a validar
            $fields['user_id'] = Auth::id();
            $paymentVoucher = PaymentVoucher::create($fields);
            $article->update(['payment_voucher_id' => $paymentVoucher->id]);

            $this->handleFileVoucher($request, $paymentVoucher);
            return redirect()->route("{$this->routeName}index")->with('success', 'Comprobante generado con éxito');
        } catch (\Exception $e) {
            Log::error('Error en guardar comprobante: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Ocurrió un error al cargar el comprobante.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        return Inertia::render("{$this->source}Show", [
            'title'             => 'Visualizar comprobante de pago',
            'paymentVoucher'    => $paymentVoucher->load(
                'article.postulant.institution.country',
                'article.postulant.institution.state',
                'article.editor',
                'article.articleStatus',
                'paymentVoucherStatus',
                'file'
            ),
            'filePath'                  => $this->getFile($paymentVoucher->file),
            'paymentVoucherStatuses'    => PaymentVoucherStatus::all(),
            'routeName'                 => $this->routeName,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'             => 'Editar comprobante de pago',
            'paymentVoucher'    => $paymentVoucher->load('article.editor', 'article.articleStatus', 'paymentVoucherStatus'),
            'filePath'          => $this->getFile($paymentVoucher->file),            
            'routeName'         => $this->routeName,
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
        return redirect()->route("{$this->routeName}index")->with('success', 'Comprobante actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        //
    }

    public function showValidate(PaymentVoucher $paymentVoucher)
    {
        return Inertia::render("{$this->source}Validate", [
            'title'             => 'Validar comprobante de pago',
            'paymentVoucher'    => $paymentVoucher->load(
                'article.postulant.institution.country',
                'article.postulant.institution.state',
                'article.editor',
                'article.articleStatus',
                'paymentVoucherStatus',
                'file'
            ),
            'filePath'                  => $this->getFile($paymentVoucher->file),
            'paymentVoucherStatuses'    => PaymentVoucherStatus::all(),
            'routeName'                 => $this->routeName,
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
        $fileStorage = $request->file('file');

        if ($fileStorage) {
            $fileVoucher = $paymentVoucher->file()->first();
            if ($fileVoucher && Storage::disk('private')->exists($fileVoucher->path)) {
                Storage::disk('private')->delete($fileVoucher->path);
            }

            $fileName = (File::withTrashed()->max('id') + 1) . '-' . $fileStorage->getClientOriginalName();
            $filePath = $fileStorage->storeAs($this->storage_path, $fileName, 'private');

            $fileData = [
                'name' => $fileName,
                'path' => $filePath,
                'size' => $fileStorage->getSize(),
                'mime_type' => $fileStorage->getMimeType(),
            ];
            if ($fileVoucher) {
                $fileVoucher->update($fileData);
            } else {
                $paymentVoucher->file()->create($fileData);
            }
        }
    }
}
