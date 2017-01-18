<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pessoa extends AppModel {
    protected $table = 'comum.pessoa';
    protected $primaryKey = 'id_pessoa';

    public function getDataNascimentoAttribute($value) {
        return date('d/m/Y', strtotime($value));
    }
    public function getCpfCnpjAttribute($value) {
        $value = str_pad($value, 11, '0', STR_PAD_LEFT);        
        return implode('.', [substr($value, 0, 3), substr($value, 3, 3), substr($value, 6, 3)]).'-'.substr($value, 9, 2);
    }
    public function discente() {
        return $this->HasOne('App\Discente', 'id_pessoa');
    }
}
