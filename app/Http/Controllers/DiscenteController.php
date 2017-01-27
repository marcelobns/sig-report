<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Discente;
use \App\StatusDiscente;
use \App\Curso;
use \App\Pessoa;
use Input;

class DiscenteController extends AppController {
    public function index(Request $request) {
        $input = (object) $request->all();
        $input->status = @$input->status ? $input->status : [1, 8, 9];
        $input->search_text = str_replace(['.','-'], '', @$input->search_text);

        $query = Discente::joinPessoa()->joinCurso()->whereRaw("curso.nivel = 'G'")->orderBy('pessoa.nome');
        if(!empty($input->status)){
            $query->whereIn('status', $input->status);
        }
        if(!empty($input->curso_id)){
            $query->where(['discente.id_curso'=>$input->curso_id]);
        }
        if(!empty($input->periodo)){
            $query->whereRaw("ano_ingresso||'.'||periodo_ingresso = '{$input->periodo}'");
        }
        if(!empty($input->search_text)){
            $query->where(function($query) use ($input){
                $query->orWhere('pessoa.nome', 'ilike', "%$input->search_text%");
                $query->orWhereRaw("cast(pessoa.cpf_cnpj as text) like '$input->search_text'");
                $query->orWhereRaw("cast(discente.matricula as text) like '$input->search_text'");
            });
        }
        if(!empty($input->csv)){
            ini_set('max_execution_time', 60);
            ini_set('memory_limit', '2G');

            $view['filepath'] = $this->discentes_csv($query->get());
            $input->csv = '';
        }
        $view['table'] = $query->paginate(30);

        $view['status'] = StatusDiscente::orderBy('descricao')->pluck('descricao', 'status');
        $view['cursos'] = Curso::whereRaw("nivel = 'G' and ativo")->orderBy('nome')->pluck('nome', 'id_curso');
        $view['periodos'] = Discente::selectRaw("DISTINCT(ano_ingresso||'.'||periodo_ingresso) as periodo")->whereIn('status', [1, 5, 8, 9])->orderBy('periodo', 'desc')->pluck('periodo', 'periodo');

        $view['form'] = $input;
        return view('discentes.index', $view);
    }
    public function discentes_csv($collection){
        $filename = md5($collection);
        $filepath = "public/file/$filename.xls";

        if (!file_exists($filepath)) {

            $file = fopen($filepath, 'w');

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
        }
        return $filepath;
    }
    public function view($id){
        $view['pessoa'] = Pessoa::find($id);        
        return view('discentes.view', $view);
    }
}
// TODO: Filtrar e mostrar por estrutura curricular
