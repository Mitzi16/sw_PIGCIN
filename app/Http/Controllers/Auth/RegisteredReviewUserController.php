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

class RegisteredReviewUserController extends Controller
{
     /**
     * Display the registration view.
     */
    public function createRevisores(): Response
    {
        return Inertia::render("Auth/RegisterReview", [
            'title'         => 'Registro de Revisores',
            'areas'         => KnowledgeArea::orderBy('name')->get(),
            'institutions'  => Institution::orderBy('name')->select('id', 'name')->where('status', true)->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeRevisores(RegisterRequest $request): RedirectResponse
    {
        $request->validated();
        $postulant = Role::where('name', 'Revisor')->firstOrFail();
        $user = User::create($request->validated());
        $user->assignRole($postulant);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route("dashboard");
    }
}
