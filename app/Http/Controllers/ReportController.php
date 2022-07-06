<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JasperPHP\Facades\JasperPHP;

class ReportController extends Controller
{
    public function getDatabaseConfigSqlServer()
    {
        /* $jdbc_dir : Informe o caminho real onde o driver jdbc.
      Adicione o jdbc_driver conforme array abaixo.
      jdbc_url : informe o IP e a porta da instancia do seu banco e databasename informe o nome do banco */

        $jdbc_dir = '/var/www/laravel-jaspert/vendor/cossou/jasperphp/src/asperStarter/jdbc';
        return [
            'driver'   => 'generic',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
            'jdbc_driver' => 'com.microsoft.sqlserver.jdbc.SQLServerDriver',
            'jdbc_url' => 'jdbc:sqlserver://localhost:5432;databaseName=' . env('DB_DATABASE') . '',
            'jdbc_dir' =>  $jdbc_dir
        ];
    }

    public function getDatabaseConfig()
    {

        return [
            'driver'   => 'postgres',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE')
        ];
    }

    public function generateReport()
    {
        $extension = 'pdf';
        $name = 'testeJasper';
        $filename =  $name  . time();
        $output = base_path('/public/jasper/' . $filename);

        JasperPHP::compile(storage_path('app/public') . '/relations/test1.jrxml')->execute();

        JasperPHP::process(
            storage_path('app/public/relations/test1.jasper'), // input
            $output, // output
            array($extension),
            array("namaHp" => 'nokia'),
            $this->getDatabaseConfig()
        )->execute();

        $file = $output . '.' . $extension;

        if (!file_exists($file)) {
            abort(404);
        }
        if ($extension == 'xls') {
            header('Content-Description: Arquivo Excel');
            header('Content-Type: application/x-msexcel');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            flush(); // Flush system output buffer
            readfile($file);
            unlink($file);
            die();
        } else if ($extension == 'pdf') {
            return response()->file($file)->deleteFileAfterSend();
        }
    }

    public function getParameters()
    {
        $output =
            JasperPHP::list_parameters(storage_path('app/public') . '/relations/test1.jrxml')->execute();

        foreach ($output as $row) {

            $parameter_description = trim($row);
            //echo $parameter_description . '<br>' ;
            $exp = explode(" ", trim($parameter_description), 4);
            echo '<strong>Parameter:</strong>  ' .  $exp[1] .
                ' <strong>Tipe data:</strong>  ' . $exp[2] .
                ' <strong>Desc:</strong>   ' . $exp[3] . '<br>';
        }
    }
}
