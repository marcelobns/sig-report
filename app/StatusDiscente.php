<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StatusDiscente extends AppModel {
    protected $table = 'public.status_discente';
    protected $primaryKey = 'status';
}
