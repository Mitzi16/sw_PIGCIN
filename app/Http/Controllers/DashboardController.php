<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Call;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $postulants = User::role('Postulante')->count();
        $newPostulantsThisWeek = User::role('Postulante')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $articles = Article::count();
        $newArticlesThisWeek = Article::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        $calls = Call::count();
        $callsActives = Call::where('status', true)->count();

        $data = [
            'postulants' => $postulants,
            'newPostulantsThisWeek' => $newPostulantsThisWeek,
            'articles' => $articles,
            'newArticlesThisWeek' => $newArticlesThisWeek,
            'calls' => $calls,
            'callsActives' => $callsActives
        ];

        return Inertia::render("Dashboard", [
            'data' => $data,
        ]);
    }
}
