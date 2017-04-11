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
        return $this->HasMany('App\Discente', 'id_pessoa');
    }
    public function scopeJoinMunicipio($query){
        return $query->leftJoin('comum.municipio', 'municipio.id_municipio', '=', 'pessoa.id_municipio_naturalidade');
    }
    public function scopeJoinPais($query){
        return $query->leftJoin('comum.pais', 'pais.id_pais', '=', 'pessoa.id_pais_nacionalidade');
    }
}
