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
    public function generateParticipation(Request $request): Response
    {
        return Inertia::render('Article/Diapositivas/Participation', [
            'title' => 'Generar mi Reconocimiento',
        ]);
    }

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

        //  Lógica de Prioridad de Roles
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
            
            $articlesAssigned = DB::table('article_reviews')
                ->where('reviewer_id', $user->id)
                ->count();

            if ($articlesAssigned === 0) {
                Log::warning("Revisor {$user->id} sin artículos.");
                return response()->json([
                    'error' => 'No puedes generar tu reconocimiento. Debes tener al menos un artículo asignado.'
                ], 403); 
            }
        }
        elseif ($roles->contains('Asistente')) {
            $participant_role = 'Asistente';
        } elseif ($user->roles->isNotEmpty()) {
            $participant_role = $user->roles->first()->name;
        }
        
        $participant_role = mb_strtoupper($participant_role, 'UTF-8');

        //  Verificación de roles especiales
        $rolesConVerificacion = ['TALLERISTA', 'CONFERENCISTA', 'STAFF', 'ASISTENTE'];

        if (in_array($participant_role, $rolesConVerificacion)) {
            if (!$user->reconocimiento_habilitado) {
                Log::warning("Usuario {$user->id} reconocimiento deshabilitado.");
                return response()->json([
                    'error' => 'La generación de reconocimientos no está habilitada para su cuenta.'
                ], 403); 
            }
        }

        // 4. Obtener area o Programa de Estudio según el rol
            $participant_area = 'No especificada'; 

            if ($user->knowledge_area_id) {
                try {
                    $areaAsignada = DB::table('knowledge_areas')->find($user->knowledge_area_id);
                    
                    if ($areaAsignada) {
                        // Verificamos si el rol es Editor o el Dual (Editor-Revisor)
                        // Nota: Usamos las variables que ya definiste en el paso 3
                        $esEditorEspecial = ($participant_role === 'EDITOR' || $participant_role === 'DUAL_EDITOR_REVISOR');

                        if ($esEditorEspecial && $areaAsignada->parent_id) {
                            // Si es Editor, intentamos extraer el Programa de Estudio (el padre)
                            $programaEstudio = DB::table('knowledge_areas')->find($areaAsignada->parent_id);
                            $participant_area = $programaEstudio ? $programaEstudio->name : $areaAsignada->name;
                        } else {
                            // Para todos los demás roles (o si no hay padre), se queda con el área actual
                            $participant_area = $areaAsignada->name;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al extraer area/programa: " . $e->getMessage());
                }
            }
            $participant_area = mb_strtoupper($participant_area, 'UTF-8');

        // 5. Generar sello
        $signedText = "Error al generar sello"; 
        try {
            $signedText = $this->generateSignature($user); 
        } catch (\Exception $e) {
            Log::error("Error firma: " . $e->getMessage());
        }
        
        // 1. Limpiamos espacios del rol
        $roleForFolio = str_replace(' ', '-', $participant_role);
        
        // 2. Semilla Única: ID + Fecha Creación.
        // Esto garantiza que el número base sea único para el usuario y no cambie con el tiempo.
        $seed = $user->id . ($user->created_at ? $user->created_at->format('YmdHis') : 'static');
        
        // 3. Generamos un hash numérico (CRC32)
        $hash = sprintf('%u', crc32($seed)); 
        
        // 4. Recortamos a 4 dígitos exactos.
        // Si el hash fuera "38129048", nos quedamos con "3812".
        $fourDigits = substr($hash, 0, 4);

        // Resultado: REC-688-CONFERENCISTA-3812
        $reconocimiento_folio = 'REC-' . $user->id . '-' . $roleForFolio . '-' . $fourDigits;
        

        $data = [
            'user_id' => $user->id,
            'reconocimiento_folio' => $reconocimiento_folio,
            'folio_number' => $fourDigits,
            'participant_name' => $participant_name,
            'participant_role' => $participant_role,
            'participant_area' => $participant_area,
            'signedText' => $signedText,
            'verification_url' => route('reconocimiento.verify', $reconocimiento_folio),
        ];

        return response()->json($data);
    }

    private function generateSignature(User $user): string
    {
        $varCER = 'VIICyT/VIICyT.cer';
        $varKEY = 'VIICyT/VIICyT.key';
        $keyPasswd = 'viicyt2024';

        $dataCER = Storage::disk('public')->get($varCER);
        if (!$dataCER) throw new \Exception('No se encontró el archivo .cer.');

        $dataKEY = Storage::disk('public')->get($varKEY);
        if (!$dataKEY) throw new \Exception('No se encontró el archivo .key.');

        $resKEY = openssl_pkey_get_private($dataKEY, $keyPasswd);
        if ($resKEY === false) throw new \Exception('Clave privada incorrecta.');

        $string = '||' . $user->id . '||' . $user->name . '||' . $user->email . '||RECONOCIMIENTO||';
        $stringMD5 = md5($string); 

        $success = openssl_private_encrypt($stringMD5, $encrypted_string, $resKEY);
        openssl_pkey_free($resKEY);

        if (!$success) throw new \Exception('Fallo al firmar.');

        return base64_encode($encrypted_string);
    }

    public function verify(Request $request, $folio)
    {
        $cleanFolio = preg_replace('/[?#].*$/', '', $folio);
        
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

            // Validación: REC en [0] y ID numérico en [1]
            if ($parts[0] !== 'REC' || !isset($parts[1]) || !is_numeric($parts[1])) {
                Log::warning("Folio formato incorrecto: {$folio}");
                return view('RverificationParticipation', $errorViewData);
            }

            $userId = $parts[1];
            $user = User::with('roles')->find($userId);

            if (!$user) {
                return view('RverificationParticipation', $errorViewData);
            }

            $userName = mb_strtoupper($user->name, 'UTF-8');
            $userRole = 'PARTICIPANTE'; 
            if ($user->roles->isNotEmpty()) {
                $userRole = mb_strtoupper($user->roles->first()->name, 'UTF-8');
            }

            $selloDigital = "Firma no disponible";
            try {
                $selloDigital = $this->generateSignature($user);
            } catch (\Exception $e) {
                Log::error("Error firma verify: " . $e->getMessage());
            }

            return view('RverificationParticipation', [
                'data' => true, 
                'participant_name' => $userName,
                'participant_role' => $userRole,
                'selloDigital'     => $selloDigital, 
                'signedText'       => $cleanFolio,  
                'folio'            => $cleanFolio 
            ]);

        } catch (\Exception $e) {
            Log::error("Error verify: " . $e->getMessage());
            return view('RverificationParticipation', $errorViewData);
        }
    }
}