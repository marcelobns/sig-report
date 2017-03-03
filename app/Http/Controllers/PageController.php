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
        $result = $query->where(['pessoa.id_pessoa'=>503396])->get(); //varios discentes
        // $row = $query->find(509894);

        foreach ($result as $key => $row) {
            $pessoa = [
                'REGISTRO'=>$row->tipo_registro,            
                'NOME'=>$row->nome,
                'CPF'=>$row->cpf,
                'DOC_ESTRANGEIRO'=> ($row->id_pais_nacionalidade != 31 ? $row->passaport : null),
                'DATA_NASCIMENTO'=> str_replace('/','',$row->data_nascimento),
                'SEXO'=> (int)($row->sexo == 'F'),
                'RACA'=> $row->raca,
                'NOME_MAE'=> $row->nome_mae,
                'NACIONALIDADE'=> $row->nacionalidade,
                'NASC_UF'=> $row->municipio->id_unidade_federativa,
                'NASC_MUNICIPIO'=> $row->municipio->codigo,
                'PAIS_ORIGEM'=> $row->pais->cod_pingifes,
                'DEFICIENTE'=> (int)!is_null($row->id_tipo_necessidade_especial),
                "DEF_CEGUEIRA"=>0,
                "DEF_BAIXA_VISAO"=> (int)($row->id_tipo_necessidade_especial == 2),
                "DEF_SURDEZ"=>0,
                "DEF_AUDITIVA"=> (int)($row->id_tipo_necessidade_especial == 1),
                "DEF_FISICA"=> (int)($row->id_tipo_necessidade_especial == 3),
                "DEF_SURDOCEGUEIRA"=>0,
                "DEF_MULTIPLA"=> (int)($row->id_tipo_necessidade_especial == 4),
                "DEF_INTELECTUAL"=>0,
                "DEF_AUTISMO"=> (int)($row->id_tipo_necessidade_especial == 5),
                "DEF_ASPERGER"=>0,
                "DEF_RETT"=>0,
                "DEF_DESINTEGRATIVO"=>0,
                "DEF_SUPERDOTACAO"=> (int)($row->id_tipo_necessidade_especial == 7)
            ];
            foreach ($pessoa as $i => $value) {
                if(explode('_', $i)[0] == 'DEF' && !$pessoa['DEFICIENTE']){
                    unset($pessoa[$i]);
                }
                if(explode('_', $i)[0] == 'NASC' && $pessoa['NACIONALIDADE'] != 1){
                    unset($pessoa[$i]);
                } elseif($pessoa['NACIONALIDADE'] == 1) {
                    unset($pessoa['DOC_ESTRANGEIRO']);
                }
            }
            foreach ($row->discente as $j => $discente) {
                $aluno = [

                ];
                $this->console($discente->movimentacao_aluno);
            }
            var_dump($pessoa);
        }
                

        return view('page.report');
    }
}