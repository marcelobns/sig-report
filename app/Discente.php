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
        if ($this->status == null) {
            $status = 2;
        } elseif ($this->status == 5) {
            $status = 3;
        } elseif ($this->status == 3) {
            $status = 6;
        } elseif ($this->status == 6) {
            $status = 4;
        } elseif ($this->status == 9) {
            $status = 4;
        }
        return $status;
    }
    public function getTurnoCodigoAttribute(){
        switch (@$this->id_turno) {
            case 1078700:
                $codigo = 1;
                break;
            case 1078706:
                $codigo = 2;
                break;
            case 1078702:
                $codigo = 3;
                break;
            case 1078707:
                $codigo = 3;
                break;
            default:
                $codigo = 4;
                break;
        }
        return $codigo;
    }
    public function getSemestreIngressoAttribute(){
        $this->periodo_ingresso = $this->periodo_ingresso > 2 ? 2 : $this->periodo_ingresso;
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
    public function polo_curso(){
        return $this->belongsTo('App\PoloCurso', 'id_curso')->where(['id_polo'=>20356]);
    }
    public function discente_graduacao() {
        return $this->hasOne('App\DiscenteGraduacao', 'id_discente_graduacao');
    }
    public function movimentacao_aluno(){
        return $this->hasOne('App\MovimentacaoAluno', 'id_discente');
    }
}
