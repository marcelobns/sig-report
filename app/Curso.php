<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Curso extends AppModel {
    protected $table = 'public.curso';
    protected $primaryKey = 'id_curso';

    public function scopeJoinPoloCurso($query){
        $query->leftJoin('ead.polo_curso', function($join) {
            $join->on('polo_curso.id_curso', '=', 'public.curso.id_curso');
            $join->on('polo_curso.id_polo','=', DB::raw(20356));
        });
    }
}
