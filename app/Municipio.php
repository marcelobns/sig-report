<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Municipio extends AppModel {
    protected $table = 'comum.municipio';
    protected $primaryKey = 'id_municipio';

    public function getCodigoAttribute($value){
        return trim(str_replace('-','',$value));
    }
}
    