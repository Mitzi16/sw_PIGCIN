<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Country;
use App\Models\Institution;
use App\Models\KnowledgeArea;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $callId = $request->query('call_id');

        return Inertia::render("Auth/Register", [
            'title'         => 'Registro de Postulantes',
            'areas'         => KnowledgeArea::orderBy('name')->get(),
            'institutions'  => Institution::orderBy('name')
                ->select('id', 'name')
                ->where('status', true)
                ->get(),
            'call_id'       => $callId,
            // Buscamos la convocatoria para mostrar los datos en el front
            'selected_call' => $callId ? \App\Models\Call::find($callId) : null,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // 1. Obtenemos los datos validados
        $data = $request->validated();

        // 2. Extraemos el rol y el call_id (no son columnas de la tabla users)
        $roleName = $data['role'] ?? 'Postulante';
        $callId = $data['call_id'] ?? null;
        
        unset($data['role'], $data['call_id']);

        // 3. Buscamos el rol en la base de datos
        $role = Role::where('name', $roleName)->firstOrFail();

        // 4. Creamos el usuario
        $user = User::create($data);

        // 5. Le asignamos el rol dinámico
        $user->assignRole($role);

        // 👇 6. ASOCIAMOS A LA CONVOCATORIA SELECCIONADA 👇
        if ($callId) {
            $user->calls()->attach($callId);
        }

        event(new Registered($user));
        Auth::login($user);

        // 7. Redirección dependiendo del rol (asegúrate de que las rutas existan)
        return match ($roleName) {
            'Postulante'    => redirect()->route('profile.edit'), // Lo enviamos directo a su perfil
            'Revisor'       => redirect()->route('profile.edit'),
            'Editor'        => redirect()->route('profile.edit'),
            'Moderador'     => redirect()->route('profile.edit'),
            'Conferencista' => redirect()->route('profile.edit'),
            'Tallerista'    => redirect()->route('profile.edit'),
            'Staff'         => redirect()->route('profile.edit'),
            default         => redirect()->route('dashboard'),
        };
    }
}