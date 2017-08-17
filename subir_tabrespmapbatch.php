<?php
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/Santiago');
header('Content-type: application/vnd.ms-excel');//esta es la principal
header("Content-Disposition: attachment; filename=archivo.xls");
header("Pragma: no-cache");
header("Expires: 0");


/*require 'PHPExcel.php';
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
*/

$log = "log.txt";
$time = time();
$fecha = date("d-m-Y H:i:s", $time);
file_put_contents($log,"Hora de Inicio ".$fecha);

/*$connect = mysqli_connect('localhost','auditordb','Auditor_2016');
mysqli_select_db($connect,'auditordb');

if(!$connect){
    echo 'no me pude conectar';
}*/

$host = '127.0.0.1';	
$user = 'root';	
$pass = '';
$log = '';	

$mysqli = new mysqli($host,$user,$pass);
$mysqli->select_db('normalizacion_beco');

if(mysqli_connect_errno())
{
		$log .= date('Y-m-d_H:m:i')."INFO: No se pudo establecer la conexión con el servidor, ERROR: ".mysqli_connect_errno().".\n";
		echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: No se pudo establecer la conexión con el servidor, ERROR: ".mysqli_connect_errno().".\n";
		exit();
}
else 
{
		$log .= date('Y-m-d_H:m:i')."INFO: Conexión establecida con éxito.\n";
		echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Conexión establecida con éxito.\n";
}


$diaActual = date('Ymd');
$inicioMes = date('Ym').'01';

$fechas = compact('diaActual','inicioMes');

/*Buscamos archivos en el directorio Entradas*/

	$path = "/var/www/html/MAPCITY/CARGAS";

	$archivos = scandir($path);

	if(!empty($archivos)){

		//echo date('Y-m-d_H:m:i')." existen archivos en el direcotrio\n";
		
		$log .= date('Y-m-d_H:m:i')."INFO: Se ha validado que exista un archivo en el directorio.\n";
		echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Se ha validado que exista un archivo en el directorio.\n";
		
		$cargas = array_diff($archivos, array('.', '..'));

		
		foreach ($cargas as $carga) {
			
			//echo date('Y-m-d_H:m:i')." se va a cargar archivo ".$carga."\n";

			$log .= date('Y-m-d_H:m:i')." INFO: Se ha validado exitosamente el archivo ".$carga.".\n";
			$log .= date('Y-m-d_H:m:i')." INFO: Comenzará a cargarse el archivo ".$carga." a la base de datos.\n";
			echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Se ha validado exitosamente el archivo ".$carga.".\n";
			echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Comenzará a cargarse el archivo ".$carga." a la base de datos.\n";			

			$query = "LOAD DATA LOCAL INFILE '" . $path."/".$carga . "' INTO TABLE TABRESPMAP FIELDS TERMINATED BY ';' LINES TERMINATED BY '\n' (RUT_CLIENTE, ID_DIRECCION, DIR_ENVIADA, DIR_FORMATEADA, NOMBRE_VIA_CORTO, NOMBRE_VIA_LARGO, TIPO_VIA_CORTO, TIPO_VIA_LARGO, ESTADO, ALTURA, ANEXO, COMUNA_CORTO, COMUNA_LARGO, PROVINCIA_CORTO, PROVINCIA_LARGO, REGION_CORTO, REGION_LARGO, PAIS_LARGO, PAIS_CORTO, LAT, LNG, FECHA_PROCESO, NOM_ARCHIVO, FUENTE)";


			$mysqli->query($query);

			$log .= date('Y-m-d_H:m:i')."INFO: Se ha cargado la base exitosamente, de acuerdo al archivo ".$carga.".\n";
			echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Se ha cargado la base exitosamente, de acuerdo al archivo ".$carga.".\n";

			rename($path."/".$carga, 'CARGAS_REALIZADAS/'.date('Y-m-d_H:m:i').'_'.$carga);

			$log .= date('Y-m-d_H:m:i')."INFO: Se movio el archivo de la carpeta CARGAS a la de CARGAS_REALIZADAS.\n";
			echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: Se movio el archivo de la carpeta CARGAS a la de CARGAS_REALIZADAS.\n";			
		}

	}

	else {

		$log .= date('Y-m-d_H:m:i')."INFO: No existen archivos en el directorio de CARGAS.\n";
		echo 'HORA: '.date('Y-m-d_H:m:i').", INFO: No existen archivos en el directorio de CARGAS.\n";
		//exit();
	}

$log = "log.txt";
$time = time();
$fecha = date("d-m-Y H:i:s", $time);
file_put_contents($log,"Hora de Termino ".$fecha);
