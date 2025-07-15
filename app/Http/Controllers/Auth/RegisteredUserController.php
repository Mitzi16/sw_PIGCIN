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
    public function create(): Response
    {
        return Inertia::render("Auth/Register", [
            'title'         => 'Registro',
            'areas'         => KnowledgeArea::orderBy('name')->get(),
            'institutions'  => Institution::orderBy('name')->select('id', 'name')->where('status', true)->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $request->validated();
        $postulant = Role::where('name', 'Postulante')->first();
        $user = User::create($request->validated());
        $user->assignRole($postulant);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route("dashboard");
    }
}
