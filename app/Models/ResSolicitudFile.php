<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResSolicitudFile extends Model
{
    use HasFactory;
    protected $table = 'solicitud_res';
    protected $primaryKey = 'solicitud_res_id';

    protected $fillable = ['resolucion_id', 'file_path'];
}
