<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArea;
use App\Http\Requests\StoreKnowledgeAreaRequest;
use App\Http\Requests\UpdateKnowledgeAreaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeAreaController extends Controller
{
    private KnowledgeArea $model;
    private string $source;
    private string $routeName;
    private string $module = 'knowledgeArea';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/KnowledgeArea/';
        $this->model = new KnowledgeArea();
        $this->routeName = 'knowledgeArea.';

        // $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        // $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        // $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        // $this->middleware("permission:{$this->module}.delete")->only(['destroy', 'edit']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';

        $records = $this->model->where('parent_id', null);

        $records->when($request->search, function ($query, $search) {
            if ($search != '') {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%');
            }
        });

        $records = $records->orderBy($order, $direction)->paginate(8)->withQueryString()->through(
            fn ($data) => [
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'areas'        =>  $records,
            'title'          => 'Gestión de Areas de conocimiento',
            'routeName'      => $this->routeName,
            'search'         => $request->search ?? '',
            'direction'     => $direction
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'          => 'Agregar área de conocimiento',
            'routeName'      => $this->routeName
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKnowledgeAreaRequest $request)
    {
        KnowledgeArea::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Área de conocimiento creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(KnowledgeArea $knowledgeArea)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KnowledgeArea $knowledgeArea)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'          => 'Editar área de conocimiento',
            'area'        => $knowledgeArea,
            'routeName'      => $this->routeName
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKnowledgeAreaRequest $request, KnowledgeArea $knowledgeArea)
    {
        $knowledgeArea->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Área de conocimiento modificada con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KnowledgeArea $knowledgeArea)
    {
        $knowledgeArea->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Área de conocimiento eliminada con éxito');
    }
}
