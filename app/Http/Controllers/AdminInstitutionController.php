<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Call;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AdminInstitutionController extends Controller
{
    public function index(Request $request)
    {
        // Obtenemos los usuarios con el rol 'Administrador' y cargamos sus relaciones
        if (!$request->user()->hasRole('SuperAdmin')) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        $admins = User::role('Administrador')
            ->with(['institution', 'calls'])
            ->when($request->institution_id, function ($query, $institution_id) {
                // Si se selecciona una institución, filtramos
                $query->where('institution_id', $institution_id);
            })
            ->get();

        return Inertia::render('Admins/Index', [
            'title' => 'Listado de Administradores',
            'admins' => $admins,
            'institutions' => Institution::select('id', 'name')->get(),
            'filters' => $request->only('institution_id')
        ]);
    }

    public function create(Request $request)
    {
        if (!$request->user()->hasRole('SuperAdmin')) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        // 👇 CORRECCIÓN AQUÍ: Quitamos 'Admin' del arreglo 👇
        $users = User::role(['SuperAdmin', 'Admin-Revista', 'Administrador'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admins/Create', [
            'title'         => 'Asignar Administrador',
            'users'         => $users,
            'institutions'  => Institution::select('id', 'name')->get(),
            'calls'         => Call::select('id', 'title', 'institution_id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'institution_id' => 'required|exists:institutions,id',
            'calls' => 'array',
            'calls.*' => [
                'integer',
                Rule::exists('calls', 'id')->where(function ($query) use ($request) {
                    return $query->where('institution_id', $request->institution_id);
                }),
            ],
        ], [
            'calls.*.exists' => 'Una de las convocatorias seleccionadas no pertenece a la institución elegida.'
        ]);

        // 1. Buscamos al usuario existente en lugar de crearlo
        $user = User::findOrFail($validated['user_id']);

        // 2. Actualizamos su institución
        $user->update([
            'institution_id' => $validated['institution_id'],
        ]);

        // 3. Le asignamos el rol de Administrador
        $user->assignRole('Administrador');

        // 4. Sincronizamos las convocatorias
        if (!empty($validated['calls'])) {
            $user->calls()->sync($validated['calls']);
        } else {
            $user->calls()->sync([]); // Si no se seleccionó ninguna, limpiamos las anteriores
        }

        return redirect()->route('admins.index')->with('success', 'Usuario asignado como Administrador exitosamente.');
    }

    public function edit(User $user)
    {
        // Cargamos la institución y convocatorias actuales del administrador
        $user->load(['institution', 'calls']);

        return Inertia::render('Admins/Edit', [
            'title'        => 'Editar Asignación de Administrador',
            'admin'        => $user,
            'institutions' => Institution::select('id', 'name')->get(),
            'calls'        => Call::select('id', 'title', 'institution_id')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'calls' => 'array',
            'calls.*' => [
                'integer',
                Rule::exists('calls', 'id')->where(function ($query) use ($request) {
                    return $query->where('institution_id', $request->institution_id);
                }),
            ],
        ], [
            'calls.*.exists' => 'Una de las convocatorias seleccionadas no pertenece a la institución elegida.'
        ]);

        // Actualizamos la institución
        $user->update([
            'institution_id' => $validated['institution_id'],
        ]);

        // Sincronizamos las convocatorias
        if (!empty($validated['calls'])) {
            $user->calls()->sync($validated['calls']);
        } else {
            $user->calls()->sync([]);
        }

        return redirect()->route('admins.index')->with('success', 'Asignación actualizada exitosamente.');
    }
}
