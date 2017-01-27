<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }    
}
