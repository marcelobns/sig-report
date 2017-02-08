<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DiscenteGraduacao extends AppModel {
    protected $table = 'graduacao.discente_graduacao';
    protected $primaryKey = 'id_discente_graduacao';
}
