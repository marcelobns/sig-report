<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Pessoa;
use \App\Discente;

class PageController extends AppController {
    public function home() {
        return view('page.home');
    }
    public function censup(Request $request){
        $ano_censo = date('Y')-1;
        $page = $request->input('page', 1);
        $pessoaPaginator = Pessoa::censo($ano_censo, $page, 5000);

        $apoio_social = $this->read_file("public/file/Apoio-Social.csv", [2]);
        $atividade_extra = $this->read_file("public/file/Atividade-Extracurricular.csv", [2]);
        $mobilidade_estudantil = $this->read_file("public/file/Mobilidade-Estudantil.csv", [2,3,4,5]);

        $file = ['40|789|4'.PHP_EOL];
        foreach ($pessoaPaginator as $key => $row) {
            $pessoa = new Pessoa((array)$row);
            if(@$cpf_cnpj != $pessoa->cpf_cnpj) {
                $registro41 = [
                    'REGISTRO'=>41,
                    'ALUNO_INEP'=>null,
                    'NOME'=>$this->translit($pessoa->nome_oficial),
                    'CPF'=>$pessoa->cpf,
                    'DOC_ESTRANGEIRO'=> $pessoa->doc_estrangeiro,
                    'DATA_NASCIMENTO'=> str_replace('/','',$pessoa->data_nascimento),
                    'SEXO'=> (int)($pessoa->sexo == 'F'),
                    'RACA'=> $pessoa->raca_cor,
                    'NOME_MAE'=>$this->translit($pessoa->nome_mae),
                    'NACIONALIDADE'=> $pessoa->nacionalidade,
                    'NASC_UF'=> $pessoa->id_unidade_federativa,
                    'NASC_MUNICIPIO'=> $pessoa->municipio_codigo,
                    'PAIS_ORIGEM'=> $pessoa->cod_pais_pingifes,
                    'DEFICIENTE'=> (int)!is_null($pessoa->id_tipo_necessidade_especial),
                    'DEF_CEGUEIRA'=>0,
                    'DEF_BAIXA_VISAO'=> (int)($pessoa->id_tipo_necessidade_especial == 2),
                    'DEF_SURDEZ'=>0,
                    'DEF_AUDITIVA'=> (int)($pessoa->id_tipo_necessidade_especial == 1),
                    'DEF_FISICA'=> (int)($pessoa->id_tipo_necessidade_especial == 3),
                    'DEF_SURDOCEGUEIRA'=>0,
                    'DEF_MULTIPLA'=> (int)($pessoa->id_tipo_necessidade_especial == 4),
                    'DEF_INTELECTUAL'=>0,
                    'DEF_AUTISMO'=> (int)($pessoa->id_tipo_necessidade_especial == 5),
                    'DEF_ASPERGER'=>0,
                    'DEF_RETT'=>0,
                    'DEF_DESINTEGRATIVO'=>0,
                    'DEF_SUPERDOTACAO'=> (int)($pessoa->id_tipo_necessidade_especial == 7)
                ];
                foreach ($registro41 as $i => $value) {
                    if(!$registro41['DEFICIENTE'] && explode('_', $i)[0] == 'DEF'){
                        $registro41[$i] = null;
                    }
                    if($registro41['NACIONALIDADE'] != 1 && explode('_', $i)[0] == 'NASC'){
                        $registro41[$i] = null;
                    }
                }
                array_push($file, implode('|', $registro41).PHP_EOL);
            }

            if(@$referencia != $pessoa->referencia) {
                $discente = new Discente((array)$row);

                $ch_integralizada_periodo = $discente->referencia == 1 ? $discente->ch_total_integralizada - $discente->ch_periodo_integralizado : $discente->ch_total_integralizada;
                $ch_integralizada_periodo = $ch_integralizada_periodo < 0 ? 0 : $ch_integralizada_periodo;

                $escola_publica = is_null($discente->escola_publica) ? ((int) !in_array($discente->id_forma_ingresso, [11662,11654]))+1 : (int)$discente->escola_publica;

                $has_apoio_social = array_key_exists($discente->cpf_cnpj, $apoio_social);
                $has_mobilidade = array_key_exists($discente->cpf_cnpj, $mobilidade_estudantil);
                $has_atividade_extra = array_key_exists($discente->cpf_cnpj, $atividade_extra);

                $atividade_pesquisa = $has_atividade_extra ? (int)(in_array("PESQUISA", $atividade_extra[$discente->cpf_cnpj]) || in_array("PESQUISA_BOLSA", $atividade_extra[$discente->cpf_cnpj])) : null;
                $atividade_extensao = $has_atividade_extra ? (int)(in_array("EXTENSAO", $atividade_extra[$discente->cpf_cnpj]) || in_array("EXTENSAO_BOLSA", $atividade_extra[$discente->cpf_cnpj])) : null;
                $atividade_monitoria = $has_atividade_extra ? (int)(in_array("MONITORIA", $atividade_extra[$discente->cpf_cnpj]) || in_array("MONITORIA_BOLSA", $atividade_extra[$discente->cpf_cnpj])) : null;
                $atividade_estagio = $has_atividade_extra ? (int)(in_array("ESTAGIO", $atividade_extra[$discente->cpf_cnpj]) || in_array("ESTAGIO_BOLSA", $atividade_extra[$discente->cpf_cnpj])) : null;

                $inicio_curso = (int)date("Y", strtotime($discente->dt_inicio_funcionamento));
                $semestre_ingresso = $inicio_curso != 1969 && $inicio_curso >= $discente->ano_ingresso ?  "02$inicio_curso" : $discente->semestre_ingresso;

                $registro42 = [
                    'REGISTRO'=>$discente->tipo_registro,
                    'SEMESTRE_REFERENCIA'=> $discente->referencia,
                    'CODIGO_CURSO'=> $discente->codigo_inep,
                    'EAD_POLO'=> $discente->id_modalidade_educacao == 2 ? 1033528 : null,
                    'MATRICULA'=>$discente->matricula,
                    'TURNO'=>$discente->id_modalidade_educacao == 2 ? null : $discente->turno_codigo,
                    'SITUACAO_VINCULO'=> $discente->vinculo_status,
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
                    'MOB_TIPO'=> $has_mobilidade ? $mobilidade_estudantil[$discente->cpf_cnpj][0] : null,
                    'MOB_IES_DESTINO'=> $has_mobilidade ? $mobilidade_estudantil[$discente->cpf_cnpj][1] : null,
                    'MOB_INTER_TIPO'=> $has_mobilidade ? $mobilidade_estudantil[$discente->cpf_cnpj][2] : null,
                    'MOB_INTER_PAIS_DESTINO'=> $has_mobilidade ? $mobilidade_estudantil[$discente->cpf_cnpj][3] : null,
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
                    'APOIO_ALIMENTACAO'=>$has_apoio_social ? (int)in_array("ALIMENTACAO", $apoio_social[$discente->cpf_cnpj]) : null,
                    'APOIO_MORADIA'=>$has_apoio_social ? (int)in_array("MORADIA", $apoio_social[$discente->cpf_cnpj]) : null,
                    'APOIO_TRANSPORTE'=>$has_apoio_social ? (int)in_array("TRANSPORTE", $apoio_social[$discente->cpf_cnpj]) : null,
                    'APOIO_MATERIAL'=>$has_apoio_social ?(int)in_array("MATERIAL", $apoio_social[$discente->cpf_cnpj]) : null,
                    'APOIO_BOLSA_TRABALHO'=>$has_apoio_social ? (int)in_array("BOLSA_TRABALHO", $apoio_social[$discente->cpf_cnpj]) : null,
                    'APOIO_BOLSA_PERMANENCIA'=>$has_apoio_social ? (int)in_array("PERMANENCIA", $apoio_social[$discente->cpf_cnpj]) : null,
                    'ATIVIDADE EXTRACURRICULAR'=>(int)$has_atividade_extra,
                    'ATIVIDADE_PESQUISA'=>$atividade_pesquisa,
                    'ATIVIDADE_PESQUISA_BOLSA'=> $atividade_pesquisa ? (int)in_array("PESQUISA_BOLSA", $atividade_extra[$discente->cpf_cnpj]) : null,
                    'ATIVIDADE_EXTENSAO'=>$atividade_extensao,
                    'ATIVIDADE_EXTENSAO_BOLSA'=> $atividade_extensao ? (int)in_array("EXTENSAO_BOLSA", $atividade_extra[$discente->cpf_cnpj]) : null,
                    'ATIVIDADE_MONITORIA'=>$atividade_monitoria,
                    'ATIVIDADE_MONITORIA_BOLSA'=> $atividade_monitoria ? (int)in_array("MONITORIA_BOLSA", $atividade_extra[$discente->cpf_cnpj]) : null,
                    'ATIVIDADE_ESTAGIO'=> $atividade_estagio,
                    'ATIVIDADE_ESTAGIO_BOLSA'=> $atividade_estagio ? (int)in_array("ESTAGIO_BOLSA", $atividade_extra[$discente->cpf_cnpj]) : null,
                    'CH_CURSO'=>$discente->ch_total_minima,
                    'CH_INTEGRALIZADO'=> $discente->vinculo_status == 6 ? $discente->ch_total_minima : $ch_integralizada_periodo,
                ];
                foreach ($registro42 as $j => $value) {
                    if(!$registro42['MOBILIDADE_ACADEMICA'] && explode('_', $j)[0] == 'MOB'){
                        $registro42[$j] = null;
                    }
                    if($j == 'EAD_POLO' && !$registro42['EAD_POLO']){
                        $registro42[$j] = null;
                    }
                    if(!$registro42['PROGRAMA DE RESERVAS'] && explode('_', $j)[0] == 'RESERVA'){
                        $registro42[$j] = null;
                    }
                }
                if ($discente->referencia == 1 && $semestre_ingresso != "02$ano_censo") {
                   array_push($file, implode('|',$registro42).PHP_EOL);
                } elseif ($discente->referencia != 1 && !in_array(@$last_status, [4,6,7])) {
                    array_push($file, implode('|',$registro42).PHP_EOL);
                }
                $last_status = $discente->vinculo_status;
            }
            $cpf_cnpj = $pessoa->cpf_cnpj;
            $referencia = $pessoa->referencia;
        }
        $filename = "$ano_censo-alunos-$page.txt";
        $filepath = "public/file/$filename";

        file_put_contents($filepath, $file);

        $view = [
            'pessoaPaginator'=>$pessoaPaginator,
            'filename'=>$filename,
            'filepath'=>$filepath
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
}
