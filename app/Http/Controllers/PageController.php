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

        $pessoaPagination = Pessoa::select(
                'pessoa.id_pessoa',
                'pessoa.cpf_cnpj',
                'pessoa.nome_oficial',
                'pessoa.nome_mae',
                'pessoa.passaporte',
                'pessoa.data_nascimento',
                'pessoa.id_pais_nacionalidade',
                'pessoa.id_tipo_necessidade_especial',
                'discente.matricula',
                'discente.ano_ingresso',
                'municipio.id_unidade_federativa',
                'municipio.codigo'
            )
            ->selectRaw("COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes")
            ->selectRaw(" sum(componente_curricular_detalhes.ch_total) as ch_periodo")
            ->joinMunicipio()
            ->joinPais()
            ->join('public.discente', 'discente.id_pessoa', '=', 'pessoa.id_pessoa')
            ->join('public.curso', 'curso.id_curso', '=', 'discente.id_curso')
            ->leftJoin('ensino.matricula_componente', function($join) use ($censo){
                $join->whereRaw("matricula_componente.id_discente = discente.id_discente");
                $join->whereRaw("matricula_componente.ano = $censo");
                $join->whereRaw("matricula_componente.id_situacao_matricula in (4,22,24)");
            })
            ->leftJoin('ensino.componente_curricular_detalhes', 'componente_curricular_detalhes.id_componente_detalhes', '=', 'matricula_componente.id_componente_detalhes')
            ->whereRaw("(discente.ano_ingresso <= $censo and curso.ativo = true and discente.nivel = 'G' and discente.status not in (14, 2, 12, -1, 11, 6, 10, 13, 15, 3) )")
            ->orWhereRaw("(curso.ativo = true and discente.id_discente in (
                    SELECT id_discente
                    FROM ensino.movimentacao_aluno
                    WHERE id_tipo_movimentacao_aluno in (1, 315) and ano_ocorrencia = $censo
                ))")
            ->groupBy(
                'pessoa.id_pessoa',
                'pessoa.cpf_cnpj',
                'pessoa.nome_oficial',
                'pessoa.nome_mae',
                'pessoa.passaporte',
                'pessoa.data_nascimento',
                'pessoa.id_pais_nacionalidade',
                'pessoa.id_tipo_necessidade_especial',
                'discente.matricula',
                'discente.ano_ingresso',
                'municipio.id_unidade_federativa',
                'municipio.codigo',
                'pais.cod_pais_pingifes'
                )
            ->orderBy('pessoa.nome_oficial')
            ->paginate(1500)
            ;
            // dd($pessoaPagination->toSql());

        $apoio_social = $this->read_file("public/file/Apoio-Social.csv", [2]);
        $atividade_extra = $this->read_file("public/file/Atividade-Extracurricular.csv", [2]);
        $mobilidade_estudantil = $this->read_file("public/file/Mobilidade-Estudantil.csv", [2,3,4,5]);
        $last_cpf = null;
        foreach ($pessoaPagination as $key => $row) {
            if($last_cpf != $row->cpf_cnpj){
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
            $last_cpf = $row->cpf_cnpj;

            $ch_periodo = 0;
            for ($periodo=2; $periodo >= 1; $periodo--) {
                //TODO: utilizar scope
                $discentes = Discente::select(
                            'discente.matricula',
                            'discente.status',
                            'discente.id_forma_ingresso',
                            'discente.ano_ingresso',
                            'discente.periodo_ingresso',
                            'movimentacao_aluno.ano_ocorrencia',
                            'movimentacao_aluno.periodo_ocorrencia',
                            'curriculo.ch_total_minima',
                            'discente_graduacao.ch_total_integralizada',
                            'curso.codigo_inep as curso_inep',
                            'curso.nome as curso_nome',
                            'curso.id_turno',
                            'curso.id_modalidade_educacao',
                            'curso.dt_inicio_funcionamento'
                        )
                        ->selectRaw(" sum(componente_curricular_detalhes.ch_total) as ch_periodo")
                        ->join('public.curso', 'curso.id_curso', '=', 'discente.id_curso')
                        ->leftJoin('graduacao.discente_graduacao', 'discente_graduacao.id_discente_graduacao', '=', 'discente.id_discente')
                        ->leftJoin('graduacao.curriculo', 'curriculo.id_matriz', '=', 'discente_graduacao.id_matriz_curricular')
                        ->leftJoin('ensino.movimentacao_aluno', function($join)use ($censo, $periodo){
                            $join->whereRaw("movimentacao_aluno.id_discente = discente.id_discente");
                            $join->whereRaw("id_tipo_movimentacao_aluno in (1, 315)");
                            $join->whereRaw("ano_ocorrencia = '$censo' and periodo_ocorrencia = '$periodo'");
                        })
                        ->leftJoin('ensino.matricula_componente', function($join) use ($censo, $periodo){
                            $join->whereRaw("matricula_componente.id_discente = discente.id_discente");
                            $join->whereRaw("matricula_componente.ano = $censo and matricula_componente.periodo = $periodo");
                            $join->whereRaw("matricula_componente.id_situacao_matricula in (4,22,24)");
                        })
                        ->leftJoin('ensino.componente_curricular_detalhes', 'componente_curricular_detalhes.id_componente_detalhes', '=', 'matricula_componente.id_componente_detalhes')
                        ->whereRaw("discente.matricula = {$row->matricula}")
                        ->groupBy(
                                    'discente.matricula',
                                    'discente.status',
                                    'discente.id_forma_ingresso',
                                    'discente.ano_ingresso',
                                    'discente.periodo_ingresso',
                                    'movimentacao_aluno.ano_ocorrencia',
                                    'movimentacao_aluno.periodo_ocorrencia',
                                    'curriculo.ch_total_minima',
                                    'discente_graduacao.ch_total_integralizada',
                                    'curso.codigo_inep',
                                    'curso.nome',
                                    'curso.id_turno',
                                    'curso.id_modalidade_educacao',
                                    'curso.dt_inicio_funcionamento'
                                )
                        ->get();
                        $discente = $discentes[0];
                        $ch_integralizada_periodo = $discente->ch_total_integralizada - $ch_periodo;
                        $ch_integralizada_periodo = $ch_integralizada_periodo < 0 ? 0 : $ch_integralizada_periodo;
                        $ch_periodo += $discente->ch_periodo;

                        if($discente->ano_ingresso == $censo && $discente->periodo_ingresso > $periodo) {
                            continue;
                        }
                        $escola_publica = is_null($row->escola_publica) ? ((int) !in_array($discente->id_forma_ingresso, [11662,11654]))+1 : (int)$row->escola_publica;

                        $inicio_curso = (int)date("Y", strtotime($discente->dt_inicio_funcionamento));
                        $ano_ingresso = $discente->ano_ingresso;

                        $has_apoio_social = array_key_exists($row->cpf_cnpj, $apoio_social);
                        $has_atividade_extra = array_key_exists($row->cpf_cnpj, $atividade_extra);
                        $has_mobilidade = array_key_exists($row->cpf_cnpj, $mobilidade_estudantil);

                        $atividade_pesquisa = $has_atividade_extra ? (int)(in_array("PESQUISA", $atividade_extra[$row->cpf_cnpj]) || in_array("PESQUISA_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                        $atividade_extensao = $has_atividade_extra ? (int)(in_array("EXTENSAO", $atividade_extra[$row->cpf_cnpj]) || in_array("EXTENSAO_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                        $atividade_monitoria = $has_atividade_extra ? (int)(in_array("MONITORIA", $atividade_extra[$row->cpf_cnpj]) || in_array("MONITORIA_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;
                        $atividade_estagio = $has_atividade_extra ? (int)(in_array("ESTAGIO", $atividade_extra[$row->cpf_cnpj]) || in_array("ESTAGIO_BOLSA", $atividade_extra[$row->cpf_cnpj])) : null;







                        $aluno = [
                            'REGISTRO'=>$discente->tipo_registro,
                            'SEMESTRE_REFERENCIA'=> $periodo,
                            'CODIGO_CURSO'=>$discente->curso_inep,
                            'EAD_POLO'=> $discente->id_modalidade_educacao == 2 ? 1033528 : null,
                            'MATRICULA'=>$discente->matricula,
                            'TURNO'=>$discente->id_modalidade_educacao != 2 ? $discente->turno_codigo : null,
                            'SITUACAO_VINCULO'=> $this->status($discente->vinculo_status, $discente->ch_total_minima, $ch_integralizada_periodo, $periodo),
                            'CURSO_ORIGEM'=> null,
                            'CONCLUSAO_PERIODO'=> null,
                            'PARFOR'=> in_array($discente->curso_inep, [5001023,1156313,118566,1186297,
                                                                        1186746,16895,16898,118568,1184530,
                                                                        22532,31230,22533,31229,68225,68226,
                                                                        68228,1185309,118564,1259131,16902,16896]) ? 0 : null,
                            'SEMESTRE_INGRESSO'=> $inicio_curso != 1969 && $inicio_curso >= $ano_ingresso ?  "02$inicio_curso" : $discente->semestre_ingresso,
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
                            'INGR VAGAS PROG. ESPECIAIS'=>(int)in_array($discente->id_forma_ingresso, [34116,1697747,11643,11649,34131]),
                            'MOBILIDADE_ACADEMICA'=>($this->vinculo == 2 ? (int)$has_mobilidade : null),
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
                          array_push($file, implode('|',$aluno).PHP_EOL);

                            //   var_dump($aluno);
                // }
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
