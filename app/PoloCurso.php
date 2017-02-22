<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PoloCurso extends AppModel {
    protected $table = 'ead.polo_curso';
    protected $primaryKey = 'id_polo';
}
