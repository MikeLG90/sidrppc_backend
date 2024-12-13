<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    public function index ()
    {
        $roles = Rol::all();
        $usuarios = DB::table('usuarios as u')
    ->join('oficinas as o', 'u.oficina_id', '=', 'o.oficina_id')
    ->join('areas as a', 'a.area_id', '=', 'u.area_id')
    ->join('dependencia as d', 'd.dep_id', '=', 'o.dep_id')
    ->join('cabeza_sector as c', 'd.cs_id', '=', 'c.cs_id')
    ->join('persona as p', 'p.persona_id', '=', 'u.persona_id')
    ->join('roles as r', 'u.rol_id', '=', 'r.rol_id')
    ->select(DB::raw('CONCAT(p.nombre, " ", p.ape_paterno, " ", p.ape_materno) as nombre'), 
             'a.area as departamento', 
             'o.oficina as delegacion', 
             'd.nombre as dependencia', 
             'u.usuario_id',
             'r.rol',
             'u.image',
             'c.nombre as cabeza_sector')
    ->get();

    return view('rppc.users', compact('usuarios', 'roles'));
    }


public function updateUser(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return back()->withErrors(['message' => 'Usuario no autenticado']);
    }

    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = Str::random(10) . '.' . $image->getClientOriginalExtension();

        try {
            $path = $image->storeAs('archivos', $imageName, 'ftp');
            $user->image = $imageName;
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error al guardar la imagen: ' . $e->getMessage()]);
        }
    }
    $user->save();

    return back()->with('success', 'Usuario actualizado correctamente');
}

    public function cambioRol(Request $request, $userId) {

        $user = User::findOrFail($userId);
        $user->rol_id = $request->rol;
        $user->save();
        return back()->with('success', 'Usuario actualizado correctamente');

    }

}
