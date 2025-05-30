<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionResolucion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_res';

    protected $fillable = [
        'usuario_origen_id',
        'usuario_destino_id',
        'mensaje',
        'link',
        'leido',
 ];
}
