<?php
namespace App\Http\Controllers;

use App\Exports\ArticleTypeExport;
use App\Models\ArticleType;
use App\Http\Requests\StoreArticleTypeRequest;
use App\Http\Requests\UpdateArticleTypeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ArticleService;
use Exception;


class ArticleTypeController extends Controller
{
    private ArticleType $model;
    private string $source;
    private string $routeName;
    private string $module = 'articleType';
    protected ArticleService $service;

    public function __construct(ArticleService $service)
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/ArticleType/';
        $this->model = new ArticleType();
        $this->routeName = 'articleType.';
        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
        $this->service = $service;
        $this->service = $service;
    }

    public function index(Request $request)
{
    $direction = $request->direction ?? 'desc';
    $order = $request->order ?? 'created_at';
    $records = $this->model->query();

    $records->when($request->search, function ($query, $search) {
        if ($search != '') {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }
    });

    $records = $records->withCount('rubrics') // Agregar conteo de rúbricas
        ->orderBy($order, $direction)
        ->paginate(8)
        ->withQueryString()
        ->through(fn($data) => [
            'id' => $data->id,
            'name' => $data->name,
            'rubrics_count' => $data->rubrics_count, // Mostrar cantidad de rúbricas
            'created_at' => $data->created_at->format('d/m/Y'),
        ]);

    return Inertia::render("{$this->source}Index", [
        'articleTypes' => $records,
        'title' => 'Tipos de artículos',
        'routeName' => $this->routeName,
        'search' => $request->search ?? '',
        'direction' => $direction,
    ]);
}
    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title' => 'Agregar tipo de artículo',
            'routeName' => $this->routeName,
            'title' => 'Agregar tipo de artículo',
            'routeName' => $this->routeName,
        ]);
    }

    public function store(StoreArticleTypeRequest $request)
    {
        try {
            $data = $request->validated();
            $rubrics = $request->input('rubrics', []);

            $articleType = $this->service->createWithRubrics($data, $rubrics);

            return redirect()->route("{$this->routeName}index")
                ->with('success', 'Tipo de artículo creado con éxito!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el tipo de artículo: ' . $e->getMessage());
        }
        try {
            $data = $request->validated();
            $rubrics = $request->input('rubrics', []);

            $articleType = $this->service->createWithRubrics($data, $rubrics);

            return redirect()->route("{$this->routeName}index")
                ->with('success', 'Tipo de artículo creado con éxito!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el tipo de artículo: ' . $e->getMessage());
        }
    }

    public function edit(ArticleType $articleType)
    {
        // Cargar todas las relaciones necesarias para el formulario
        $articleType->load([
            'rubrics' => function ($query) {
                $query->with([
                    'items' => function ($query) {
                        $query->with('answers');
                    }
                ]);
            }
        ]);

        // Cargar todas las relaciones necesarias para el formulario
        $articleType->load([
            'rubrics' => function ($query) {
                $query->with([
                    'items' => function ($query) {
                        $query->with('answers');
                    }
                ]);
            }
        ]);

        return Inertia::render("{$this->source}Edit", [
            'title' => 'Editar tipo de artículo',
            'articleType' => $articleType,
            'routeName' => $this->routeName,
            'title' => 'Editar tipo de artículo',
            'articleType' => $articleType,
            'routeName' => $this->routeName,
        ]);
    }

    public function update(UpdateArticleTypeRequest $request, ArticleType $articleType)
    {
        try {
            $data = $request->validated();
            $rubrics = $request->input('rubrics', []);

            $this->service->updateWithRubrics($articleType, $data, $rubrics);

            return redirect()->route("{$this->routeName}index")
                ->with('success', 'Tipo de artículo actualizado con éxito!');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el tipo de artículo: ' . $e->getMessage());
        }
    }

    public function show(ArticleType $articleType)
    {
        // Para mostrar detalles del tipo de artículo con sus rúbricas
        $articleType->load([
            'rubrics.items.answers'
        ]);

        return Inertia::render("{$this->source}Show", [
            'title' => 'Detalles del tipo de artículo',
            'articleType' => $articleType,
            'routeName' => $this->routeName,
        ]);
    }

   public function destroy(ArticleType $articleType)
{
    try {
                \Log::info("Eliminando ArticleType ID: {$articleType->id} - Nombre: {$articleType->name}");

        $articleType->delete();

        return redirect()->route("{$this->routeName}index")
            ->with('success', 'Tipo de artículo eliminado con éxito!');
    } catch (Exception $e) {
                \Log::error("Error eliminando ArticleType: " . $e->getMessage());

        return redirect()->back()
            ->with('error', 'Error al eliminar el tipo de artículo: ' . $e->getMessage());
    }
}
public function listAll()
{
    return ArticleType::select('id', 'name')->orderBy('name')->get();
}

 public function export()
    {
        return Excel::download(new ArticleTypeExport, 'Tipos de Artículo.xlsx');
    }
} 