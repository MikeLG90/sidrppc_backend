<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegacion extends Model
{
    use HasFactory;
    protected $table = 'oficinas';
    protected $primaryKey = 'oficina_id';

    protected $fillable = [
           'oficina',
           'dep_id'
    ];
}
