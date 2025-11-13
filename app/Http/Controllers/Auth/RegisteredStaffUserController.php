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

class RegisteredStaffUserController extends Controller
{
    public function createStaff(): Response
    {
        return Inertia::render("Auth/RegisterStaff", [
            'title'         => 'Registro de Staff',
            'areas'         => KnowledgeArea::orderBy('name')->get(),
            'institutions'  => Institution::orderBy('name')->select('id', 'name')->where('status', true)->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeStaff(RegisterRequest $request): RedirectResponse
    {
        $request->validated();
        $postulant = Role::where('name', 'Staff')->firstOrFail();
        $user = User::create($request->validated());
        $user->assignRole($postulant);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route("dashboard");
    }
}
