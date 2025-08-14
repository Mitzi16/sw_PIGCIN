<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Institution;
use App\Models\KnowledgeArea;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;


class RegisteredAsistenteUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function createAsistentes(): Response
    {
        return Inertia::render("Auth/RegisterAsistente", [
            'title'         => 'Registro de Asistentes',
            'areas'         => KnowledgeArea::orderBy('name')->get(),
            'institutions'  => Institution::orderBy('name')->select('id', 'name')->where('status', true)->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeAsistente(RegisterRequest $request): RedirectResponse
    {
        $request->validated();
        $postulant = Role::where('name', 'Asistente')->firstOrFail();
        $user = User::create($request->validated());
        $user->assignRole($postulant);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route("dashboard");
    }
}
