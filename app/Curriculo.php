<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Curriculo extends AppModel {
    protected $table = 'graduacao.curriculo';
    protected $primaryKey = 'id_matriz';
}
