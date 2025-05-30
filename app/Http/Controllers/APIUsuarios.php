<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folio;
use App\Models\User;
use App\Models\OficinaFolio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class APIUsuarios extends Controller
{
    public function getUsers()
    {
        $query = DB::table('usuarios as u')
        ->join('persona as p', 'p.usuario_id', '=', 'u.usuario_id')
        ->join('oficinas as o', 'o.oficina_id', '=', 'u.oficina_id')
        ->join('areas as a', 'a.area_id', '=', 'u.area_id')
        ->select(

            'a.area',
            DB::raw('p.nombre || \' \' || p.ape_paterno || \' \' || p.ape_materno as nombre_completo')
        );
    
    $usuarios = $query->get();

    return response()->json($usuarios);
    }
}
