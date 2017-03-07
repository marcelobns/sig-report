<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Discente extends AppModel {
    protected $table = 'public.discente';
    protected $primaryKey = 'id_discente';
    
    public function getTipoRegistroAttribute(){
        return 42;
    }
    public function getVinculoStatusAttribute(){
        $codigo = null;
        if(in_array($this->status, [1,8,9])){
            $codigo = 2;
        } elseif ($this->status == 5) {
            $codigo = 3;
        } elseif ($this->status == 3) {
            $codigo = 6;
        }
        return $codigo;
    }
    public function getSemestreIngressoAttribute(){
        return str_pad($this->periodo_ingresso.$this->ano_ingresso, 6, '0', STR_PAD_LEFT);
    }
    public function pessoa() {
        return $this->belongsTo('App\Pessoa', 'id_pessoa');
    }
    public function status_discente() {
        return $this->belongsTo('App\StatusDiscente', 'status');
    }
    public function curso() {
        return $this->belongsTo('App\Curso', 'id_curso');
    }
    public function discente_graduacao() {
        return $this->hasOne('App\DiscenteGraduacao', 'id_discente_graduacao');
    }    
    public function movimentacao_aluno(){
        return $this->hasOne('App\MovimentacaoAluno', 'id_discente')
                    ->where(['movimentacao_aluno.id_tipo_movimentacao_aluno'=>1]);
    }
    public function scopeJoinPessoa($query){
        return $query->join('comum.pessoa', 'pessoa.id_pessoa', '=', 'discente.id_pessoa');
    }
    public function scopeJoinCurso($query){
        return $query->join('public.curso', 'curso.id_curso', '=', 'discente.id_curso');
    }
}
