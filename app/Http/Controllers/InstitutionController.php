<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Http\Requests\StoreInstitutionRequest;
use App\Http\Requests\UpdateInstitutionRequest;
use App\Models\Country;
use App\Models\State;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Inertia\Response;

class InstitutionController extends Controller
{
    private Institution $model;
    private string $source;
    private string $routeName;
    private string $module = 'institution';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/Institution/';
        $this->model = new Institution();
        $this->routeName = 'institution.';

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';

        $records = $this->model->query()->when($request->search, function ($query, $search) {
            if ($search != '') {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('status', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('state', function ($query) use ($search) {
                        $query->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('country', function ($query) use ($search) {
                        $query->where('name', 'LIKE', '%' . $search . '%');
                    });
            }
        });

        if ($order === 'country') {
            $records = $records->leftJoin('countries', 'institutions.country_id', '=', 'countries.id')
                ->orderBy('countries.name', $direction)
                ->select('institutions.*');
        } else {
            $records = $records->orderBy($order, $direction);
        }
        $records = $records->paginate(8)->withQueryString()->through(
            fn ($institution) => [
                'id' => $institution->id,
                'name' => $institution->name,
                'country' => $institution->country ? $institution->country->name : null,
                'status' => $institution->status,
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'institutions'   =>  $records,
            'title'          => 'Gestión de Instituciones',
            'routeName'      => $this->routeName,
            'loadingResults' => false,
            'search'         => $request->search ?? '',
            'direction'      => $direction
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'     => 'Agregar Institución',
            'routeName' => $this->routeName,
            'countries' => Country::orderBy('name')->get(),
            'states'    => State::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstitutionRequest $request)
    {
        Institution::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Institución creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Institution $institution)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'         => 'Editar Institución',
            'institution'   => $institution->load('state'),
            'routeName'     => $this->routeName,
            'countries'     => Country::orderBy('name')->get(),
            'states'        => State::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstitutionRequest $request, Institution $institution)
    {
        $institution->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Institución modificada con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution)
    {
        $institution->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Institución eliminada con éxito');
    }
}
