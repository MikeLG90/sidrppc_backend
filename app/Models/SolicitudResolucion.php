<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudResolucion extends Model
{
    use HasFactory;
    protected $table = 'solicitudes_resoluciones';
    protected $fillable = ['promovente', 'oficina_id', 'descripcion'];

    public function archivos()
    {
        return $this->hasMany(ArchivoSolicitud::class, 'solicitud_id');
    }
}
