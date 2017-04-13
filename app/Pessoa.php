<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class Pessoa extends AppModel {
    protected $table = 'comum.pessoa';
    protected $primaryKey = 'id_pessoa';
    protected $guarded = array();

    public function getCpfAttribute() {
        return str_pad($this->cpf_cnpj, 11, '0', STR_PAD_LEFT);
    }
    public function getDataNascimentoAttribute($value) {
        return date('d/m/Y', strtotime($value));
    }
    public function getIdPaisNacionalidadeAttribute($value){
        return is_null($value) ? 31 : $value;
    }
    public function getNacionalidadeAttribute(){
        if($this->id_forma_ingresso == 34117) {
            return 3;
        }
        return (is_null($this->id_pais_nacionalidade)
                || $this->id_pais_nacionalidade == 31) ? 1 : 3;
    }
    public function getDocEstrangeiroAttribute(){
        return $this->cod_pais_pingifes != 'BRA' ? $this->passaporte : null;
    }
    public function getRacaCorAttribute(){
        $racas = [
            1 => [1],
            2 => [3],
            3 => [null,-1,2,2014,11663,11662,11661,11657,11659],
            5 => [4,11645,11656]
        ];
        foreach ($racas as $key=>$options) {
            if(in_array($this->id_raca, $options)){
                return $key;
            }
            if(in_array($this->id_forma_ingresso, $options)){
                return $key;
            }
            if(in_array($this->ano_ingresso, $options)){
                return $key;
            }
        }
        return 6;
    }
    public function getMunicipioCodigoAttribute(){
        return trim(str_replace('-','',$this->codigo));
    }
    public function pais() {
        return $this->BelongsTo('App\Pais', 'id_pais_nacionalidade');
    }
    public function discente() {
        return $this->HasMany('App\Discente', 'id_pessoa');
    }
    public function scopeJoinMunicipio($query){
        return $query->leftJoin('comum.municipio', 'municipio.id_municipio', '=', 'pessoa.id_municipio_naturalidade');
    }
    public function scopeJoinPais($query){
        return $query->leftJoin('comum.pais', 'pais.id_pais', '=', 'pessoa.id_pais_nacionalidade');
    }
    public function scopeCenso($query, $ano, $page, $limit) {
        $collection = DB::select(
            "SELECT
                pessoa.id_pessoa,
                pessoa.cpf_cnpj,
                pessoa.sexo,
                pessoa.nome_oficial,
                pessoa.nome_mae,
                pessoa.passaporte,
                pessoa.data_nascimento,
                pessoa.id_pais_nacionalidade,
                pessoa.id_tipo_necessidade_especial,
                pessoa.escola_publica,
                referencia,
                discente.matricula,
                discente.ano_ingresso,
                discente.periodo_ingresso,
                discente.id_forma_ingresso,
                municipio.id_unidade_federativa,
                municipio.codigo,
                COALESCE(pais.cod_pais_pingifes, 'BRA') as cod_pais_pingifes,
                curso.dt_inicio_funcionamento,
                curso.id_modalidade_educacao,
                matriz.codigo_inep,
                matriz.id_turno,
                curriculo.ch_total_minima,
                graduacao.ch_total_integralizada,
                (SELECT sum(ch_total)
                 FROM ensino.matricula_componente componente
                 LEFT JOIN ensino.componente_curricular_detalhes detalhes on detalhes.id_componente_detalhes = componente.id_componente_detalhes
                 WHERE componente.ano = 2016 and componente.periodo = referencia and componente.id_discente = discente.id_discente and componente.id_situacao_matricula in (4,22,24)
                ) as ch_periodo_integralizado,
                (SELECT max(movimentacao.id_tipo_movimentacao_aluno)
                 FROM ensino.movimentacao_aluno movimentacao
                 WHERE movimentacao.ano_referencia = 2016 and movimentacao.periodo_referencia = referencia and movimentacao.id_discente = discente.id_discente
                       and movimentacao.id_tipo_movimentacao_aluno in (10,17,101,315)
                ) as status
            FROM unnest(ARRAY[1,2]) referencia, public.discente discente
            INNER JOIN public.curso curso on curso.id_curso = discente.id_curso and discente.id_curso != 581633
            INNER JOIN comum.pessoa pessoa on pessoa.id_pessoa = discente.id_pessoa
            LEFT JOIN comum.municipio municipio on municipio.id_municipio = pessoa.id_municipio_naturalidade
            LEFT JOIN comum.pais pais on pais.id_pais = pessoa.id_pais_nacionalidade
            INNER JOIN graduacao.discente_graduacao graduacao on graduacao.id_discente_graduacao = discente.id_discente
            INNER JOIN graduacao.curriculo curriculo on curriculo.id_curriculo = discente.id_curriculo
            INNER JOIN graduacao.matriz_curricular matriz on matriz.id_matriz_curricular = curriculo.id_matriz
            LEFT JOIN ensino.matricula_componente matricula on matricula.id_discente = discente.id_discente and matricula.ano = $ano
            LEFT JOIN ensino.movimentacao_aluno movimento on movimento.id_discente = discente.id_discente and movimento.ano_referencia = $ano
            WHERE discente.nivel = 'G' and discente.ano_ingresso <= $ano and ($ano in (matricula.ano,movimento.ano_referencia))
            GROUP BY
                pessoa.id_pessoa,
                pessoa.cpf_cnpj,
                pessoa.sexo,
                pessoa.nome_oficial,
                pessoa.nome_mae,
                pessoa.passaporte,
                pessoa.data_nascimento,
                pessoa.id_pais_nacionalidade,
                pessoa.id_tipo_necessidade_especial,
                pessoa.escola_publica,
                referencia,
                discente.id_discente,
                discente.matricula,
                discente.ano_ingresso,
                discente.periodo_ingresso,
                discente.id_forma_ingresso,
                municipio.id_unidade_federativa,
                municipio.codigo,
                pais.cod_pais_pingifes,
                curso.dt_inicio_funcionamento,
                curso.id_modalidade_educacao,
                curriculo.ch_total_minima,
                graduacao.ch_total_integralizada,
                matriz.codigo_inep,
                matriz.id_turno
            ORDER BY pessoa.nome_oficial, discente.matricula, referencia");

            $offset = ($page * $limit) - $limit;
            return new Paginator(array_slice($collection, $offset, $limit), count($collection), $limit);
    }
}
