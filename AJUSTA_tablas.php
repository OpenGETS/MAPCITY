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
	
$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=$count_jsonG=0;
$count_99_1=$count_99_N_1=0;
$sector_validado = '';

$select = "SELECT * FROM TABDIR_NOR 
		WHERE DIR_ASIGNACION=1 AND NUM_SECTOR='99' AND VALIDA_DIR != 0 AND FECHA_ORIG_ARCH>='$fec_ini' AND FECHA_ORIG_ARCH <='$fec_fin' ";
		
//ECHO "PASO 0: ".$select;$P= strtoupper(trim(fgets(STDIN,256)));	

$sqlr = mysqli_query($conection,$select);

WHILE ($file1 = mysqli_fetch_array($sqlr)){
	
				echo $cont++;
				echo chr(13);
				
				$RUT_CLIENTE 	= $file1['RUT_CLIENTE'];
				$ID_DIRECCION 	= $file1['ID_DIRECCION'];
				$VALIDA_DIR 	= $file1['VALIDA_DIR'];
				$NEW_SECTOR 	= $file1['NEW_SECTOR'];
				$archivo		= $file1['NOM_ARCHIVO'];
				
				$select = "SELECT RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,
							NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,COMUNA_CORTO,COMUNA_LARGO,
							PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,PAIS_LARGO,PAIS_CORTO,LAT,LNG,FUENTE,
							COUNT(*) 
							FROM TABRESPMAP 
							WHERE  ID_DIRECCION = $ID_DIRECCION  AND NOM_ARCHIVO ='$archivo' ";
			
				//ECHO "PASO 1: ".$select;$P= strtoupper(trim(fgets(STDIN,256)));	

				$sql11 = mysqli_query($conection,$select);				
				$file11 = mysqli_fetch_array($sql11);
				
				if( $file11[22] > 0 ){
					
					$RUT_CLIENTE 		= $file11[0];
					$ID_DIRECCION 		= $file11[1];
					$DIR_ENVIADA 		= $file11[2];
					$DIR_FORMATEADA 	= $file11[3];
					$NOMBRE_VIA_CORTO 	= $file11[4];
					$NOMBRE_VIA_LARGO 	= $file11[5];
					$TIPO_VIA_CORTO 	= $file11[6];
					$TIPO_VIA_LARGO 	= $file11[7];
					$ESTADO 			= $file11[8];
					$ALTURA 			= $file11[9];
					$ANEXO 				= $file11[10];
					$COMUNA_CORTO 		= $file11[11];
					$COMUNA_LARGO 		= $file11[12];
					$PROVINCIA_CORTO 	= $file11[13];
					$PROVINCIA_LARGO 	= $file11[14];
					$REGION_CORTO 		= $file11[15];
					$REGION_LARGO 		= $file11[16];
					$PAIS_LARGO 		= $file11[17];
					$PAIS_CORTO 		= $file11[18];
					$LAT 				= $file11[19];
					$LNG 				= $file11[20];
					$FUENTE 			= $file11[21];
								
					$select = "SELECT NOM_ARCHIVO,FECHA_PROCESO FROM TABDIR_NOR 
					WHERE FECHA_ORIG_ARCH>='$fec_ini' AND FECHA_ORIG_ARCH <='$fec_fin' 
					AND ID_DIRECCION = $ID_DIRECCION AND NOM_ARCHIVO != '$archivo'  
					GROUP BY 1,2 ";
					
					//ECHO "PASO 2: ".$select;$P= strtoupper(trim(fgets(STDIN,256)));
					
					$sqlMAP = mysqli_query($conection,$select);

					WHILE ($fileMAP= mysqli_fetch_array($sqlMAP)){
					
						$NOM_ARCHIVO 		= $fileMAP[0];
						$FECHA_PROCESO_MAP 	= $fileMAP[1];
						
						
						$SQL_insertar = "INSERT INTO TABRESPMAP 
						(RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,
								NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,COMUNA_CORTO,COMUNA_LARGO,
								PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,PAIS_LARGO,PAIS_CORTO,LAT,LNG,FUENTE,FECHA_PROCESO,NOM_ARCHIVO)
						VALUES
						( '".$RUT_CLIENTE."','".$ID_DIRECCION."','".$DIR_ENVIADA."','".$DIR_FORMATEADA."','".$NOMBRE_VIA_CORTO."','".
						$NOMBRE_VIA_LARGO."','".$TIPO_VIA_CORTO."','".$TIPO_VIA_LARGO."','".$ESTADO."','".$ALTURA."','".$ANEXO."','".
						$COMUNA_CORTO."','".$COMUNA_LARGO."','".$PROVINCIA_CORTO."','".$PROVINCIA_LARGO."','".$REGION_CORTO."','".
						$REGION_LARGO."','".$PAIS_LARGO."','".$PAIS_CORTO."','".$LAT."','".$LNG."','".$FUENTE."','".
						$FECHA_PROCESO_MAP."','".$NOM_ARCHIVO."')";
						
						//ECHO "PASO 3: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
						
						$result		= mysqli_query($conection,$SQL_insertar);
					}
					
					$SQL_insertar = "UPDATE TABDIR_NOR SET VALIDA_DIR=$VALIDA_DIR,NEW_SECTOR='$NEW_SECTOR'
					WHERE ID_DIRECCION='$ID_DIRECCION' AND VALIDA_DIR = 0 
					AND FECHA_ORIG_ARCH>='$fec_ini' AND FECHA_ORIG_ARCH <='$fec_fin' ";
					
					//ECHO "PASO 4: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
					
					$result		= mysqli_query($conection,$SQL_insertar);
							
					
					//fwrite($archivo_alta,$archivo.";".$RUT_CLIENTE.";".$ID_DIRECCION.";".$VALIDA_DIR.";".$NEW_SECTOR."\n");
						
					
				}			
			}
echo "\nProceso finalizado: ".date('d-M-Y H:i:s')."\n\n";

function Conectar(){

	// PARAMETRIZACION DATOS DE CONEXION A BASE DE DATOS


	$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "normalizacion_beco_web";
	
	
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