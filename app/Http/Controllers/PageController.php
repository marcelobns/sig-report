<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Pessoa;
use \App\Discente;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }
    public function report(){
        $query = Pessoa::selectRaw('*');
        $row = $query->find(503396);

        var_dump($row->doc_estrangeiro);
        $pessoa = [
            'REGISTRO'=>$row->tipo_registro,
            'COD no INEP'=>null,
            'NOME'=>$row->nome,
            'CPF'=>$row->cpf,
            'DOC ESTRANGEIRO'=> ($row->id_pais_nacionalidade != 31 ? $row->passaport : null),
            'DATA NASCIMENTO'=> str_replace('/','',$row->data_nascimento),
            'SEXO'=> (int)($row->sexo == 'F'),
            'RACA'=> $row->raca,
            'NO_MAE'=> $row->nome_mae,
            'NACIONALIDADE'=> $row->nacionalidade
        ];
        var_dump($pessoa);
        $this->console($row);

        return view('page.report');
    }
}
