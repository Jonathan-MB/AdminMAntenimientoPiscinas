<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\CambioPassword;
use App\Models\Hotel;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::with(['roles', 'hotel', 'ultimoCambioPassword.autor'])
            ->orderBy('nombre_usuario')
            ->get();
        $roles    = $this->rolesQuePuedeAsignar();
        $hoteles  = Hotel::where('activo', true)->orderBy('nombre')->get();

        return view('usuarios', compact('usuarios', 'roles', 'hoteles'));
    }



    public function store(StoreUsuarioRequest $request)
    {
        $datos = $request->validated();
        $roles = $datos['roles'];
        unset($datos['roles']);

        $error = $this->revisarRoles($roles);

        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        //  Solo el rol hotel lleva un hotel asignado
        if ($this->incluyeRol($roles, Rol::HOTEL)) {
            if (empty($datos['hotel_id'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Un usuario con rol hotel necesita un hotel asignado');
            }
        } else {
            $datos['hotel_id'] = null;
        }

        $usuario = Usuario::create($datos);

        //  La contraseña con la que se crea es provisional: la cambia el al entrar
        $usuario->debe_cambiar_password = true;
        $usuario->save();

        CambioPassword::anotar($usuario->id, Auth::id());

        $usuario->roles()->sync($roles);

        return redirect()->back()->with('mensajeCreado', 'Usuario creado correctamente');
    }



    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        // PATCH sin data
        if (empty($data) && $roles === null) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        $actual = Auth::user();

        //  Al master solo lo edita el master. Antes esto solo cubria roles y
        //  estado, asi que un administrador podia cambiarle la contraseña y
        //  quedarse con la cuenta.
        if ($usuario->esMaster() && ! $actual->esMaster()) {
            return response()->json([
                'message' => 'Solo el master puede modificar la cuenta del master'
            ], 403);
        }

        //  El rol master es exclusivo: no se cambia ni se desactiva, ni el suyo
        if ($usuario->esMaster() && ($roles !== null || isset($data['activo']))) {
            return response()->json([
                'message' => 'El usuario master no se puede modificar de esa forma'
            ], 403);
        }

        //  Un administrador no toca a otro administrador
        if ($usuario->esAdministrador() && ! $actual->esMaster() && $actual->id !== $usuario->id) {
            return response()->json([
                'message' => 'Solo el master puede modificar a un administrador'
            ], 403);
        }

        //  Nadie se deja a si mismo fuera de la aplicacion
        if ($actual->id === $usuario->id) {
            if (array_key_exists('activo', $data) && ! $data['activo']) {
                return response()->json([
                    'message' => 'No puedes desactivar tu propio usuario'
                ], 403);
            }

            if ($roles !== null && ! $this->incluyeRol($roles, Rol::ADMINISTRADOR)) {
                return response()->json([
                    'message' => 'No puedes quitarte a ti mismo el rol de administrador'
                ], 403);
            }
        }

        if ($roles !== null) {
            $error = $this->revisarRoles($roles);

            if ($error) {
                return response()->json(['message' => $error], 403);
            }
        }

        //  Solo el rol hotel lleva hotel asignado, igual que al crear
        if ($roles !== null || array_key_exists('hotel_id', $data)) {
            $rolesFinales = $roles ?? $usuario->roles->pluck('id')->all();

            if ($this->incluyeRol($rolesFinales, Rol::HOTEL)) {
                $hotelId = array_key_exists('hotel_id', $data) ? $data['hotel_id'] : $usuario->hotel_id;

                if (empty($hotelId)) {
                    return response()->json([
                        'message' => 'Un usuario con rol hotel necesita un hotel asignado'
                    ], 422);
                }

                $data['hotel_id'] = $hotelId;
            } else {
                $data['hotel_id'] = null;
            }
        }

        //  Si un administrador le pone la contraseña a otro, esa clave es
        //  provisional: solo sirve para volver a entrar y elegir la suya.
        //  Cambiarse la propia desde aqui no obliga a nada.
        if (array_key_exists('password', $data) && $actual->id !== $usuario->id) {
            $usuario->debe_cambiar_password = true;
        }

        // Cargar datos sin guardar
        $usuario->fill($data);

        $cambioRoles = $roles !== null && $usuario->roles->pluck('id')->sort()->values()->all() !== collect($roles)->map('intval')->sort()->values()->all();

        // No hubo cambios
        if (! $usuario->isDirty() && ! $cambioRoles) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $usuario->save();

        if (array_key_exists('password', $data)) {
            CambioPassword::anotar($usuario->id, $actual->id);
        }

        if ($cambioRoles) {
            $usuario->roles()->sync($roles);
        }

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $usuario->fresh()->load('roles')
        ], 200);
    }



    public function destroy(Request $request, Usuario $usuario)
    {
        $actual = Auth::user();

        //  El master no lo elimina nadie, nunca
        if ($usuario->esMaster()) {
            return response()->json([
                'message' => 'El usuario master no se puede eliminar'
            ], 403);
        }

        //  Nadie se elimina a si mismo
        if ($actual->id === $usuario->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario'
            ], 403);
        }

        //  Con varios roles gana la proteccion mas fuerte: si entre sus roles
        //  esta administrador, solo el master puede eliminarlo
        if ($usuario->esAdministrador() && ! $actual->esMaster()) {
            return response()->json([
                'message' => 'Solo el master puede eliminar a un administrador'
            ], 403);
        }

        //  Si dejo trabajo hecho, borrarlo dejaria huecos en el historico. La
        //  base ya lo impide; sin esto la pantalla mostraba el error de SQL en
        //  crudo, con un 500.
        $rastro = $this->rastroDe($usuario);

        if ($rastro !== null) {
            return response()->json([
                'message' => "No se puede eliminar: $rastro. Desactívalo en vez de eliminarlo."
            ], 409);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }



    //  Lo que impide borrarlo, en palabras. Devuelve null si no dejo rastro.
    private function rastroDe(Usuario $usuario): ?string
    {
        $cuentas = [
            ['jornada',    'jornadas',    $usuario->jornadas()->count()],
            ['medición',   'mediciones',  $usuario->mediciones()->count()],
            ['corrección', 'correcciones', $usuario->cambios()->count()],
            ['ticket',     'tickets',     $usuario->tickets()->count()],
        ];

        $partes = [];

        foreach ($cuentas as $cuenta) {
            list($singular, $plural, $cuantos) = $cuenta;

            if ($cuantos > 0) {
                $partes[] = $cuantos . ' ' . ($cuantos === 1 ? $singular : $plural);
            }
        }

        if (! $partes) {
            return null;
        }

        return 'tiene ' . implode(', ', $partes) . ' a su nombre';
    }



    //  El master es exclusivo: no se asigna desde la pantalla ni convive con otros roles
    private function revisarRoles(array $roles): ?string
    {
        $master = Rol::where('nombre', Rol::MASTER)->first();

        if ($master && in_array($master->id, array_map('intval', $roles), true)) {
            return 'El rol master no se asigna desde aqui';
        }

        return null;
    }



    private function incluyeRol(array $roles, string $nombre): bool
    {
        return Rol::whereIn('id', $roles)->where('nombre', $nombre)->exists();
    }



    //  El master tampoco asigna master desde la pantalla: ese rol se siembra
    private function rolesQuePuedeAsignar()
    {
        return Rol::where('nombre', '!=', Rol::MASTER)->orderBy('id')->get();
    }
}
