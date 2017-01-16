<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StatusDiscente extends AppModel {
    protected $table = "public.status_discente";
    protected $guarded = array('status');
}

//TODO valdenilson
// ag 1904-6
// cc 7609-0
