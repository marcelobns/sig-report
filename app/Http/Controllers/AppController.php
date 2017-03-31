<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppController extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function power_up(){
        ini_set('max_execution_time', 60);
        ini_set('memory_limit', '2G');
    }
    public function current_date() {
        return date('Y-m-d Hi ');
    }
    public function clear_dir($dir = 'public/file/'){
        $files = $this->get_files($dir);
        foreach ($files as $i => $filename) {
            unlink(realpath("$dir/$filename"));
        }
        return true;
    }
    public function get_files($dir = 'public/file/'){
        return array_diff(scandir($dir), array('..', '.'));
    }
    public function create_csv($dir, $collection, $columns){
        $filename = md5($collection);
        $filepath = realpath("$dir/$filename.csv");

        if (!file_exists($filepath)) {
            $file = fopen($filepath, 'w');

            fputcsv($file, $columns, ';');
            foreach ($collection as $i=>$row) {
                fputcsv($file, array_intersect_key($row, $columns), ';');
            }
            fclose($file);
        }
        return $filepath;
    }
    public function console($data){
      echo "<script>console.log($data)</script>";
    }
    public function translit($dirty){
        $clean = iconv('UTF-8', 'US-ASCII//TRANSLIT', strtoupper($dirty));
        return strtoupper(str_ireplace(['ˆ','^','\'','´','˜','~'], '', $clean));
    }
}
