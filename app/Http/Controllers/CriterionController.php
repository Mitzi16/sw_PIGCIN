<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Http\Requests\StoreCriterionRequest;
use App\Http\Requests\UpdateCriterionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CriterionController extends Controller
{
    private Criterion $model;
    private string $source;
    private string $routeName;
    private string $module = 'criterion';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/Criterion/';
        $this->model = new Criterion();
        $this->routeName = 'criterion.';

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';

        $records = $this->model->query();

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
                'description' => $data->description,
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'criteria'        =>  $records,
            'title'          => 'Gestión de criterios',
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
            'title'          => 'Agregar criterio',
            'routeName'      => $this->routeName,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCriterionRequest $request)
    {
        Criterion::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Criterio creado con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Criterion $criterion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Criterion $criterion)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'          => 'Editar criterio',
            'criteria'        => $criterion,
            'routeName'      => $this->routeName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCriterionRequest $request, Criterion $criterion)
    {
        $criterion->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Criterio modificado con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Criterion $criterion)
    {
        $criterion->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Criterio eliminado con éxito');
    }
}
