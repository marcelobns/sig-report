<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        return $this->BelongsTo('App\Pessoa', 'id_pessoa');
    }
    public function statusDiscente() {
        return $this->BelongsTo('App\StatusDiscente', 'status');
    }
    public function curso() {
        return $this->BelongsTo('App\Curso', 'id_curso');
    }
    public function scopeJoinPessoa($query) {
        $query->join('comum.pessoa', 'pessoa.id_pessoa', '=', 'public.discente.id_pessoa');
    }
    public function scopeJoinCurso($query) {
        $query->join('public.curso', 'curso.id_curso', '=', 'public.discente.id_curso');
    }
}
