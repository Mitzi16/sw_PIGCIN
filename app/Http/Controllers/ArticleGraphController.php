<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Article;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class ArticleGraphController extends Controller
{
   public function institution()
    {
    $institutions = DB::table('institutions')
        ->join('authors', 'institutions.id', '=', 'authors.institution_id')
        ->whereNotNull('authors.article_id')
        ->where('authors.is_corresponding', 1) 
        ->select(
            'institutions.id',
            'institutions.name',
            DB::raw('COUNT(authors.article_id) as articles_count')
        )
        ->groupBy('institutions.id', 'institutions.name')
        ->orderByDesc('articles_count')
        ->get();

    return Inertia::render('Graphs/ArticlesByInstitution', [
        'institutions' => $institutions
    ]);
    }

   public function articleType()
{
    // Lista de tipos con sus totales
    $articleTypes = DB::table('articles')
        ->join('article_types', 'articles.article_type_id', '=', 'article_types.id')
        ->select(
            'article_types.id',
            'article_types.name',
            DB::raw('COUNT(articles.id) as total')
        )
        ->groupBy('article_types.id', 'article_types.name')
        ->orderBy('article_types.id')
        ->get();

    // Lista de artículos individuales (para filtrar por fecha en Vue)
    $articles = DB::table('articles')
        ->select('id', 'article_type_id', 'created_at')
        ->orderBy('created_at')
        ->get();

    return Inertia::render('Graphs/ArticlesByType', [
        'articleTypes' => $articleTypes,
        'articles' => $articles
    ]);
}

    public function status()
    {
    $statuses = DB::table('articles')
        ->join('article_statuses', 'articles.article_status_id', '=', 'article_statuses.id')
        ->select('article_statuses.id', 'article_statuses.name', DB::raw('COUNT(articles.id) as total'))
        ->groupBy('article_statuses.id', 'article_statuses.name')
        ->orderBy('article_statuses.id')
        ->get();

    return Inertia::render('Graphs/ArticlesByStatus', [
        'statuses' => $statuses
    ]);
    }

    public function date()
    {
    $articlesByDate = DB::table('articles')
        ->select(
            DB::raw('DATE(created_at) as date'), 
            DB::raw('COUNT(*) as total'),
            DB::raw('DAY(created_at) as day'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year')
        )
        ->groupBy('date', 'day', 'month', 'year') // Agrega todas las columnas no agregadas
        ->orderBy('date', 'ASC')
        ->get();

    return Inertia::render('Graphs/ArticlesByDate', [
        'articlesByDate' => $articlesByDate,
        'dateRange' => [
            'start' => $articlesByDate->first()->date ?? null,
            'end' => $articlesByDate->last()->date ?? null
        ]
    ]);
    }

    public function articlesByRole()
    {
        // Obtener solo el rol Postulante
    $role = Role::where('name', 'Postulante')->firstOrFail();
    
    // Obtener usuarios postulantes con sus artículos
    $postulants = User::role($role->name)
        ->withCount('articles')
        ->has('articles', '>', 0)
        ->orderBy('name')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'articles_count' => $user->articles_count,
            ];
        });
    
    // Calcular totales
    $totalArticles = $postulants->sum('articles_count');
    $totalUsers = $postulants->count();
    
    return Inertia::render('Graphs/ArticlesByUser', [
        'users' => $postulants,
        'totals' => [
            'articles' => $totalArticles,
            'users' => $totalUsers,
            'avg_articles_per_user' => $totalUsers > 0 ? round($totalArticles / $totalUsers, 1) : 0,
        ]
    ]);
    }

    public function articlesByProgram()
    {   
    $programsData = DB::table('knowledge_areas as ka')
        ->leftJoin('knowledge_areas as area', 'area.parent_id', '=', 'ka.id')
        ->leftJoin('articles as art', 'art.knowledge_area_id', '=', 'area.id')
        ->select(
            'ka.id as program_id',
            'ka.name as program_name',
            DB::raw('COUNT(art.id) as total_articles')
        )
        ->whereNull('ka.parent_id')
        ->groupBy('ka.id', 'ka.name')
        ->orderBy('ka.name')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->program_id,
                'name' => $item->program_name,
                'total' => (int)$item->total_articles
            ];
        });

    return Inertia::render('Graphs/ArticlesByProgram', [
        'chartData' => $programsData
    ]);
    }

    public function articlesByArea()
    {
    $programsAndAreas = DB::table('knowledge_areas as ka')
        ->leftJoin('knowledge_areas as area', 'area.parent_id', '=', 'ka.id')
        ->leftJoin('articles as art', 'art.knowledge_area_id', '=', 'area.id')
        ->select(
            'ka.id as program_id',
            'ka.name as program_name',
            'area.id as area_id',
            'area.name as area_name',
            DB::raw('COUNT(art.id) as total_articles')
        )
        ->whereNull('ka.parent_id') // Solo programas (padres)
        ->whereNotNull('area.id') // Solo áreas que existen
        ->groupBy('ka.id', 'ka.name', 'area.id', 'area.name')
        ->orderBy('ka.name')
        ->orderBy('area.name')
        ->get();

    // Preparar datos para la gráfica
    $chartData = $programsAndAreas->map(function ($item) {
        return [
            'id' => $item->area_id,
            'name' => $item->area_name,
            'total' => (int)$item->total_articles,
            'program' => $item->program_name // Agregamos el programa para referencia
        ];
    });

    return Inertia::render('Graphs/ArticlesByArea', [
        'chartData' => $chartData
    ]);
    }
}