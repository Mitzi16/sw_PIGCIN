<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnowledgeArea;
use App\Http\Requests\StoreKnowledgeAreaRequest;
use App\Http\Requests\UpdateKnowledgeAreaRequest;
use Inertia\Inertia;
use App\Exports\KnowledgeSubAreaExport;
use Maatwebsite\Excel\Facades\Excel;

class KnowledgeSubAreaController extends Controller
{
    private KnowledgeArea $model;
    private string $source;
    private string $routeName;
    private string $module = 'knowledgeSubArea';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/KnowledgeSubArea/';
        $this->model = new KnowledgeArea();
        $this->routeName = 'knowledgeSubArea.';

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
    }

   public function index(Request $request)
{
    $direction = $request->direction ?? 'desc';
    $order = $request->order ?? 'created_at';

    // 👇 prefijo explícito en parent_id
    $records = $this->model->with('parent')->whereNotNull('knowledge_areas.parent_id');

    $records->when($request->search, function ($query, $search) {
        if ($search != '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('knowledge_areas.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('knowledge_areas.description', 'LIKE', '%' . $search . '%');
            });
        }
    });

    if ($order === 'parent') {
        $records = $records
            ->join('knowledge_areas as parents', 'parents.id', '=', 'knowledge_areas.parent_id')
            ->orderBy('parents.name', $direction)
            ->select('knowledge_areas.*'); // evita columnas duplicadas
    } else {
        $records = $records->orderBy("knowledge_areas.$order", $direction);
    }

    $records = $records->paginate(8)->withQueryString()->through(
        fn($data) => [
            'id' => $data->id,
            'name' => $data->name,
            'description' => $data->description,
            'parent' => $data->parent?->name,
        ]
    );

    return Inertia::render("{$this->source}Index", [
        'subareas'   => $records,
        'title'      => 'Gestión de Áreas Prioritarias',
        'routeName'  => $this->routeName,
        'search'     => $request->search ?? '',
        'direction'  => $direction,
    ]);
}

    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'          => 'Agregar Área Prioritaria',
            'routeName'      => $this->routeName,
            'areas' => KnowledgeArea::where('parent_id', null)->get()
        ]);
    }

    public function store(StoreKnowledgeAreaRequest $request)
    {
        KnowledgeArea::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'SubÁrea de conocimiento creada con éxito!');
    }

    public function edit(KnowledgeArea $knowledgeSubArea)
    {
        $knowledgeSubArea->load('parent');
        return Inertia::render("{$this->source}Edit", [
            'title'          => 'Editar SubÁrea de conocimiento',
            'subarea'        => $knowledgeSubArea,
            'routeName'      => $this->routeName,
            'areas' => KnowledgeArea::where('parent_id', null)->get()
        ]);
    }

    public function update(UpdateKnowledgeAreaRequest $request, KnowledgeArea $knowledgeSubArea)
    {
        $knowledgeSubArea->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Área de conocimiento modificada con éxito!');
    }

    public function destroy(KnowledgeArea $knowledgeSubArea)
    {
        $knowledgeSubArea->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Área de conocimiento eliminada con éxito');
    }

    public function export()
    {
        return Excel::download(new KnowledgeSubAreaExport(), 'subareas_de_conocimiento.xlsx');
    }
}
