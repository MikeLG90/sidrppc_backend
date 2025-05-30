<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\Persona;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                "unique:" . User::class,
            ],
            "password" => ["required", "confirmed", Rules\Password::defaults()],
            "ape_materno" => ["required", "string", "max:255"],
            "ape_paterno" => ["required", "string", "max:255"],
            "genero" => ["required", "string", "in:M,F"], // Asegúrate de que el género sea M o F
            "area_id" => ["required", "integer", "exists:areas,area_id"], // Valida que el área exista
        ]);

        $rol_id = 2; // Rol por defecto para usuarios normales
        $areaId = $request->area_id;

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "rol_id" => $rol_id,
            "password" => Hash::make($request->password),
        ]);

        // Crear la persona asociada al usuario
        $persona = new Persona();
        $persona->usuario_id = $user->usuario_id;
        $persona->nombre = $request->name;
        $persona->ape_materno = $request->ape_materno;
        $persona->ape_paterno = $request->ape_paterno;
        $persona->sexo = $request->genero;
        $persona->save();

        // Asignar el área y la oficina al usuario
        $area = Area::find($areaId);
        $user->persona_id = $persona->persona_id;
        $user->area_id = $area->area_id;
        $user->oficina_id = $area->oficina_id;
        $user->save();

        // Disparar el evento de registro
        event(new Registered($user));

        // Iniciar sesión automáticamente (opcional)
        Auth::login($user);

        // Devolver una respuesta JSON
        return response()->json(
            [
                "message" => "Usuario registrado exitosamente",
                "user" => $user,
                "persona" => $persona,
            ],
            201
        );
    }

    public function login(Request $request)
    {
        // validar datos de entrada

        $request->validate([
            "email" => ["required", "string", "email"],
            "password" => ["required", "string"],
        ]);

        //  intentar autenticar usuario

        if (
            Auth::attempt([
                "email" => $request,
                "password" => $request->password,
            ])
        ) {
            $user = Auth::user();
            $token = $user->createToken("authToken")->plainTextToken;

            $usuario = DB::table("rppc.usuarios as u")
                ->select([
                    "u.usuario_id",
                    "u.email",
                    "u.image",
                    "u.oficina_id",
                    DB::raw(
                        "CONCAT(p.nombre, ' ', p.ape_paterno, ' ', p.ape_materno) AS nombre_completo"
                    ),
                    "r.rol",
                    "r.rol_id",
                    "o.oficina",
                ])
                ->join("rppc.persona as p", "p.usuario_id", "=", "u.usuario_id")
                ->join("rppc.roles as r", "r.rol_id", "=", "u.rol_id")
                ->join(
                    "rppc.oficinas as o",
                    "u.oficina_id",
                    "=",
                    "o.oficina_id"
                )
                ->where("u.usuario_id", $user->usuario_id)
                ->first();

            return response()->json(
                [
                    "message" => "Login exitoso",
                    "user" => $usuario,
                    "token" => $token,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    "message" => "Credenciales Inválidas",
                ],
                401
            );
        }
    }
}
