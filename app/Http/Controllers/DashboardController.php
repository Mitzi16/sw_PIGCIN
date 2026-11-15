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
        $user = $request->user();
        $role = $user->getRoleNames()->first();

        // 1. Determinamos si es SuperAdmin o un Admin normal
        $isSuperAdmin = $user->hasRole('SuperAdmin') || $user->hasRole('Admin');

        // 2. Obtenemos los IDs de las convocatorias asignadas al Admin
        $myCallIds = $user->calls->pluck('id')->toArray();

        $filters = [
            'program_id' => $request->input('program_id', 'all') ?? 'all',
            'area_id'    => $request->input('area_id', 'all') ?? 'all',
            'type'       => $request->input('type', 'all') ?? 'all',
            'date_start' => $request->input('date_start'),
            'date_end'   => $request->input('date_end')
        ];

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 3. Construimos las consultas condicionalmente
        if ($isSuperAdmin) {
            // SuperAdmin ve TODO
            $postulantsQuery = User::role('Postulante');
            $articlesQuery = Article::query();
            $callsQuery = Call::query();
        } else {
            // Admin ve SOLO lo de sus convocatorias
            // Filtramos usuarios que estén inscritos a las convocatorias del admin
            $postulantsQuery = User::role('Postulante')->whereHas('calls', function ($q) use ($myCallIds) {
                $q->whereIn('calls.id', $myCallIds);
            });

            // Filtramos artículos por el call_id (asumiendo que tu tabla articles tiene call_id)
            // Si se relaciona a través del postulante, sería: ->whereHas('postulant.calls', ...)
            $articlesQuery = Article::whereIn('call_id', $myCallIds);

            // Filtramos las convocatorias
            $callsQuery = Call::whereIn('id', $myCallIds);
        }

        // 4. Calculamos las estadísticas con las consultas filtradas
        $data = [
            'postulants' => (clone $postulantsQuery)->count(),
            'newPostulantsThisWeek' => (clone $postulantsQuery)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),

            'articles' => (clone $articlesQuery)->count(),
            'newArticlesThisWeek' => (clone $articlesQuery)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),

            'calls' => (clone $callsQuery)->count(),
            'callsActives' => (clone $callsQuery)->where('status', true)->count(),
        ];

        // 5. Conteo de artículos por tipo filtrado
        $articleCountsByType = (clone $articlesQuery)
            ->select('article_type_id', DB::raw('count(*) as count'))
            ->groupBy('article_type_id')
            ->get();

        // Programas, áreas y tipos se mantienen globales para los filtros
        $programsAndAreas = DB::table('knowledge_areas as ka')
            ->leftJoin('knowledge_areas as area', 'area.parent_id', '=', 'ka.id')
            ->whereNull('ka.parent_id')
            ->select('ka.id as program_id', 'ka.name as program_name', 'area.id as area_id', 'area.name as area_name')
            ->orderBy('ka.id')
            ->get();

        $programs = $programsAndAreas->unique('program_id')->map(fn($item) => ['id' => $item->program_id, 'name' => $item->program_name])->values();
        $areas = $programsAndAreas->filter(fn($item) => $item->area_id !== null)->unique('area_id')->map(fn($item) => ['id' => $item->area_id, 'name' => $item->area_name, 'program_id' => $item->program_id])->values();
        $articleTypes = DB::table('article_types')->select('id', 'name')->orderBy('id')->get();

        // Conteo de artículos filtrados para la gráfica de barras
        $totalFilteredArticles = (clone $articlesQuery)
            ->join('knowledge_areas as ka', 'articles.knowledge_area_id', '=', 'ka.id')
            ->join('knowledge_areas as parent', 'ka.parent_id', '=', 'parent.id')
            ->when($filters['program_id'] !== 'all', fn($q) => $q->where('parent.id', $filters['program_id']))
            ->when($filters['area_id'] !== 'all', fn($q) => $q->where('ka.id', $filters['area_id']))
            ->when($filters['type'] !== 'all', fn($q) => $q->where('articles.article_type_id', $filters['type']))
            ->when($filters['date_start'], fn($q) => $q->whereDate('articles.created_at', '>=', $filters['date_start']))
            ->when($filters['date_end'], fn($q) => $q->whereDate('articles.created_at', '<=', $filters['date_end']))
            ->count();

        return Inertia::render('Dashboard', [
            'data' => $data,
            'role' => $role,
            'programs' => $programs,
            'areas' => $areas,
            'articleTypes' => $articleTypes,
            'totalFilteredArticles' => $totalFilteredArticles,
            'filters' => $filters,
            'articleCountsByType' => $articleCountsByType
        ]);
    }
}
