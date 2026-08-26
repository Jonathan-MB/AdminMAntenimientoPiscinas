<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = Usuario::with('rol')->orderBy('nombre_usuario')->get();
        $roles    = $this->rolesQuePuedeAsignar();

        return view('usuarios', compact('usuarios', 'roles'));
    }



    public function store(StoreUsuarioRequest $request)
    {
        $datos = $request->validated();

        //  Nadie crea un master desde la pantalla, ni siquiera el master
        $rol = Rol::find($datos['rol_id']);

        if ($rol->nombre === Rol::MASTER) {
            return redirect()->back()->with('error', 'El rol master no se asigna desde aqui');
        }

        Usuario::create($datos);

        return redirect()->back()->with('mensajeCreado', 'Usuario creado correctamente');
    }



    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return response()->json([
                'message' => 'Sin datos'
            ], 422);
        }

        //  Al master no se le cambia el rol ni se le desactiva
        if ($usuario->esMaster() && (isset($data['rol_id']) || isset($data['activo']))) {
            return response()->json([
                'message' => 'El usuario master no se puede modificar de esa forma'
            ], 403);
        }

        //  Un administrador no toca a otro administrador
        if ($usuario->esAdministrador() && ! Auth::user()->esMaster() && Auth::id() !== $usuario->id) {
            return response()->json([
                'message' => 'Solo el master puede modificar a un administrador'
            ], 403);
        }

        //  Nadie asciende a nadie a master
        if (isset($data['rol_id'])) {
            $rol = Rol::find($data['rol_id']);

            if ($rol->nombre === Rol::MASTER) {
                return response()->json([
                    'message' => 'El rol master no se asigna desde aqui'
                ], 403);
            }
        }

        // Cargar datos sin guardar
        $usuario->fill($data);

        // No hubo cambios
        if (! $usuario->isDirty()) {
            return response()->json([
                'message' => 'No se detectaron cambios'
            ], 422);
        }

        $usuario->save();

        return response()->json([
            'message' => 'Actualizado Correctamente',
            'data'    => $usuario->fresh()->load('rol')
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

        //  A un administrador solo lo elimina el master
        if ($usuario->esAdministrador() && ! $actual->esMaster()) {
            return response()->json([
                'message' => 'Solo el master puede eliminar a un administrador'
            ], 403);
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }



    //  El master tampoco asigna master desde la pantalla: ese rol se siembra
    private function rolesQuePuedeAsignar()
    {
        return Rol::where('nombre', '!=', Rol::MASTER)->orderBy('nombre')->get();
    }
}
