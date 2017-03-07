<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Curso extends AppModel {
    protected $table = 'public.curso';
    protected $primaryKey = 'id_curso';

    public function getTurnoCodigoAttribute(){        
        switch ($this->id_turno) {
            case 1078700:
                $codigo = 1;
                break;
            case 1078706:
                $codigo = 2;
                break;
            case 1078702:
                $codigo = 3;
                break;
            default:
                $codigo = 4;
                break;
        }        
        return $codigo;
    }
    public function polo_curso(){
        return $this->belongsTo('App\PoloCurso', 'id_curso')->where(['id_polo'=>20356]);
    }
}
