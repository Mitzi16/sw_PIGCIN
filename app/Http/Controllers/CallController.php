<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\File;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CallController extends Controller
{
    private Call $model;
    private string $source;
    private string $routeName;
    private string $module = 'call';
    private string $storage_path = 'calls';

    public function __construct()
    {
        $this->middleware('auth');
        $this->source = 'Catalogs/Call/';
        $this->model = new Call();
        $this->routeName = 'call.';

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

        $records = $this->model->query()->when($request->search, function ($query, $search) {
            if ($search != '') {
                $query->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%')
                    ->orWhere('start_date', 'LIKE', '%' . $search . '%')
                    ->orWhere('end_date', 'LIKE', '%' . $search . '%');
            }
        });

        $records = $records->orderBy($order, $direction)->paginate(8)->withQueryString()->through(
            fn ($data) => [
                'id' => $data->id,
                'title' => $data->title,
                'description' => $data->description,
                'start_date' => $data->start_date,
                'end_date' => $data->end_date,
                'status' => $data->status
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'calls'        =>  $records,
            'title'          => 'Gestión de convocatorias',
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
            'title'          => 'Agregar convocatoria',
            'routeName'      => $this->routeName,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCallRequest $request)
    {
        $fields = $request->validated();
        $call = Call::create($fields);
        $this->storeFile($request, $call, 'file');
        $this->storeFile($request, $call, 'image');
        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Call $call)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Call $call)
    {
        $call->load('institution', 'files');
        return Inertia::render("{$this->source}Edit", [
            'title'          => 'Editar convocatoria',
            'call'        => $call->transform(),
            'routeName'      => $this->routeName,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCallRequest $request, Call $call)
    {
        $validated = $request->validated();
        $call->update($validated);
        $this->updateFile($request, $call, 'file');
        $this->updateFile($request, $call, 'image');
        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria modificada con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Call $call)
    {
        $call->delete();
        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria eliminada con éxito');
    }

    private function storeFile(Request $request, Call $call, $type)
    {
        if ($request->hasFile($type)) {
            $this->createFile($request, $call, $type);
        }
    }

    private function createFile(Request $request, Call $call, $type)
    {
        $fileStorage = $request->file($type);
        $fileName = (File::max('id') + 1) . '-' . $fileStorage->getClientOriginalName();
        $filePath = $fileStorage->storeAs($this->storage_path, $fileName, 'public');
        $call->files()->create([
            'name' => $fileName,
            'path' => $filePath,
            'size' => $fileStorage->getSize(),
            'mime_type' => $fileStorage->getMimeType(),
        ]);
    }

    private function updateFile(Request $request, Call $call, $type)
    {
        if ($request->hasFile($type)) {
            if ($request->has($type . '_id')) {
                $id = $type === 'image' ? $request->image_id : $request->file_id;
                $file = File::findOrFail($id);
                Storage::disk('public')->delete($file->path);
                $fileStorage = $request->file($type);
                $fileName = $id . '-' . $fileStorage->getClientOriginalName();
                $filePath = $fileStorage->storeAs($this->storage_path, $fileName, 'public');
                $file->name = $fileName;
                $file->path = $filePath;
                $file->size = $fileStorage->getSize();
                $file->mime_type = $fileStorage->getMimeType();
                $file->save();
            } else {
                $this->createFile($request, $call, $type);
            }
        }
    }
}
