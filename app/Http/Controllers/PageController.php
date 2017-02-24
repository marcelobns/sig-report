<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Discente;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }
    public function report(){
        $query = Discente::selectRaw('*');
        // dd($query->toSql());
        $result = $query->paginate(3);

        foreach ($result as $i => $row) {
            var_dump($row->id_discente);
            var_dump($row->pessoa->nome);
            var_dump($row->pessoa->municipio->nome);
            var_dump($row->movimentacaoAluno->whereHas('id_tipo_movimentacao_aluno', '=', 1)->get());

        }
        return view('page.report');
    }
}
