<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends AppController {
    public function discentes() {
        $view['status'] = \App\StatusDiscente::orderBy('descricao')->pluck('descricao', 'status');
        $view['cursos'] = \App\Curso::orderBy('nome')->pluck('nome', 'id_curso');
        $view['columns'] = [
            'matricula'=>'Matricula',
            'nome'=>'Nome',
            'ano_ingresso'=>'Ano Ingresso',
        ];
        return view('dashboard.discentes', $view);
    }
}
