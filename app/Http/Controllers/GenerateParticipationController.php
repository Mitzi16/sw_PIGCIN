<?php

namespace App\Http\Controllers;

// Importaciones necesarias
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class GenerateParticipationController extends Controller
{
    /**
     * Muestra la página (o el modal) para que el usuario
     * genere su reconocimiento personal.
     */
    public function generateParticipation(Request $request): Response
    {
        return Inertia::render('Article/Diapositivas/Participation', [
            'title' => 'Generar mi Reconocimiento',
        ]);
    }

    /**
     * Obtiene los datos del usuario autenticado para
     * generar el reconocimiento.
     */
    public function getReconocimientoData()
    {
        // 1. Obtener el usuario autentificado
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // 2. Cargar 'roles'
        $user->load('roles'); 

        // 3. Preparar los datos del usuario
        $participant_name = $user->name;
        $participant_name = mb_strtoupper($participant_name, 'UTF-8');
        
        $roles = $user->roles->pluck('name');
        $hasEditor = $roles->contains('Editor');
        $hasRevisor = $roles->contains('Revisor');
        $participant_role = 'No especificado'; 

        //  Lógica de Prioridad (incluyendo nuevos roles)
        if ($hasEditor && $hasRevisor) {
            $participant_role = 'DUAL_EDITOR_REVISOR';
        } 
        elseif ($roles->contains('Tallerista')) {
            $participant_role = 'Tallerista';
        } elseif ($roles->contains('Conferencista')) {
            $participant_role = 'Conferencista';
        }
        elseif ($roles->contains('Admin')) {
            $participant_role = 'Admin';
        } elseif ($roles->contains('Admin-Revista')) {
            $participant_role = 'Admin-Revista';
        }
        elseif ($roles->contains('Staff')) {
            $participant_role = 'Staff';
        } elseif ($roles->contains('Moderador')) {
            $participant_role = 'Moderador';
        }
        elseif ($hasEditor) {
            $participant_role = 'Editor';
        } elseif ($hasRevisor) {
            $participant_role = 'Revisor';
            
            
            // 1. Contar cuántos artículos tiene asignados este revisor
            $articlesAssigned = DB::table('article_reviews')
                ->where('reviewer_id', $user->id)
                ->count();

            // 2. Si el conteo es 0, no puede generar el reconocimiento
            if ($articlesAssigned === 0) {
                Log::warning("Revisor {$user->id} intentó generar reconocimiento, pero no tiene artículos asignados.");
                return response()->json([
                    'error' => 'No puedes generar tu reconocimiento. Debes tener al menos un artículo asignado. Por favor, contacte al administrador.'
                ], 403); // 403 = Prohibido
            }


        }
        elseif ($roles->contains('Asistente')) {
            $participant_role = 'Asistente';
        } elseif ($user->roles->isNotEmpty()) {
            $participant_role = $user->roles->first()->name;
        }
        
        $participant_role = mb_strtoupper($participant_role, 'UTF-8');

        
        //  Definir roles que SÍ necesitan la verificación
        $rolesConVerificacion = [
            'TALLERISTA', 
            'CONFERENCISTA', 
            'STAFF', 
            'ASISTENTE'
        ];

        //  Ejecutar la comprobación
        if (in_array($participant_role, $rolesConVerificacion)) {
            // Este rol SÍ necesita verificar el campo 'reconocimiento_habilitado'
            
            // Usamos !$user->reconocimiento_habilitado
            if (!$user->reconocimiento_habilitado) {
                // Si está deshabilitado (0), bloqueamos al usuario.
                Log::warning("Usuario {$user->id} (Rol: {$participant_role}) intentó generar reconocimiento, pero 'reconocimiento_habilitado' es 0.");
                return response()->json([
                    'error' => 'La generación de reconocimientos no está habilitada para su cuenta en este momento. Por favor, contacte al administrador.'
                ], 403); // 403 = Prohibido
            }
            // Si $user->reconocimiento_habilitado es 1, el código simplemente continúa.
        }

        // 4. Obtener el 'area' usando el 'knowledge_area_id'
        $participant_area = 'No especificada'; 
        if ($user->knowledge_area_id) {
            try {
                $area = DB::table('knowledge_areas')->find($user->knowledge_area_id);
                if ($area) {
                    $participant_area = $area->name; 
                }
            } catch (\Exception $e) {
                Log::error("Error al buscar knowledge_area_id {$user->knowledge_area_id}: " . $e->getMessage());
            }
        }
        $participant_area = mb_strtoupper($participant_area, 'UTF-8');

        // 5. Generar el sello digital
        $signedText = "Error al generar sello"; // Valor por defecto
        try {
            $signedText = $this->generateSignature($user); 
        } catch (\Exception $e) {
            Log::error("Error al generar firma para reconocimiento (user {$user->id}): " . $e->getMessage());
        }

        // Folio único
        $reconocimiento_folio = 'REC-' . $user->id . '-' . now()->timestamp;

        // 6. Construir el objeto de datos para el JSON
        $data = [
            'user_id' => $user->id,
            'reconocimiento_folio' => $reconocimiento_folio,
            'participant_name' => $participant_name,
            'participant_role' => $participant_role,
            'participant_area' => $participant_area,
            'signedText' => $signedText,
            'verification_url' => route('reconocimiento.verify', $reconocimiento_folio),
        ];

        return response()->json($data);
    }

    /**
     * Genera la firma digital (sello) para un RECONOCIMIENTO
     */
    private function generateSignature(User $user): string
    {
        $varCER = 'VIICyT/VIICyT.cer';
        $varKEY = 'VIICyT/VIICyT.key';
        $keyPasswd = 'viicyt2024';

        $dataCER = Storage::disk('public')->get($varCER);
        if (!$dataCER) {
            Log::error("No se encontró el archivo .cer en storage/app/public/{$varCER}");
            throw new \Exception('No se encontró el archivo .cer.');
        }

        $dataKEY = Storage::disk('public')->get($varKEY);
        if (!$dataKEY) {
            Log::error("No se encontró el archivo .key en storage/app/public/{$varKEY}");
            throw new \Exception('No se encontró el archivo .key.');
        }

        $resKEY = openssl_pkey_get_private($dataKEY, $keyPasswd);
        if ($resKEY === false) {
            Log::error("openssl_pkey_get_private falló. ¿Contraseña incorrecta o clave corrupta?");
            throw new \Exception('No se pudo obtener la clave privada. Verifique la contraseña.');
        }

        $string = '||' . $user->id . '||' . $user->name . '||' . $user->email . '||RECONOCIMIENTO||';
        $stringMD5 = md5($string); // Corregido

        $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);
        openssl_pkey_free($resKEY);

        if (!$success) {
            throw new \Exception('Fallo al encriptar (firmar) el string: ' . openssl_error_string());
        }

        return base64_encode($encrypted_string);
    }

    /**
     * Verifica la validez de un folio de reconocimiento.
     */
    public function verify(Request $request, $folio)
    {
       // Limpieza básica del folio
        $cleanFolio = preg_replace('/[?#].*$/', '', $folio);
        
        // Vista por defecto para errores
        $errorViewData = [
            'data' => false,
            'folio' => $cleanFolio,
            'participant_name' => '',
            'participant_role' => '',
            'selloDigital' => '',
            'signedText' => ''
        ];

        try {
            $parts = explode('-', $cleanFolio);

            // 1. Validación de formato
            if ($parts[0] !== 'REC' || !isset($parts[1]) || !is_numeric($parts[1])) {
                Log::warning("Folio con formato incorrecto: {$folio}");
                return view('RverificationParticipation', $errorViewData);
            }

            $userId = $parts[1];

            // 2. Buscar usuario
            $user = User::with('roles')->find($userId);

            if (!$user) {
                Log::warning("Folio válido pero usuario no encontrado: {$folio}");
                return view('RverificationParticipation', $errorViewData);
            }

            // 3. Preparar datos para la vista
            $userName = mb_strtoupper($user->name, 'UTF-8');
            $userRole = 'PARTICIPANTE'; // Valor por defecto

            if ($user->roles->isNotEmpty()) {
                // Toma el primer rol y lo convierte a mayúsculas
                $userRole = mb_strtoupper($user->roles->first()->name, 'UTF-8');
            }

            // 4. Regenerar el Sello Digital
            // Como el sello depende de datos estáticos del usuario (id, name, email),
            // lo recalculamos aquí para mostrarlo en la validación.
            $selloDigital = "Firma no disponible";
            try {
                $selloDigital = $this->generateSignature($user);
            } catch (\Exception $e) {
                Log::error("Error regenerando firma en verify: " . $e->getMessage());
            }

            // 5. Retornar la vista Exitosa
            return view('RverificationParticipation', [
                'data' => true, // Bandera para que el Blade muestre la sección verde
                'participant_name' => $userName,
                'participant_role' => $userRole,
                'selloDigital'     => $selloDigital, // El texto encriptado largo
                'signedText'       => $cleanFolio,   // En tu blade, el segundo textarea muestra el folio usando esta variable
                'folio'            => $cleanFolio    // Por redundancia
            ]);

        } catch (\Exception $e) {
            Log::error("Error crítico en verificación de folio {$folio}: " . $e->getMessage());
            return view('RverificationParticipation', $errorViewData);
        }
    }
    
}