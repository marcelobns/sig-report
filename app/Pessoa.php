<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Pessoa extends AppModel {
    protected $table = 'comum.pessoa';
    protected $primaryKey = 'id_pessoa';

    public function getTipoRegistroAttribute(){
        return 41;
    }
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
        if(@$this->discente[0]->id_forma_ingresso == 34117) {
            return 3;
        }
        else {
            return (is_null($this->id_pais_nacionalidade)
                    || $this->id_pais_nacionalidade == 31) ? 1 : 3;
        }
    }
    public function getRacaAttribute(){
        $forma_ingresso = @$this->discente[0]->id_forma_ingresso;
        $racas = [
            1 => [1],
            2 => [3],
            3 => [2,11663,11662,11661,11657,11659],
            5 => [4,11645,11656]
        ];
        foreach ($racas as $key=>$options) {
            if(in_array($this->id_raca, $options)){
                return $key;
            }
            if(in_array($forma_ingresso, $options)){
                return $key;
            }
        }
        if(@$this->discente[0]->ano_ingresso >= 2014){
            return 3;
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
        $current = date('Y');
        $censo = date('Y')-1;
        return $this->HasMany('App\Discente', 'id_pessoa')
                    ->select(
                        'discente.*',
                        'movimentacao_aluno.ano_ocorrencia',
                        'movimentacao_aluno.periodo_ocorrencia',
                        'curriculo.ch_total_minima',
                        'discente_graduacao.ch_total_integralizada',
                        'curso.codigo_inep as curso_inep',
                        'curso.nome as curso_nome',
                        'curso.id_turno',
                        'curso.id_modalidade_educacao'
                        )
                    ->join('public.curso', 'curso.id_curso', '=', 'discente.id_curso')
                    ->leftJoin('graduacao.discente_graduacao', 'discente_graduacao.id_discente_graduacao', '=', 'discente.id_discente')
                    ->leftJoin('graduacao.curriculo', 'curriculo.id_matriz', '=', 'discente_graduacao.id_matriz_curricular')
                    ->leftJoin('ensino.movimentacao_aluno', function($join){
                        $join->whereRaw("movimentacao_aluno.id_discente = discente.id_discente");
                        $join->whereRaw("id_tipo_movimentacao_aluno in (1, 315)");
                    })
                    ->whereRaw("discente.nivel='G' and discente.status in (1, 8, 9, 5, 3) and discente.id_curso is not null and discente.ano_ingresso <= '$censo' ")
                    ->whereRaw("(ano_ocorrencia = '$censo' or (ano_ocorrencia = '$current' and id_tipo_movimentacao_aluno in (1, 315)) or ano_ocorrencia is null)");
    }
    public function scopeJoinMunicipio($query){
        return $query->leftJoin('comum.municipio', 'municipio.id_municipio', '=', 'pessoa.id_municipio_naturalidade');
    }
    public function scopeJoinPais($query){
        return $query->leftJoin('comum.pais', 'pais.id_pais', '=', 'pessoa.id_pais_nacionalidade');
    }
}
