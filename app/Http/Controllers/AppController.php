<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppController extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function current_date() {
        return date('Y-m-d Hi ');
    }
}
