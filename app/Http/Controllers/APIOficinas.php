<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folio;
use App\Models\User;
use App\Models\OficinaFolio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class APIOficinas extends Controller
{
    public function getOficinas()
    {
        $oficinas = OficinaFolio::all();

        return response()->json($oficinas);
    }
}
