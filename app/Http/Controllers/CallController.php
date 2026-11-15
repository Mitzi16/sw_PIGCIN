<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\CallsExport;
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

    public function index(Request $request)
    {
        $user = $request->user();
        $direction = $request->direction ?? 'desc';
        $order = $request->order ?? 'created_at';

        $query = $this->model->newQuery();

        // 👇 CORREGIDO AQUÍ 👇
        if (!$user->hasRole('Super Admin')) {
            $myCallIds = $user->calls->pluck('id')->toArray();
            $query->whereIn('id', $myCallIds);
        }

        $query->when($request->search, function ($q, $search) {
            if ($search != '') {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('start_date', 'LIKE', '%' . $search . '%')
                        ->orWhere('end_date', 'LIKE', '%' . $search . '%');
                });
            }
        });

        $records = $query->orderBy($order, $direction)->paginate(8)->withQueryString()->through(
            fn($data) => [
                'id' => $data->id,
                'title' => $data->title,
                'description' => $data->description,
                'start_date' => $data->start_date ? \Carbon\Carbon::parse($data->start_date)->format('Y-m-d') : null,
                'end_date' => $data->end_date ? \Carbon\Carbon::parse($data->end_date)->format('Y-m-d') : null,
                'status' => $data->status,
            ]
        );

        return Inertia::render("{$this->source}Index", [
            'calls'         => $records,
            'title'         => 'Gestión de convocatorias',
            'routeName'     => $this->routeName,
            'search'        => $request->search ?? '',
            'direction'     => $direction
        ]);
    }

    public function create()
    {
        // 👇 CORREGIDO AQUÍ 👇
        if (!request()->user()->hasRole('Super Admin')) {
            abort(403, 'Solo el SuperAdministrador puede crear convocatorias.');
        }

        return Inertia::render("{$this->source}Create", [
            'title'         => 'Agregar convocatoria',
            'routeName'     => $this->routeName,
        ]);
    }

    public function store(StoreCallRequest $request)
    {
        $fields = $request->validated();
        $call = Call::create($fields);
        $this->storeFile($request, $call, 'file');
        $this->storeFile($request, $call, 'image');

        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria creada con éxito!');
    }

    public function show(Call $call)
    {
        //
    }

    public function edit(Call $call)
    {
        // 👇 CORREGIDO AQUÍ 👇
        if (!request()->user()->hasRole('Super Admin')) {
            abort(403, 'Solo el SuperAdministrador puede editar convocatorias.');
        }

        $call->load('institution', 'files');

        return Inertia::render("{$this->source}Edit", [
            'title'         => 'Editar convocatoria',
            'call'          => $call->transform(),
            'routeName'     => $this->routeName,
        ]);
    }

    public function update(UpdateCallRequest $request, Call $call)
    {
        $validated = $request->validated();
        unset($validated['file'], $validated['image']);
        $call->update($validated);

        $this->updateFile($request, $call, 'file');
        $this->updateFile($request, $call, 'image');

        return redirect()->route("{$this->routeName}index")->with('success', 'Convocatoria modificada con éxito!');
    }

    public function destroy(Call $call)
    {
        // 👇 CORREGIDO AQUÍ 👇
        if (!request()->user()->hasRole('Super Admin')) {
            abort(403, 'Solo el SuperAdministrador puede eliminar convocatorias.');
        }

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
            $id = $type === 'image' ? $request->image_id : $request->file_id;

            if ($request->filled($type . '_id') && $id) {
                $file = File::find($id); 

                if ($file) {
                    Storage::disk('public')->delete($file->path);
                    $fileStorage = $request->file($type);
                    $fileName = $id . '-' . $fileStorage->getClientOriginalName();
                    $filePath = $fileStorage->storeAs($this->storage_path, $fileName, 'public');

                    $file->name = $fileName;
                    $file->path = $filePath;
                    $file->size = $fileStorage->getSize();
                    $file->mime_type = $fileStorage->getMimeType();
                    $file->save();
                    return; 
                }
            }
            $this->createFile($request, $call, $type);
        }
    }

    public function export()
    {
        // 👇 CORREGIDO AQUÍ 👇
        if (!request()->user()->hasRole('Super Admin')) {
            abort(403, 'No tienes permiso para exportar.');
        }

        return Excel::download(
            new CallsExport(),
            'reporte_convocatorias_' . now()->format('Ymd') . '.xlsx'
        );
    }
}