<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        return is_null($value) ? 31 : null;
    }
    public function getNacionalidadeAttribute(){
        return (is_null($this->id_pais_nacionalidade) || $this->id_pais_nacionalidade == 31) ? 1 : 3;
    }
    public function getRacaAttribute(){
        $forma_ingresso = $this->discente[0]->id_forma_ingresso;
        switch ($this->id_raca) {
            case 1:
                $raca = 1;
                break;
            case 3:
                $raca = 2;
                break;            
            default:
                if(in_array($this->discente[0]->id_forma_ingresso, [2,11663,11662,11661,11657,11659])) {
                    $raca = 3;
                } elseif (in_array($this->discente[0]->id_forma_ingresso, [4, 11645,11656])) {
                    $raca = 5;
                } else {
                    $raca = 6;
                }                
                break;
        }
        return $raca;                
    }

    public function discente() {
        return $this->HasMany('App\Discente', 'id_pessoa');
    }
    public function municipio() {
        return $this->BelongsTo('App\Municipio', 'id_municipio_naturalidade');
    }
    public function pais() {
        return $this->BelongsTo('App\Pais', 'id_pais_nacionalidade');
    }
}