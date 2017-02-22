<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MovimentacaoAluno extends AppModel {
    protected $table = 'ensino.movimentacao_aluno';
    protected $primaryKey = 'id_movimentacao_aluno';
}
