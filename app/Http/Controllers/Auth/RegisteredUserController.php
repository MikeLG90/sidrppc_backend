<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Area;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\Persona;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $oficinas = Oficina::all();
        $areas = Area::all();
        return view('auth.register', compact('areas', 'oficinas'));
    }

    public function createAdmin(): View
    {
        $areas = Area::all();
        $roles = Rol::all();
        return view('auth.register-admin', compact('areas', 'roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ape_materno' => ['required', 'string', 'max:255'],
            'ape_paterno' => ['required', 'string', 'max:255'],
            'genero' => ['required', 'string'],
            'area_id' => ['required', 'integer', 'exists:areas,area_id'],
        ]);
    
        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'rol_id' => 2,
            'password' => Hash::make($request->password),
        ]);
    
        // Obtener el ID personalizado
        $userId = $user->usuario_id;
    
        // Crear la persona asociada
        $persona = new Persona;
        $persona->usuario_id = $userId;
        $persona->nombre = $request->name;
        $persona->ape_materno = $request->ape_materno;
        $persona->ape_paterno = $request->ape_paterno;
        $persona->sexo = $request->genero;
        $persona->save();
    
        // Obtener ID de la persona creada
        $personaId = $persona->persona_id;
    
        // Obtener el área
        $area = Area::find($request->area_id);
    
        // Actualizar el usuario con la relación a persona, área y oficina
        $user->persona_id = $personaId;
        $user->area_id = $area->area_id;
        $user->oficina_id = $area->oficina_id;
        $user->save();
    
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $rol_id = $request->rol;
        $areaId = $request->area;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'rol_id' => $rol_id,
            'password' => Hash::make($request->password),
        ]);

        $userId = $user->usuario_id;

        $persona = new Persona;

        $persona->usuario_id = $userId;
        $persona->nombre = $request->name;
        $persona->ape_materno = $request->ape_materno;
        $persona->ape_paterno = $request->ape_paterno;
        $persona->sexo = $request->genero;
        $persona->save();

        $personaId = $persona->persona_id;

        $user = User::find($userId);
        $area = Area::find($areaId);

        $user->persona_id = $personaId;
        $user->area_id = $area->area_id;
        $user->oficina_id = $area->oficina_id;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
