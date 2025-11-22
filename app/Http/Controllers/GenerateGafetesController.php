<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class GenerateGafetesController extends Controller
{
    /**
     * Muestra la vista principal (el botón/modal) para generar gafetes.
     * Solo accesible por Admin.
     */
    public function generate()
    {
        return Inertia::render('Article/Diapositivas/Gafetes', [
            'title' => 'Generar Gafetes Masivos',
        ]);
    }

    /**
     * Obtiene la lista masiva de usuarios con sus datos para los gafetes.
     */
    public function getGafetesData()
    {
        try {
            // 1. Identificar usuarios que son "Postulantes" con exposición PRESENCIAL
            $presencialPostulantsIds = DB::table('articles')
                ->whereRaw('LOWER(expo_type) = ?', ['presencial'])
                ->whereNotNull('postulant_id')
                ->distinct()
                ->pluck('postulant_id')
                ->toArray();

            // 2. Obtener usuarios cargando roles, área e institución
            $users = User::with(['roles', 'knowledgeArea', 'institution']) // Agregamos 'institution'
                ->where(function ($query) use ($presencialPostulantsIds) {
                    $query->has('roles')
                          ->orWhereIn('id', $presencialPostulantsIds);
                })
                ->get();

            $gafetes = [];

            foreach ($users as $user) {
                // --- Lógica de Roles ---
                $roles = $user->roles->pluck('name');
                $roleForBadge = null;
                $isComite = false;

                // Jerarquía de Roles
                $hasEditor = $roles->contains('Editor');
                $hasRevisor = $roles->contains('Revisor');

                if ($hasEditor && $hasRevisor) {
                    $roleForBadge = 'DUAL_EDITOR_REVISOR';
                    $isComite = true;
                } elseif ($roles->contains('Admin')) {
                    $roleForBadge = 'Admin';
                    $isComite = true;
                } elseif ($roles->contains('Admin-Revista')) {
                    $roleForBadge = 'Admin-Revista';
                    $isComite = true;
                } elseif ($hasEditor) {
                    $roleForBadge = 'Editor';
                    $isComite = true;
                } elseif ($hasRevisor) {
                    $roleForBadge = 'Revisor';
                    $isComite = true;
                } elseif ($roles->contains('Staff')) {
                    $roleForBadge = 'Staff';
                } elseif ($roles->contains('Moderador')) {
                    $roleForBadge = 'Moderador';
                } elseif ($roles->contains('Tallerista')) {
                    $roleForBadge = 'Tallerista';
                } elseif ($roles->contains('Conferencista')) {
                    $roleForBadge = 'Conferencista';
                } elseif (in_array($user->id, $presencialPostulantsIds)) {
                    $roleForBadge = 'Postulante';
                } elseif ($roles->contains('Asistente')) {
                    $roleForBadge = 'Asistente';
                }

                // Si no califica para gafete, saltar
                if (!$roleForBadge) {
                    continue;
                }

                // --- Datos Adicionales ---
                
                // Área
                $areaName = 'General';
                if ($user->knowledgeArea) {
                    $areaName = $user->knowledgeArea->name;
                }

                // Institución (Nueva lógica)
                $institutionName = ''; // Dejar vacío si no tiene
                if ($user->institution) {
                    $institutionName = $user->institution->name; // Asumiendo que la tabla institutions tiene columna 'name'
                }

                // --- Construcción del objeto ---
                $gafetes[] = [
                    'user_id'     => $user->id,
                    'name'        => mb_strtoupper($user->name, 'UTF-8'),
                    'role'        => $roleForBadge,
                    'area'        => mb_strtoupper($areaName, 'UTF-8'),
                    'institution' => mb_strtoupper($institutionName, 'UTF-8'), // Dato nuevo
                    'is_comite'   => $isComite,
                    'email'       => $user->email
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $gafetes,
                'count' => count($gafetes)
            ]);

        } catch (\Exception $e) {
            Log::error("Error generando datos para gafetes: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener datos.'], 500);
        }
    }

    public function download($user_id)
    {
       
        $pathToFile = public_path('pdfs/info/Programa previo 5to CITCA 26nov.pdf'); 


        // 2. Verificar que el archivo físico realmente exista en el servidor antes de intentar servirlo.
        if (!File::exists($pathToFile)) {
            Log::error("Error en descarga de QR: No se encontró el archivo en: $pathToFile. Solicitado por usuario ID: $user_id");

            // Devolver un error 404 si el archivo no se ha subido al servidor.
            abort(404, 'El documento solicitado no está disponible en este momento.');
        }

        $downloadFileName = "Programa_del_Evento.pdf";

        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        
        return response()->download($pathToFile, $downloadFileName, $headers);
    }
}