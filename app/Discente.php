<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Discente extends AppModel {
    protected $table = 'public.discente';
    protected $primaryKey = 'id_discente';
    protected static function csvColunas() {
        return [
            "Matricula",
            "Ingresso",
            "Nome",
            "CPF",
            "Nascimento",
            "Situação",
            "Curso"
        ];
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
}
