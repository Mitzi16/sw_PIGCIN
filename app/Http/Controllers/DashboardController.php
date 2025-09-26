<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Call;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first();
        $data = null; // MODIFICADO: Inicializar data como null
        $articleCountsByType = []; // NUEVO: Inicializar array para el conteo

        // Normalizamos valores nulos a 'all'
        $filters = [
            'program_id' => $request->input('program_id', 'all') ?? 'all',
            'area_id'    => $request->input('area_id', 'all') ?? 'all',
            'type'       => $request->input('type', 'all') ?? 'all',
            'date_start' => $request->input('date_start'),
            'date_end'   => $request->input('date_end')
        ];

        // Datos generales para Admin / Editor
        if (in_array($role, ['Admin', 'Editor'])) {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $data = [
                'postulants' => User::role('Postulante')->count(),
                'newPostulantsThisWeek' => User::role('Postulante')
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->count(),

                'articles' => Article::count(),
                'newArticlesThisWeek' => Article::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),

                'calls' => Call::count(),
                'callsActives' => Call::where('status', true)->count(),
            ];

            // NUEVO: Conteo de artículos por tipo para el gráfico circular
            $articleCountsByType = DB::table('articles')
                ->select('article_type_id', DB::raw('count(*) as count'))
                ->groupBy('article_type_id')
                ->get();
        }

        // Programas y áreas para selects
        $programsAndAreas = DB::table('knowledge_areas as ka')
            ->leftJoin('knowledge_areas as area', 'area.parent_id', '=', 'ka.id')
            ->whereNull('ka.parent_id') // Solo programas (padres)
            ->select(
                'ka.id as program_id',
                'ka.name as program_name',
                'area.id as area_id',
                'area.name as area_name'
            )
            ->orderBy('ka.id')
            ->get();

        $programs = $programsAndAreas
            ->unique('program_id')
            ->map(fn($item) => [
                'id' => $item->program_id,
                'name' => $item->program_name
            ])
            ->values();

        $areas = $programsAndAreas
            ->filter(fn($item) => $item->area_id !== null)
            ->unique('area_id')
            ->map(fn($item) => [
                'id' => $item->area_id,
                'name' => $item->area_name,
                'program_id' => $item->program_id
            ])
            ->values();

        $articleTypes = DB::table('article_types')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        // Conteo total de artículos filtrados
        $totalFilteredArticles = DB::table('articles as a')
            ->join('knowledge_areas as ka', 'a.knowledge_area_id', '=', 'ka.id')
            ->join('knowledge_areas as parent', 'ka.parent_id', '=', 'parent.id')
            ->when($filters['program_id'] !== 'all', function ($query) use ($filters) {
                $query->where('parent.id', $filters['program_id']);
            })
            ->when($filters['area_id'] !== 'all', function ($query) use ($filters) {
                $query->where('ka.id', $filters['area_id']);
            })
            ->when($filters['type'] !== 'all', function ($query) use ($filters) {
                $query->where('a.article_type_id', $filters['type']);
            })
            ->when($filters['date_start'], function ($query) use ($filters) {
                $query->whereDate('a.created_at', '>=', $filters['date_start']);
            })
            ->when($filters['date_end'], function ($query) use ($filters) {
                $query->whereDate('a.created_at', '<=', $filters['date_end']);
            })
            ->count();

        return Inertia::render('Dashboard', [
            'data' => $data,
            'role' => $role,
            'programs' => $programs,
            'areas' => $areas,
            'articleTypes' => $articleTypes,
            'totalFilteredArticles' => $totalFilteredArticles,
            'filters' => $filters,
            'articleCountsByType' => $articleCountsByType // NUEVO: Pasamos el nuevo dato
        ]);
    }
}