<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoSolicitud extends Model
{
    use HasFactory;

    protected $table = 'archivos_solicitudes';
    protected $fillable = ['solicitud_id', 'file_path'];


    public function solicitud()
    {
        return $this->belongsTo(SolicitudResolucion::class, 'solicitud_id');
    }
}
