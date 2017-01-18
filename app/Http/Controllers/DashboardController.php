<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Discente;
use \App\StatusDiscente;
use \App\Curso;

class DashboardController extends AppController {
    public function discentes() {
        $_GET['status'] = @$_GET['status'] ? $_GET['status'] : [1, 8, 9];

        $query = Discente::joinPessoa()->joinCurso();
        if(isset($_GET['status']) && !empty($_GET['status'])){
            $query->whereIn('status', $_GET['status']);
        }
        if(isset($_GET['curso_id']) && !empty($_GET['curso_id'])){
            $query->where('discente.id_curso', '=', $_GET['curso_id']);
        }
        $view['resultado'] = $query->whereRaw("curso.nivel = 'G'")->orderBy('pessoa.nome')->paginate(30);

        $view['status'] = StatusDiscente::orderBy('descricao')->pluck('descricao', 'status');
        $view['cursos'] = Curso::whereRaw("nivel = 'G'")->orderBy('nome')->pluck('nome', 'id_curso');

        $view['discente']['status'] = @$_GET['status'];
        $view['discente']['curso_id'] = @$_GET['curso_id'];

        return view('dashboard.discentes', $view);
    }
    public function discentes_csv($collection){
        $file = fopen('public/files/discentes.xls', 'w');

        fputcsv($file, Discente::csvColunas(), ';');
        foreach ($collection as $i=>$row) {
            fputcsv($file, [
                $row->matricula,
                $row->ano_ingresso.'.'.$row->periodo_ingresso,
                $row->pessoa->nome,
                $row->pessoa->cpf_cnpj,
                $row->pessoa->data_nascimento,
                $row->statusDiscente->descricao,
                $row->curso->nome
            ], ';');
        }
        fclose($file);

        //TODO verificar melhor local para salvar os arquivos gerados
        //TODO estratégia de nomenclatura
        //TODO fazer chamada asyncrona
    }
}
