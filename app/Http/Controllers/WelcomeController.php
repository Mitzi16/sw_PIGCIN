<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    protected string $routeName;
    protected string $source;

    public function __construct()
    {
        $this->routeName = "welcome.";
        $this->source    = "Welcome/";
    }

    public function welcome(Request $request): Response
    {
        $query = Call::query()->with('files');
        $query = $query->orderBy('created_at', 'desc');
        $query->where('status', true);
        $query->when($request->input('search'), function ($query, $search) {
            if ($search != '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('start_date', 'LIKE', '%' . $search . '%')
                        ->orWhere('end_date', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('institution', function ($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            }
        });
        $calls = $query->paginate(3)->through(
            function ($call) {
                return $call->transform();
            }
        );
        return Inertia::render("{$this->source}Home/Index", [
            'title'   => 'Bienvenido',
            'routeName' => $this->routeName,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'calls' => $calls,
            'search' => $request->search ?? '',
        ]);
    }

    public function committee()
    {
        return Inertia::render("{$this->source}Committee/Index", [
            'title'   => 'Comité',
            'routeName' => $this->routeName,
        ]);
    }

    public function place()
    {
        return Inertia::render("{$this->source}Place/Index", [
            'title'   => 'Lugar',
            'routeName' => $this->routeName,
        ]);
    }

    public function program()
    {
        return Inertia::render("{$this->source}Program/Index", [
            'title'   => 'Programa',
            'routeName' => $this->routeName,
        ]);
    }
}
