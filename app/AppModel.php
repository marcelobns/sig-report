<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class AppModel extends Model {

    public static function boot(){
        static::creating(function ($model) {
            if(!$model->user_id) {
                $model->user_id = Auth::user()->id;
            }
        });
        static::updating(function ($model) {
            if(!$model->user_id) {
                $model->user_id = Auth::user()->id;
            }
        });
    }
}
