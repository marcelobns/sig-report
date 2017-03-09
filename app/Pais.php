<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pais extends AppModel {
    protected $table = 'comum.pais';
    protected $primaryKey = 'id_pais';    
}
