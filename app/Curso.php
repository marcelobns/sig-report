<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Curso extends AppModel {
    protected $table = "public.curso";
    protected $guarded = array('id');
}
