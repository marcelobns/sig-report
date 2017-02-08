<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Discente;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }
    public function report(){
        $result = Discente::orderBy('id_pessoa')->paginate(1);
        foreach ($result as $i => $row) {
            var_dump($row->pessoa);
        }
        return view('page.report');
    }
}
