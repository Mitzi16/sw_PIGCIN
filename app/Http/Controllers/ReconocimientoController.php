<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReconocimientoController extends Controller
{
    protected string $routeName;
    protected string $source;
    protected string $module = 'reconocimiento'; 
    protected User $model;

    public function __construct()
    {
        $this->routeName = "reconocimiento.";
        $this->source    = "Security/Reconocimiento/"; 
        $this->model     = new User();
        
        // Permisos para el nuevo módulo
        $this->middleware("permission:{$this->module}.index")->only(['index']);
        $this->middleware("permission:{$this->module}.update")->only(['update']);
    }

    /**
     * Muestra la lista de usuarios filtrados por roles específicos.
     */
    public function index(Request $request): Response
    {
        $direction = $request->input('direction', 'desc');
        $order = $request->input('order', 'created_at');
        $search = $request->input('search', '');
        $profile = $request->input('profile', null); // Para filtrar por un rol específico (ej. solo Talleristas)

        // Definimos los roles que queremos mostrar
        $rolesParticipantes = ['Tallerista', 'Conferencista', 'Staff', 'Asistente'];

        $records = $this->model->with('roles')
            // 1. Filtramos SÓLO usuarios que tengan alguno de los roles definidos
            ->whereHas('roles', function ($query) use ($rolesParticipantes) {
                $query->whereIn('name', $rolesParticipantes);
            });

        // 2. Aplicamos el orden
        $records = $records->orderBy($order, $direction);

        // 3. Aplicamos filtro de perfil 
        if ($profile) {
            $records = $records->whereHas('roles', function ($query) use ($profile) {
                $query->where('role_id', $profile);
            });
        }

        // 4. Aplicamos búsqueda general
        $records = $records->when($search, function ($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('roles', function ($query) use ($search) {
                      $query->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        });

        // 5. Paginamos y formateamos la salida
        $records = $records->paginate(8)->withQueryString()->through(
            fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles ? $user->roles->pluck('name') : null,
                'reconocimiento_habilitado' => (bool) $user->reconocimiento_habilitado,
            ]
        );

        return Inertia::render("{$this->source}ReconocimientoIndex", [
            'title'     => 'Gestión de Reconocimientos',
            'users'     => $records, 
            
            'profiles'  => Role::whereIn('name', $rolesParticipantes)->get(['id', 'name']),
            
            'routeName' => $this->routeName,
            'search'    => $search,
            'direction' => $direction,
            'profile'   => $profile
        ]);
    }

    /**
     * Actualiza el estado del reconocimiento de un usuario.
     * Esta es la función que llama el interruptor (toggle).
     */
    public function update(Request $request, User $user)
    {
        // 1. Validamos que el dato venga y sea un booleano
        $request->validate([
            'reconocimiento_habilitado' => 'required|boolean'
        ]);

        // 2. Usamos una transacción por seguridad
        try {
            DB::transaction(function () use ($request, $user) {
                $user->update([
                    'reconocimiento_habilitado' => $request->reconocimiento_habilitado
                ]);
            });

            // 3. Respondemos con éxito
            return redirect()->back()->with('success', 'Estado de reconocimiento actualizado.');

        } catch (\Exception $e) {
            // 4. Manejamos el error
            return redirect()->back()->with('error', 'No se pudo actualizar el estado.');
        }
    }
}