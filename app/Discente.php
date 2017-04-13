<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class Discente extends AppModel {
    protected $table = 'public.discente';
    protected $primaryKey = 'id_discente';
    protected $guarded = array();

    public function getTipoRegistroAttribute(){
        return 42;
    }
    public function getVinculoStatusAttribute(){
        $status = [
            2 => [null],
            3 => [101],
            4 => [4, 6, 9, 10, 17, 305, 306, 308],
            // 5 => [],
            6 => [315],
            // 7 => []
        ];
        foreach ($status as $key=>$options) {
            if(in_array($this->status, $options)){
                return $key;
            }
        }
        return 2;
    }
    public function getTurnoCodigoAttribute(){
        switch (@$this->id_turno) {
            case 1078700:
                return 1;
                break;
            case 1078706:
                return 2;
                break;
            case 1078702:
                return 3;
                break;
            case 1078707:
                return 3;
                break;
            default:
                return 4;
                break;
        }
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
