<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResolucionFirmada extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $table = 'resoluciones_firmadas';

    protected $fillable = ['file_path', 'resolucion_id'];

}
