<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Turno extends AppModel {
    protected $table = 'ensino.turno';
    protected $primaryKey = 'id_turno';
}
