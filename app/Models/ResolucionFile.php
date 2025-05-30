<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResolucionFile extends Model
{
    use HasFactory;

    protected $table = 'resolucion_file';
    protected $primaryKey = 're_file_id';

    protected $fillable = ['resolucion_id', 'file_path'];
}
