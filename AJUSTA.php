<?php

echo "\nIngrese Fecha inicio (yyyy-mm-dd) : ";
$fec_ini=trim(fgets(STDIN,256));

echo "\nIngrese Fecha fin (yyyy-mm-dd) : ";
$fec_fin=trim(fgets(STDIN,256));

$conection = Conectar();

mysqli_set_charset($conection, "utf8");

require_once('lib/nusoap.php');

$ruta_entrada	= "ENTRADA/";
$ruta_salida	= "SALIDA/";
$ruta_cargados	= "PROCESADOS/";
$ruta_errores	= "ERRORES/";

echo "\nProceso iniciado: ".date('d-M-Y H:i:s')."\n\n";

$select = "";
$select = "SELECT NOM_ARCHIVO,TIPO_ARCHIVO,FECHA_ORIG_ARCH FROM TABDIR_NOR 
			WHERE FECHA_ORIG_ARCH>='$fec_ini' AND FECHA_ORIG_ARCH <='$fec_fin' GROUP BY 1,2,3";

$sqlf = mysqli_query($conection,$select);

WHILE ($file = mysqli_fetch_array($sqlf)){
	
	$INI_CARGA = date('Y-m-d H:i:s');
	
	$archivo		= $file['NOM_ARCHIVO'];				
	$tipo_archivo	= $file['TIPO_ARCHIVO'];
	$fec_arch 		= $file['FECHA_ORIG_ARCH'];
	
	$s_archivo_alta ="SALIDA/".$archivo."_d.TXT";
	$archivo_alta=fopen ( $s_archivo_alta,"w");
	
	$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
	$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=$count_jsonG=0;
	$count_99_1=$count_99_N_1=0;
	$sector_validado = '';
	
	$select = "SELECT * FROM TABDIR_NOR 
			WHERE DIR_ASIGNACION=1 AND NUM_SECTOR='99' AND VALIDA_DIR IN (1,4) AND NOM_ARCHIVO ='$archivo' ";

	$sqlr = mysqli_query($conection,$select);

	WHILE ($file1 = mysqli_fetch_array($sqlr))
		{
				echo $cont++;
				echo chr(13);
				
				$RUT_CLIENTE 	= $file1['RUT_CLIENTE'];
				$ID_DIRECCION 	= $file1['ID_DIRECCION'];
				$VALIDA_DIR 	= $file1['VALIDA_DIR'];
				$NEW_SECTOR 	= $file1['NEW_SECTOR'];
				
			$select = "SELECT COUNT(*) FROM TABRESPMAP 
			WHERE  ID_DIRECCION = $ID_DIRECCION  AND NOM_ARCHIVO ='$archivo' ";
			
			//ECHO "PASO: ".$select;$P= strtoupper(trim(fgets(STDIN,256)));	

				$sql11 = mysqli_query($conection,$select);				
				$file11 = mysqli_fetch_array($sql11);
				
				if( $file11[0] == 0 ){
					
			
								$SQL_insertar = "UPDATE TABDIR_NOR SET VALIDA_DIR=0,NEW_SECTOR=''
												WHERE NOM_ARCHIVO ='$archivo' AND RUT_CLIENTE='$RUT_CLIENTE' 
												AND ID_DIRECCION='$ID_DIRECCION'";
												
								fwrite($archivo_alta,$archivo.";".$RUT_CLIENTE.";".$ID_DIRECCION.";".$VALIDA_DIR.";".$NEW_SECTOR."\n");
								//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));	
								
								$result		= mysqli_query($conection,$SQL_insertar);
							}			
			}
			
	}//fin ciclo 


echo "\nProceso finalizado: ".date('d-M-Y H:i:s')."\n\n";

function Conectar(){

	// PARAMETRIZACION DATOS DE CONEXION A BASE DE DATOS

	$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "normalizacion_beco_web";		
	
	/*$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "sistemaweb05052017";*/
	
	
	// Create connection
	$conection = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conection->connect_error) {
		die("Connection failed: " . $conection->connect_error);
	}else {
		//echo 'Connection OK '.$conection;
	}
	return $conection;
}

?>