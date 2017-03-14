<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Pessoa;
use \App\Discente;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }
    public function censup(){          
        $year = date('Y')-1;                
        $file = ['40|789|4'.PHP_EOL];
        $inconsistencias = [];

        $resultSet = Pessoa::select(
                'pessoa.*', 
                'municipio.id_unidade_federativa',
                'municipio.codigo'                    
            )
            ->selectRaw("COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes")
            ->joinMunicipio()
            ->joinPais()
            ->whereRaw("id_pessoa in (SELECT id_pessoa FROM public.discente
                                        WHERE id_pessoa = pessoa.id_pessoa
                                            and ano_ingresso <= '$year'
                                            and id_curso is not null
                                            and nivel = 'G'
                                            and status in (1, 5, 8, 9)) ")
                                            ->orderBy('nome')                                                
                                            ->paginate(100);
        if($resultSet->currentPage() == $resultSet->lastPage()){
            $resultSet2 = Pessoa::select(
                    'pessoa.*', 
                    'municipio.id_unidade_federativa',
                    'municipio.codigo'                    
                )
                ->selectRaw("COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes")
                ->joinMunicipio()
                ->joinPais()
                ->whereRaw("id_pessoa in (SELECT id_pessoa FROM public.discente
                                            INNER JOIN ensino.movimentacao_aluno on movimentacao_aluno.id_discente = discente.id_discente 
                                            WHERE id_pessoa = pessoa.id_pessoa
                                                and ano_ingresso <= '$year'
                                                and id_curso is not null
                                                and ano_ocorrencia = '$year'
                                                and nivel = 'G'
                                                and status = 3) ")->get();
        }        
        var_dump($resultSet2);
        
        foreach ($resultSet as $key => $row) {
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
                'NASC_UF'=> @$row->id_unidade_federativa,
                'NASC_MUNICIPIO'=> @$row->municipio_codigo,
                'PAIS_ORIGEM'=> $row->cod_pais_pingifes,
                'DEFICIENTE'=> (int)!is_null($row->id_tipo_necessidade_especial),
                'DEF_CEGUEIRA'=>0,
                'DEF_BAIXA_VISAO'=> (int)($row->id_tipo_necessidade_especial == 2),
                'DEF_SURDEZ'=>0,
                'DEF_AUDITIVA'=> (int)($row->id_tipo_necessidade_especial == 1),
                'DEF_FISICA'=> (int)($row->id_tipo_necessidade_especial == 3),
                'DEF_SURDOCEGUEIRA'=>0,
                'DEF_MULTIPLA'=> (int)($row->id_tipo_necessidade_especial == 4),
                'DEF_INTELECTUAL'=>0,
                'DEF_AUTISMO'=> (int)($row->id_tipo_necessidade_especial == 5),
                'DEF_ASPERGER'=>0,
                'DEF_RETT'=>0,
                'DEF_DESINTEGRATIVO'=>0,
                'DEF_SUPERDOTACAO'=> (int)($row->id_tipo_necessidade_especial == 7)                
            ];
            foreach ($pessoa as $i => $value) {
                if(!$pessoa['DEFICIENTE'] && explode('_', $i)[0] == 'DEF'){
                    unset($pessoa[$i]);
                }
                if($pessoa['NACIONALIDADE'] != 1 && explode('_', $i)[0] == 'NASC'){
                    unset($pessoa[$i]);
                } elseif($pessoa['NACIONALIDADE'] == 1) {
                    unset($pessoa['DOC_ESTRANGEIRO']);
                }
            }            
            array_push($file, implode('|', $pessoa).PHP_EOL);
            foreach ($row->discente as $j => $discente) {                
                if(($row->nacionalidade == 1 && (!isset($row->municipio_codigo) OR @$row->id_unidade_federativa == -1)) || !isset($row->raca)|| $discente->curso_inep == ''){
                    array_push($inconsistencias, [
                        'id_pessoa'=>$row->id_pessoa,
                        'id_discente'=>$discente->id_discente,
                        'CURSO'=>$discente->curso_nome,
                        'COD_INEP'=>@$discente->curso_inep,                        
                        'CPF'=>$row->cpf,
                        'PESSOA'=>$row->nome,
                        'MATRICULA'=>$discente->matricula,
                        'STATUS'=>$discente->vinculo_status,
                        'CONCLUSAO'=>@$discente->ano_ocorrencia,
                        'RACA'=>@$row->raca,
                        'NACIONALIDADE'=>@$row->nacionalidade,
                        'PAIS'=> @$row->cod_pais_pingifes,
                        'UF'=>@$row->id_unidade_federativa,
                        'MUNICIPIO'=>@$row->municipio_codigo,
                    ]);
                    continue;
                }
                $aluno = [
                    'REGISTRO'=>$discente->tipo_registro,
                    'INGRESSO_PERIODO'=>$discente->periodo_ingresso,
                    'CURSO_INEP'=>$discente->curso_inep,
                    'EAD_POLO'=>@$discente->polo_curso->id_polo,
                    'MATRICULA'=>$discente->matricula,
                    'TURNO'=>$discente->turno_codigo,
                    'VINCULO_STATUS'=>$discente->vinculo_status,
                    'CURSO_ORIGEM'=> null,
                    'CONCLUSAO_PERIODO'=> @$discente->periodo_ocorrencia,
                    'PARFOR'=>0,
                    'SEMESTRE_INGRESSO'=>$discente->semestre_ingresso,
                    'TIPO ESCOLA MEDIO'=>(int)in_array($discente->id_forma_ingresso, [11662,11654])+1,
                    'INGR VESTIBULAR'=> (int)in_array($discente->id_forma_ingresso, [34110,11657,11656,11650,11658,11655,11659,11660]),
                    'INGR ENEM'=> (int)in_array($discente->id_forma_ingresso, [51252808,11663,11662,11654,11652,11653,11661]),
                    'INGR AVALIACAO SERIADA'=> 0,
                    'INGR SELECAO SIMPLIFICADA'=> (int)in_array($discente->id_forma_ingresso, [37350,11645]),
                    'INGR EGRESSO BI/LI'=>0,
                    'INGR PEC-G'=>(int)in_array($discente->id_forma_ingresso, [34117]),
                    'INGR TRANS EX OFICIO'=>(int)in_array($discente->id_forma_ingresso, [11644]),
                    'INGR DECIS JUDICIAl'=>0,
                    'INGR VAGAS REMANESC'=>0,
                    'INGR VAGAS PROG. ESPECIAIS'=>(int)in_array($discente->id_forma_ingresso, [34116]),
                    'MOBILIDADE_ACADEMICA'=>(int)in_array($discente->id_forma_ingresso, [11639]),
                    'MOB_TIPO'=>null,
                    'MOB_DESTINO'=>null,
                    'MOB_INTERNACIONAL'=>null,
                    'MOB_PAIS_DESTINO'=>null,
                    'PROGRAMA DE RESERVAS'=>(int)in_array($discente->id_forma_ingresso, [11663,11662,11654,11652,11653, 11661,11657,11656,11650,11655,11659,11660,11645]),
                    'RESERVA ETNICA'=>(int)in_array($discente->id_forma_ingresso, [11663,11662,11661,11645,11657,11656,11659]),
                    'RESERVA PCD'=>(int)in_array($discente->id_forma_ingresso, [11652,11655]),
                    'RESERVA ESCOLA PUB'=>(int)in_array($discente->id_forma_ingresso, [11662,11654]),
                    'RESERVA RENDA'=>(int)in_array($discente->id_forma_ingresso, [11653,11661,11659,11660]),
                    'RESERVA OUTROS'=>0,
                    'FINANCIAMENTO'=>0,
                    // 'APOIO SOCIAL' -- VERIFICAR NA PRAE RELACAO
                    // 'ATIVIDADE EXTRACURRICULAR' -- VERIFICAR NA PRAE RELACAO
                    'CH_CURSO'=>$discente->ch_total_minima,
                    'CH_INTEGRALIZADO'=>$discente->ch_total_integralizada
                ];                
                foreach ($aluno as $j => $value) {                    
                    if(!$aluno['MOBILIDADE_ACADEMICA'] && explode('_', $j)[0] == 'MOB'){
                        unset($aluno[$j]);
                    }
                    if($j == 'EAD_POLO' && !$aluno['EAD_POLO']){
                        unset($aluno[$j]);
                    }
                }
                array_push($file, implode('|',$aluno).PHP_EOL);
            }                        
        }
        $page = @$_GET['page'] ? $_GET['page'] : 1;
        $filename = "public/file/$year-alunos-$page.txt";

        if(sizeof($inconsistencias) == 0){            
            file_put_contents($filename, $file);
        }
        $view = [
            'inconsistencias'=>$inconsistencias,
            'resultSet'=>$resultSet,            
            'filename'=>$filename,
            'year'=>$year            
        ];
        return view('page.censup', $view);
    }
}