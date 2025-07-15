<?php

namespace App\Http\Controllers;

use App\Models\ArticleType;
use App\Http\Requests\StoreArticleTypeRequest;
use App\Http\Requests\UpdateArticleTypeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArticleTypeController extends Controller
{
    private ArticleType $model;
    private string $source;
    private string $routeName;
    private string $module = 'articleType';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/ArticleType/';
        $this->model = new ArticleType();
        $this->routeName = 'articleType.';

        $this->middleware("permission:{$this->module}.index")->only(['index', 'show']);
        $this->middleware("permission:{$this->module}.store")->only(['store', 'create']);
        $this->middleware("permission:{$this->module}.update")->only(['update', 'edit']);
        $this->middleware("permission:{$this->module}.delete")->only(['destroy']);
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

        $records = $records->orderBy($order, $direction)->paginate(8)->withQueryString()->through(
            fn ($data) => [
                'id' => $data->id,
                'name' => $data->name,
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'articleTypes'   => $records,
            'title'          => 'Tipos de artículos',
            'routeName'      => $this->routeName,
            'search'         => $request->search ?? '',
            'direction'      => $direction
        ]);
    }

    public function create()
    {
        return Inertia::render("{$this->source}Create", [
            'title'      => 'Agregar tipo de artículo',
            'routeName'  => $this->routeName,
        ]);
    }

    public function store(StoreArticleTypeRequest $request)
    {
        ArticleType::create($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Tipo de artículo creado con éxito!');
    }

    public function edit(ArticleType $articleType)
    {
        return Inertia::render("{$this->source}Edit", [
            'title'        => 'Editar tipo de artículo',
            'articleType'  => $articleType,
            'routeName'    => $this->routeName,
        ]);
    }

    public function update(UpdateArticleTypeRequest $request, ArticleType $articleType)
    {
        $articleType->update($request->validated());
        return redirect()->route("{$this->routeName}index")->with('success', 'Tipo de artículo actualizado con éxito!');
    }

    public function destroy(ArticleType $articleType)
    {
        $articleType->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Tipo de artículo eliminado con éxito!');
    }
}
