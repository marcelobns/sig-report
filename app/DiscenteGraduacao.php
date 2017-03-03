<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DiscenteGraduacao extends AppModel {
    protected $table = 'graduacao.discente_graduacao';
    protected $primaryKey = 'id_discente_graduacao';

    public function curriculo() {
        return $this->belongsTo('App\Curriculo', 'id_matriz_curricular');
    }
    public function discente() {
        return $this->belongsTo('App\Discente', 'id_discente');
    }
}
