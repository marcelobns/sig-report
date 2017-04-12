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
        $current = date('Y');
        $censo = $current-1;
        $file = ['40|789|4'.PHP_EOL];
        $inconsistencias = [];
//TODO: utilizar scope
        $pessoaPagination = Pessoa::select(
                    'pessoa.id_pessoa',
                    'pessoa.cpf_cnpj',
                    'pessoa.sexo',
                    'pessoa.nome_oficial',
                    'pessoa.nome_mae',
                    'pessoa.passaporte',
                    'pessoa.data_nascimento',
                    'pessoa.id_pais_nacionalidade',
                    'pessoa.id_tipo_necessidade_especial',
                    'discente.matricula',
                    'discente.ano_ingresso',
                    'municipio.id_unidade_federativa',
                    'municipio.codigo')
                    ->selectRaw("COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes")
                    ->joinMunicipio()
                    ->joinPais()
                    ->join('public.discente', 'discente.id_pessoa', '=', 'pessoa.id_pessoa')
                    ->whereRaw("discente.matricula in (
                        SELECT discente.matricula
                        FROM comum.pessoa pessoa
                        INNER JOIN public.discente discente on discente.id_pessoa = pessoa.id_pessoa
                        INNER JOIN graduacao.curriculo curriculo on curriculo.id_curriculo = discente.id_curriculo
                        INNER JOIN graduacao.matriz_curricular matriz on matriz.id_matriz_curricular = curriculo.id_matriz
                        INNER JOIN graduacao.habilitacao habilitacao on habilitacao.id_habilitacao = matriz.id_habilitacao
                        LEFT JOIN ensino.matricula_componente matricula on matricula.id_discente = discente.id_discente and matricula.ano = $censo
                        LEFT JOIN ensino.movimentacao_aluno movimento on movimento.id_discente = discente.id_discente and movimento.ano_referencia = $censo
                        LEFT JOIN ensino.tipo_movimentacao_aluno tipo_movimento on tipo_movimento.id_tipo_movimentacao_aluno = movimento.id_tipo_movimentacao_aluno
                        WHERE discente.nivel = 'G' and matricula.periodo <= 2
                        GROUP BY discente.matricula)")
            ->orderBy('pessoa.nome_oficial')
            ->paginate(2500);
        $apoio_social = $this->read_file("public/file/Apoio-Social.csv", [2]);
        $atividade_extra = $this->read_file("public/file/Atividade-Extracurricular.csv", [2]);
        $mobilidade_estudantil = $this->read_file("public/file/Mobilidade-Estudantil.csv", [2,3,4,5]);

        foreach ($pessoaPagination as $key => $row) {
            if(@$cpf_cnpj != $row->cpf_cnpj){
                $pessoa = [
                    'REGISTRO'=>$row->tipo_registro,
                    'ALUNO_INEP'=>null,
                    'NOME'=> $this->translit($row->nome_oficial),
                    'CPF'=>$row->cpf,
                    'DOC_ESTRANGEIRO'=> ($row->id_pais_nacionalidade != 31 ? $row->passaporte : null),
                    'DATA_NASCIMENTO'=> str_replace('/','',$row->data_nascimento),
                    'SEXO'=> (int)($row->sexo == 'F'),
                    'RACA'=> $row->raca == 6 && $row->ano_ingresso >= 2014 ? 2 : $row->raca,
                    'NOME_MAE'=>$this->translit($row->nome_mae),
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
                        $pessoa[$i] = null;
                    }
                    if($pessoa['NACIONALIDADE'] != 1 && explode('_', $i)[0] == 'NASC'){
                        $pessoa[$i] = null;
                    }
                    elseif($pessoa['NACIONALIDADE'] == 1) {
                        $pessoa['DOC_ESTRANGEIRO'] = null;
                    }
                }
                array_push($file, implode('|', $pessoa).PHP_EOL);
            }
            $cpf_cnpj = $row->cpf_cnpj;

            $discentes = Discente::select(
                    'discente.id_discente',
                    'discente.matricula',
                    'discente.id_forma_ingresso',
                    'discente.ano_ingresso',
                    'discente.periodo_ingresso',
                    'matricula_componente.ano',
                    'matricula_componente.periodo',
                    'curriculo.ch_total_minima',
                    'discente_graduacao.ch_total_integralizada',
                    'habilitacao.codigo_habilitacao_inep as codigo_inep',
                    'matriz_curricular.id_turno',
                    'curso.dt_inicio_funcionamento',
                    'curso.id_modalidade_educacao'
                )
                ->selectRaw("CASE tipo_movimento.id_tipo_movimentacao_aluno = 315
                                WHEN true THEN 9
                                WHEN false THEN tipo_movimento.statusdiscente
                            END as status")
                ->selectRaw("
                    (SELECT sum(ch_total)
                    FROM ensino.matricula_componente matricula
                    LEFT JOIN ensino.componente_curricular_detalhes detalhes on detalhes.id_componente_detalhes = matricula.id_componente_detalhes
                    WHERE matricula.ano = matricula_componente.ano and matricula.periodo = matricula_componente.periodo and matricula.id_discente = discente.id_discente
                        and matricula.id_situacao_matricula in (4,22,24)
                    ) as ch_periodo
                ")
                ->join('public.curso', 'curso.id_curso', '=', 'discente.id_curso')
                ->join('graduacao.discente_graduacao', 'discente_graduacao.id_discente_graduacao', '=', 'discente.id_discente')
                ->join('graduacao.curriculo', 'curriculo.id_matriz', '=', 'discente_graduacao.id_matriz_curricular')
                ->join('graduacao.matriz_curricular', 'matriz_curricular.id_matriz_curricular', '=', 'curriculo.id_matriz')
                ->join('graduacao.habilitacao', 'habilitacao.id_habilitacao', '=', 'matriz_curricular.id_habilitacao')
                ->leftJoin('ensino.matricula_componente', function($join) use ($censo){
                    $join->whereRaw("matricula_componente.id_discente = discente.id_discente");
                    $join->whereRaw("matricula_componente.ano = $censo ");
                })
                ->leftJoin('ensino.movimentacao_aluno', function($join)use ($censo){
                    $join->whereRaw("movimentacao_aluno.id_discente = discente.id_discente");
                    $join->whereRaw("ano_ocorrencia = $censo ");
                })
                ->leftJoin('ensino.tipo_movimentacao_aluno', function($join)use ($censo){
                    $join->whereRaw("tipo_movimentacao_aluno.id_tipo_movimentacao_aluno = movimentacao_aluno.id_tipo_movimentacao_aluno");
                    $join->whereRaw("tipo_movimentacao_aluno.statusdiscente in (10,17,101,308,315)");
                })
                ->leftJoin('ensino.componente_curricular_detalhes', 'componente_curricular_detalhes.id_componente_detalhes', '=', 'matricula_componente.id_componente_detalhes')
                ->whereRaw("discente.matricula = {$row->matricula}")
                ->groupBy(
                        'discente.id_discente',
                        'discente.matricula',
                        'tipo_movimentacao_aluno.statusdiscente',
                        'discente.id_forma_ingresso',
                        'discente.ano_ingresso',
                        'discente.periodo_ingresso',
                        'matricula_componente.ano',
                        'matricula_componente.periodo',
                        'curriculo.ch_total_minima',
                        'discente_graduacao.ch_total_integralizada',
                        'habilitacao.codigo_habilitacao_inep',
                        'matriz_curricular.id_turno',
                        'curso.dt_inicio_funcionamento',
                        'curso.id_modalidade_educacao'
                    )
                    ->orderBy('matricula_componente.periodo', 'desc')
                    ->get();
            $periodo = 0;
            $ch_periodo = 0;
            foreach ($discentes as $key => $discente) {

                // if($discente->ano_ingresso == $censo && $discente->periodo_ingresso > $periodo) {
                //     continue;
                // }

                $ch_integralizada_periodo = $discente->ch_total_integralizada - $ch_periodo;
                $ch_integralizada_periodo = $ch_integralizada_periodo < 0 ? 0 : $ch_integralizada_periodo;
                $ch_periodo += $discente->ch_periodo;

                $escola_publica = is_null($row->escola_publica) ? ((int) !in_array($discente->id_forma_ingresso, [11662,11654]))+1 : (int)$row->escola_publica;

                $has_apoio_social = array_key_exists($row->cpf_cnpj, $apoio_social);
                $has_mobilidade = array_key_exists($row->cpf_cnpj, $mobilidade_estudantil);
                $has_atividade_extra = array_key_exists($row->cpf_cnpj, $atividade_extra);

                $atividade_pesquisa = $has_atividade_extra ? (int)(in_array("PESQUISA", $atividade_extra[$row->cpf_cnpj]) || in_array("PESQUISA_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                $atividade_extensao = $has_atividade_extra ? (int)(in_array("EXTENSAO", $atividade_extra[$row->cpf_cnpj]) || in_array("EXTENSAO_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                $atividade_monitoria = $has_atividade_extra ? (int)(in_array("MONITORIA", $atividade_extra[$row->cpf_cnpj]) || in_array("MONITORIA_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                $atividade_estagio = $has_atividade_extra ? (int)(in_array("ESTAGIO", $atividade_extra[$row->cpf_cnpj]) || in_array("ESTAGIO_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;

                $inicio_curso = (int)date("Y", strtotime($discente->dt_inicio_funcionamento));
                $semestre_ingresso = $inicio_curso != 1969 && $inicio_curso >= $discente->ano_ingresso ?  "02$inicio_curso" : $discente->semestre_ingresso;

                $aluno = [
                    'REGISTRO'=>$discente->tipo_registro,
                    'SEMESTRE_REFERENCIA'=> $discente->periodo,
                    'CODIGO_CURSO'=> $discente->codigo_inep,
                    'EAD_POLO'=> $discente->id_modalidade_educacao == 2 ? 1033528 : null,
                    'MATRICULA'=>$discente->matricula,
                    'TURNO'=>$discente->id_modalidade_educacao == 2 ? null : $discente->turno_codigo,
                    'SITUACAO_VINCULO'=> $discente->vinculo_status, //$this->status($discente->vinculo_status, $discente->ch_total_minima, $ch_integralizada_periodo, $periodo),
                    'CURSO_ORIGEM'=> null,
                    'CONCLUSAO_PERIODO'=> null,
                    'PARFOR'=> in_array($discente->codigo_inep, [1156313,118566,1186297,
                                                                1186746,16895,16898,118568,1184530,
                                                                22532,31230,22533,31229,68225,68226,
                                                                68228,1185309,118564,1259131,16902,16896]) ? 0 : null,
                    'SEMESTRE_INGRESSO'=>$semestre_ingresso,
                    'TIPO ESCOLA MEDIO'=> ($escola_publica == 2 && $discente->ano_ingresso >= 2013) ? 1 : $escola_publica,
                    'INGR VESTIBULAR'=> (int)in_array($discente->id_forma_ingresso, [34110,11657,11656,11650,11658,11655,11659,11660,37350,11645]),
                    'INGR ENEM'=> (int)in_array($discente->id_forma_ingresso, [51252808,11663,11662,11654,11652,11653,11661]),
                    'INGR AVALIACAO SERIADA'=> 0,
                    'INGR SELECAO SIMPLIFICADA'=> 0,
                    'INGR EGRESSO BI/LI'=>0,
                    'INGR PEC-G'=>(int)in_array($discente->id_forma_ingresso, [34117,34130]),
                    'INGR TRANS EX OFICIO'=>(int)in_array($discente->id_forma_ingresso, [11644,11639,11642]),
                    'INGR DECIS JUDICIAl'=>(int)in_array($discente->id_forma_ingresso, [6517]),
                    'INGR VAGAS REMANESC'=>(int)in_array($discente->id_forma_ingresso, [11646,11647]),
                    'INGR VAGAS PROGR ESPECIAIS'=>(int)in_array($discente->id_forma_ingresso, [34116,1697747,11643,11649,34131]),
                    'MOBILIDADE_ACADEMICA'=>($discente->vinculo_status == 2 ? (int)$has_mobilidade : null),
                    'MOB_TIPO'=> $has_mobilidade ? $mobilidade_estudantil[$row->cpf_cnpj][0] : null,
                    'MOB_IES_DESTINO'=> $has_mobilidade ? $mobilidade_estudantil[$row->cpf_cnpj][1] : null,
                    'MOB_INTER_TIPO'=> $has_mobilidade ? $mobilidade_estudantil[$row->cpf_cnpj][2] : null,
                    'MOB_INTER_PAIS_DESTINO'=> $has_mobilidade ? $mobilidade_estudantil[$row->cpf_cnpj][3] : null,
                    'PROGRAMA DE RESERVAS'=>(int)in_array($discente->id_forma_ingresso, [11663,11662,11654,11652,11653, 11661,11657,11656,11650,11655,11659,11660,11645]),
                    'RESERVA_ETNICA'=>(int)in_array($discente->id_forma_ingresso, [11663,11662,11661,11645,11650,11657,11656,11659]),
                    'RESERVA_PCD'=>(int)in_array($discente->id_forma_ingresso, [11652,11655]),
                    'RESERVA_ESCOLA_PUB'=>(int)in_array($discente->id_forma_ingresso, [11662,11654]),
                    'RESERVA_RENDA'=>(int)in_array($discente->id_forma_ingresso, [11653,11661,11659,11660]),
                    'RESERVA_OUTROS'=>0,
                    'FINANCIAMENTO'=>null,
                    'FINANC_FIES'=>null,
                    'FINANC_GOV_ESTADUAL'=>null,
                    'FINANC_GOV_MUNICIPAL'=>null,
                    'FINANC_IES'=>null,
                    'FINANC_EXTERNA'=>null,
                    'FINANC_PROUNI_I'=>null,
                    'FINANC_PROUNI_P'=>null,
                    'FINANC_EXTERNA_NR'=>null,
                    'FINANC_GOV_ESTADUAL_NR'=>null,
                    'FINANC_IES_NR'=>null,
                    'FINANC_GOV_MUNICIPAL_NR'=>null,
                    'APOIO SOCIAL'=>(int)$has_apoio_social,
                    'APOIO_ALIMENTACAO'=>$has_apoio_social ? (int)in_array("ALIMENTACAO", $apoio_social[$row->cpf_cnpj]) : null,
                    'APOIO_MORADIA'=>$has_apoio_social ? (int)in_array("MORADIA", $apoio_social[$row->cpf_cnpj]) : null,
                    'APOIO_TRANSPORTE'=>$has_apoio_social ? (int)in_array("TRANSPORTE", $apoio_social[$row->cpf_cnpj]) : null,
                    'APOIO_MATERIAL'=>$has_apoio_social ?(int)in_array("MATERIAL", $apoio_social[$row->cpf_cnpj]) : null,
                    'APOIO_BOLSA_TRABALHO'=>$has_apoio_social ? (int)in_array("BOLSA_TRABALHO", $apoio_social[$row->cpf_cnpj]) : null,
                    'APOIO_BOLSA_PERMANENCIA'=>$has_apoio_social ? (int)in_array("PERMANENCIA", $apoio_social[$row->cpf_cnpj]) : null,
                    'ATIVIDADE EXTRACURRICULAR'=>(int)$has_atividade_extra,
                    'ATIVIDADE_PESQUISA'=>$atividade_pesquisa,
                    'ATIVIDADE_PESQUISA_BOLSA'=> $atividade_pesquisa ? (int)in_array("PESQUISA_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_EXTENSAO'=>$atividade_extensao,
                    'ATIVIDADE_EXTENSAO_BOLSA'=> $atividade_extensao ? (int)in_array("EXTENSAO_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_MONITORIA'=>$atividade_monitoria,
                    'ATIVIDADE_MONITORIA_BOLSA'=> $atividade_monitoria ? (int)in_array("MONITORIA_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_ESTAGIO'=> $atividade_estagio,
                    'ATIVIDADE_ESTAGIO_BOLSA'=> $atividade_estagio ? (int)in_array("ESTAGIO_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'CH_CURSO'=>$discente->ch_total_minima,
                    'CH_INTEGRALIZADO'=> $ch_integralizada_periodo,
                ];
                foreach ($aluno as $j => $value) {
                    if(!$aluno['MOBILIDADE_ACADEMICA'] && explode('_', $j)[0] == 'MOB'){
                        $aluno[$j] = null;
                    }
                    if($j == 'EAD_POLO' && !$aluno['EAD_POLO']){
                        $aluno[$j] = null;
                    }
                    if(!$aluno['PROGRAMA DE RESERVAS'] && explode('_', $j)[0] == 'RESERVA'){
                        $aluno[$j] = null;
                    }
                }
                if($discente->periodo != $periodo && $discente->periodo < 3){
                    array_push($file, implode('|',$aluno).PHP_EOL);
                }
                if(sizeof($discentes) < 2 && $discente->ano_ingresso != 2016 && !in_array($discente->vinculo_status, [4,6])){
                    $aluno['SEMESTRE_REFERENCIA'] = $discente->periodo != 1 ? 1 : 2;
                    array_push($file, implode('|',$aluno).PHP_EOL);
                }
                $periodo = $discente->periodo;
            }
        }
        $page = @$_GET['page'] ? $_GET['page'] : 1;
        $filename = "public/file/$censo-alunos-$page.txt";

        file_put_contents($filename, $file);

        $view = [
            'pessoaPagination'=>$pessoaPagination,
            'filename'=>$filename,
            'censo'=>$censo
        ];
        return view('page.censup', $view);
    }
    public function read_file($filepath, $columns = []){
        ini_set("auto_detect_line_endings", "1");
        $pessoas = [];
        $handle = fopen($filepath, "r");
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
            $i = (int)$line[0];
            if(!isset($pessoas[$i])){
                $pessoas[$i] = [];
            }
            foreach ($columns as $j=>$c) {
                array_push($pessoas[$i], $line[$c]);
            }
        }
        fclose($handle);
        return $pessoas;
    }
    public function status($vinculo, $ch_total, $ch_integralizado, $periodo){
        if($vinculo == 4 && $periodo == 1){
            $vinculo = 3;
        } elseif ($vinculo == 6 && $periodo == 1) {
            $vinculo = 2;
        } elseif ($ch_integralizado >= $ch_total && $periodo == 2) {
            $vinculo = 6;
        } elseif ($ch_integralizado < $ch_total && $vinculo == 6) {
            $vinculo = 2;
        }
        $this->vinculo = $vinculo;
        return $vinculo;
    }
}
