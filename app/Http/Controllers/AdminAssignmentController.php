<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Call;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAssignmentController extends Controller
{
    public function index()
    {
        // Trae a los usuarios con rol 'Admin' o 'Admin-Revista'
        $admins = User::role(['Admin', 'Admin-Revista', 'Super Admin'])
            ->with('institution')
            ->get();

        return Inertia::render('AdminAssignments/Index', [
            'admins' => $admins
        ]);
    }

    public function create()
    {
        // Solo traemos los usuarios que tengan el rol de Admin
        $admins = User::role('Admin')->with('institution')->get();

        return Inertia::render('AdminAssignments/Create', [
            'admins' => $admins,
        ]);
    }

    // Esta es la función que busca las convocatorias por institución
    public function getUserCalls(User $user)
    {
        // Si el usuario no tiene institución, devolvemos un arreglo vacío
        if (!$user->institution_id) {
            return response()->json([]);
        }

        // Buscamos SOLO las convocatorias de la institución del usuario
        $calls = Call::where('institution_id', $user->institution_id)->get(['id', 'title']);

        return response()->json($calls);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'call_ids' => 'required|array',
            'call_ids.*' => 'exists:calls,id'
        ]);

        $user = User::find($request->user_id);

        // Sincronizamos las convocatorias seleccionadas en la tabla pivote (ej. call_user)
        $user->calls()->sync($request->call_ids);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Convocatorias asignadas correctamente.');
    }
    /**
     * Muestra el formulario para editar las convocatorias asignadas a un administrador.
     */
    public function edit(User $user)
    {
        // 1. Cargamos la institución del usuario para mostrarla en la vista
        $user->load('institution');

        // 2. Buscamos las convocatorias disponibles SOLO para la institución de este usuario
        $availableCalls = [];
        if ($user->institution_id) {
            $availableCalls = \App\Models\Call::where('institution_id', $user->institution_id)
                ->get(['id', 'title']);
        }

        // 3. Obtenemos los IDs de las convocatorias que ya tiene asignadas
        $assignedCalls = $user->calls()->pluck('calls.id')->toArray();

        // 4. Retornamos la vista de Inertia con los datos
        return Inertia::render('AdminAssignments/Edit', [
            'admin' => $user,
            'availableCalls' => $availableCalls,
            'assignedCalls' => $assignedCalls
        ]);
    }

    /**
     * Actualiza las convocatorias asignadas a un administrador.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'call_ids' => 'required|array',
            'call_ids.*' => 'exists:calls,id'
        ]);

        // Sincroniza las convocatorias (agrega las nuevas y quita las desmarcadas)
        $user->calls()->sync($request->call_ids);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Asignaciones actualizadas correctamente.');
    }
}
