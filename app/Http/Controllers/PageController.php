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
                'pessoa.*',
                'municipio.id_unidade_federativa',
                'municipio.codigo'
            )
            ->selectRaw("COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes")
            ->joinMunicipio()
            ->joinPais()
            ->orWhereRaw("id_pessoa in (SELECT id_pessoa FROM public.discente
                                        LEFT JOIN ensino.movimentacao_aluno on movimentacao_aluno.id_discente = discente.id_discente
                                        WHERE id_pessoa = pessoa.id_pessoa
                                            and ano_ingresso <= '$censo'
                                            and id_curso is not null
                                            and nivel = 'G'
                                            and status in (1, 5, 8, 9)
                                            and (ano_ocorrencia = '$censo' or ano_ocorrencia = '$current' or ano_ocorrencia is null)
                                            )")
            ->orWhereRaw("id_pessoa in (SELECT id_pessoa FROM public.discente
                                        INNER JOIN ensino.movimentacao_aluno on movimentacao_aluno.id_discente = discente.id_discente
                                        WHERE id_pessoa = pessoa.id_pessoa
                                            and ano_ingresso <= '$censo'
                                            and id_curso is not null
                                            and ano_ocorrencia = '$censo'
                                            and nivel = 'G'
                                            and status = 3) ")
                                            ->orderBy('nome')
                                            ->paginate(350);

        $apoio_social = $this->read_file("public/file/Apoio-Social.csv");
        $atividade_extra = $this->read_file("public/file/Atividade-Extracurricular.csv");

        foreach ($pessoaPagination as $key => $row) {
            $pessoa = [
                'REGISTRO'=>$row->tipo_registro,
                'ALUNO_INEP'=>null,
                'NOME'=>$this->translit($row->nome_oficial),
                'CPF'=>$row->cpf,
                'DOC_ESTRANGEIRO'=> ($row->id_pais_nacionalidade != 31 ? $row->passaport : null),
                'DATA_NASCIMENTO'=> str_replace('/','',$row->data_nascimento),
                'SEXO'=> (int)($row->sexo == 'F'),
                'RACA'=> $row->raca,
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
                } elseif($pessoa['NACIONALIDADE'] == 1) {
                    $pessoa['DOC_ESTRANGEIRO'] = null;
                }
            }
            array_push($file, implode('|', $pessoa).PHP_EOL);
            foreach ($row->discente as $j => $discente) {
                if(($row->nacionalidade == 1
                    && (!isset($row->municipio_codigo) OR @$row->id_unidade_federativa == -1))
                        || !isset($row->raca)
                        || $discente->curso_inep == ''
                        || $discente->ano_ingresso == $current){
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
                        'RG_EXPEDIDOR'=>@$row->orgao_expedicao_identidade
                    ]);
                }
                $has_apoio_social = in_array($row->cpf_cnpj, $apoio_social);
                $has_atividade_extra = in_array($row->cpf_cnpj, $atividade_extra);

                $aluno = [
                    'REGISTRO'=>$discente->tipo_registro,
                    'INGRESSO_PERIODO'=>$discente->periodo_ingresso,
                    'CURSO_INEP'=>$discente->curso_inep,
                    'EAD_POLO'=> $discente->id_modalidade_educacao == 2 ? 1033528 : null,
                    'MATRICULA'=>$discente->matricula,
                    'TURNO'=>$discente->id_modalidade_educacao != 2 ? $discente->turno_codigo : null,
                    'VINCULO_STATUS'=>$discente->vinculo_status,
                    'CURSO_ORIGEM'=> null,
                    'CONCLUSAO_PERIODO'=> null,
                    'PARFOR'=> in_array($discente->curso_inep, [5001023,1156313,118566,1186297,
                                                                1186746,16895,16898,118568,1184530,
                                                                22532,31230,22533,31229,68225,68226,
                                                                68228,1185309,118564,1259131,16902,16896]) ? 0 : null,
                    'SEMESTRE_INGRESSO'=> $discente->semestre_ingresso,
                    'TIPO ESCOLA MEDIO'=>is_null($row->escola_publica) ? ((int) !in_array($discente->id_forma_ingresso, [11662,11654]))+1 : (int)$row->escola_publica,
                    'INGR VESTIBULAR'=> (int)in_array($discente->id_forma_ingresso, [34110,11657,11656,11650,11658,11655,11659,11660]),
                    'INGR ENEM'=> (int)in_array($discente->id_forma_ingresso, [51252808,11663,11662,11654,11652,11653,11661]),
                    'INGR AVALIACAO SERIADA'=> 0,
                    'INGR SELECAO SIMPLIFICADA'=> (int)in_array($discente->id_forma_ingresso, [37350,11645]),
                    'INGR EGRESSO BI/LI'=>0,
                    'INGR PEC-G'=>(int)in_array($discente->id_forma_ingresso, [34117]),
                    'INGR TRANS EX OFICIO'=>(int)in_array($discente->id_forma_ingresso, [11644,11639,11642]),
                    'INGR DECIS JUDICIAl'=>0,
                    'INGR VAGAS REMANESC'=>(int)in_array($discente->id_forma_ingresso, [1697747,11643]),
                    'INGR VAGAS PROG. ESPECIAIS'=>(int)in_array($discente->id_forma_ingresso, [34116]),
                    'MOBILIDADE_ACADEMICA'=>($discente->vinculo_status == 2 ? 0 : null), // VERIFICAR, MOBILIDADE : CRINT e PROEG
                    'MOB_TIPO'=>null,
                    'MOB_DESTINO'=>null,
                    'MOB_INTERNACIONAL'=>null,
                    'MOB_PAIS_DESTINO'=>null,
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
                    'ATIVIDADE_PESQUISA'=>$has_atividade_extra ? (int)in_array("PESQUISA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_PESQUISA_BOLSA'=>$has_atividade_extra ? (int)in_array("PESQUISA_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_EXTENSAO'=>$has_atividade_extra ? (int)in_array("EXTENSAO", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_EXTENSAO_BOLSA'=>$has_atividade_extra ? (int)in_array("EXTENSAO_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_MONITORIA'=>$has_atividade_extra ? (int)in_array("MONITORIA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_MONITORIA_BOLSA'=>$has_atividade_extra ? (int)in_array("MONITORIA_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_ESTAGIO'=>$has_atividade_extra ? (int)in_array("ESTAGIO", $atividade_extra[$row->cpf_cnpj]) : null,
                    'ATIVIDADE_ESTAGIO_BOLSA'=>$has_atividade_extra ? (int)in_array("ESTAGIO_BOLSA", $atividade_extra[$row->cpf_cnpj]) : null,
                    'CH_CURSO'=>$discente->ch_total_minima,
                    'CH_INTEGRALIZADO'=> ($discente->vinculo_status == 6 && $discente->ch_total_integralizada < $discente->ch_total_minima ) ?
                                          $discente->ch_total_minima : $discente->ch_total_integralizada
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
            }
        }
        $page = @$_GET['page'] ? $_GET['page'] : 1;
        $filename = "public/file/$censo-alunos-$page.txt";

        if(sizeof($inconsistencias) == 0){
            file_put_contents($filename, $file);
        }
        $view = [
            'inconsistencias'=>$inconsistencias,
            'pessoaPagination'=>$pessoaPagination,
            'filename'=>$filename,
            'censo'=>$censo
        ];
        return view('page.censup', $view);
    }
    public function read_file($filepath){
        ini_set("auto_detect_line_endings", "1");

        $pessoas = [];
        $handle = fopen($filepath, "r");
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
            $i = (int)$line[0];

            if(!isset($pessoas[$i]))
                $pessoas[$i] = [];

            array_push($pessoas[$i], $line[2]);
        }
        fclose($handle);
        return $pessoas;
    }
}
