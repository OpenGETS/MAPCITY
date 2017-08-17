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

	$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
	$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=$count_jsonG=0;
	$count_99_1=$count_99_N_1=0;
	$sector_validado = '';
	
	$select = "SELECT * FROM TABDIR_NOR 
			WHERE DIR_ASIGNACION=1 AND NUM_SECTOR='99' AND VALIDA_DIR=0 AND NOM_ARCHIVO ='$archivo' AND 
			FECHA_ORIG_ARCH>='$fec_ini' AND FECHA_ORIG_ARCH <='$fec_fin' ";

	$sqlr = mysqli_query($conection,$select);

	WHILE ($file1 = mysqli_fetch_array($sqlr))
		{
				echo $cont++;
				echo chr(13);
				
				$RUT_CLIENTE 	= $file1['RUT_CLIENTE'];
				$ID_DIRECCION 	= $file1['ID_DIRECCION'];
				$TIPO 			= $file1['TIPO'];
				$CALLE 			= $file1['CALLE'];
				$NUMERO 		= $file1['NUMERO'];
				$COD_COMUNA 	= $file1['COD_COMUNA'];
				$BLOCK 			= $file1['BLOCK'];
				$PISO 			= $file1['PISO'];
				$DEPTO 			= $file1['DEPTO'];
				$POB_VILLA 		= $file1['POB_VILLA'];
				$COD_CIUDAD 	= $file1['COD_CIUDAD'];
				$ESTADO_DIR 	= $file1['ESTADO_DIR'];
				$OBSER1 		= $file1['OBSER1'];
				$DIR_ASIGNACION = $file1['DIR_ASIGNACION'];
				$SECTOR 		= $file1['SECTOR'];
				$DATOS_COMPLE 	= $file1['DATOS_COMPLE'];
				$LATITUD 		= $file1['LATITUD'];
				$LONGITUD 		= $file1['LONGITUD'];
				$nsector		= $file1['NUM_SECTOR'];
				
				$id_corr 		= $file1['ID_COR'];
				
				$sector_validado = '';
				$valida_dir	= 0;
				$opc_xml = 1;				
				
				//ECHO $nsector."--".$CALLE."--".$NUMERO;$P= strtoupper(trim(fgets(STDIN,256)));

				if( VALIDA_ENVIO($ID_DIRECCION ,$conection) == 0){
					
						$direccion = $CALLE;
						
						$opc_xml = manda_google($CALLE,$NUMERO,$POB_VILLA,$RUT_CLIENTE,$ID_DIRECCION,$SECTOR,$valida_dir,$direccion,$archivo,$sector_validado,$conection);
						
						if($sector_validado == '' AND $valida_dir == 1 ){$valida_dir=4;}
						
						if($opc_xml == 0){
					
								$SQL_insertar = "UPDATE TABDIR_NOR SET VALIDA_DIR='$valida_dir',NEW_SECTOR='$sector_validado'
												WHERE ID_COR=$id_corr AND NOM_ARCHIVO ='$archivo' AND RUT_CLIENTE='$RUT_CLIENTE' 
												AND ID_DIRECCION='$ID_DIRECCION'";
												
								//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));	
								
								$result		= mysqli_query($conection,$SQL_insertar);
							}				
					}			
				
			}
			
		if($cont >0){
		
			//ECHO "PASO: RENOMBRAR ARCHIVOS";$P= strtoupper(trim(fgets(STDIN,256)));
			$FIN_CARGA = date('Y-m-d H:i:s');
			$consulta = "SELECT A.NOM_ARCHIVO,
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO) 'TOTAL_REG',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 ) 'TOTAL_99',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 AND DIR_ASIGNACION=1 ) 'TOTAL_99_1',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 AND DIR_ASIGNACION !=1 ) 'TOTAL_99_N_1',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR !=99 ) 'TOTAL_00',
			(SELECT COUNT(*) FROM TABRESPMAP B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO ) 'TOTAL_JSON',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 AND DIR_ASIGNACION = 1 AND VALIDA_DIR = 0  ) 'TOT99_SINRESP',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 AND DIR_ASIGNACION = 1 AND VALIDA_DIR = 4  ) 'TOT99_NOR_SINSEC',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR=99 AND DIR_ASIGNACION = 1 AND VALIDA_DIR = 1  ) 'TOT99_NOR_SEC',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR !=99 AND VALIDA_DIR = 5 ) 'TOT00_SINSEC',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR !=99 AND VALIDA_DIR = 3 ) 'TOT00_CAMB_SEC',
			(SELECT COUNT(*) FROM TABDIR_NOR B WHERE A.NOM_ARCHIVO = B.NOM_ARCHIVO AND NUM_SECTOR !=99 AND VALIDA_DIR = 2 ) 'TOT00_MANT'
			FROM TABDIR_NOR A WHERE A.NOM_ARCHIVO IN ('$archivo') GROUP BY 1 ";
			
			$result 	= mysqli_query($conection,$consulta);
			$row12 		= mysqli_fetch_array($result);
			
			$count_total 	= $row12['TOTAL_REG'];
			$count_s00 		= $row12['TOTAL_00'];
			$count_s99 		= $row12['TOTAL_99'];
			$count_json 	= $row12['TOTAL_JSON'];
			$count_99_1 	= $row12['TOTAL_99_1'];
			$count_99_N_1 	= $row12['TOTAL_99_N_1'];
			$count_sin_nor 	= $row12['TOT99_SINRESP'];;
			$count_nor_sec 	= $row12['TOT99_NOR_SEC'];
			$count_nor_sinsec = $row12['TOT99_NOR_SINSEC'];
			$count_s00_sinsec = $row12['TOT00_SINSEC'];
			$count_s00_cambsec = $row12['TOT00_CAMB_SEC'];
			$count_s00_mansec = $row12['TOT00_MANT'];
			
			graba_log_carga($archivo,$INI_CARGA,$FIN_CARGA,$cont_error,$count_total,$count_s00,$count_s99,$count_json,$count_99_1,
					$count_99_N_1,$count_sin_nor,
					$count_nor_sec,$count_nor_sinsec,$count_s00_sinsec,$count_s00_cambsec,
					$count_s00_mansec,$conection);
			
			crear_informe_beco($archivo,$fuentes,$conection);
		}	
	}//fin ciclo 


echo "\nProceso finalizado: ".date('d-M-Y H:i:s')."\n\n";

//echo "\nTotal archivo: ".$cont." -- Procesados: ".$cont_cargados." -- Rechazados: ".$cont_error."\n\n";
function caracter_reservado_x($sBeginWord)
{
  $sNewWord=str_replace(",",".",$sBeginWord);
  return $sNewWord;
}
function graba_errores($archivo,$desc_error,$enlace_base){
		
		$inser_log = "INSERT INTO TABERRORES VALUES('$archivo',CURDATE(),CURTIME(),'$desc_error')";
		/*ECHO $inser_log;
		$P44= strtoupper(trim(fgets(STDIN,256)));*/
		$sql11 = mysqli_query($enlace_base,$inser_log);								
										
}

function graba_log_carga($archivo,$ini_carga,$fin_carga,$cont_error,$count_total,$count_s00,$count_s99,
						$count_json,$count_99_1,$count_99_N_1,$count_sin_nor,$count_nor_sec,$count_nor_sinsec,
						$count_s00_sinsec,$count_s00_cambsec,$count_s00_mansec,$enlace_base){
		
		$inser_log = "INSERT INTO TABLOG_CARGA (NOM_ARCHIVO,FEC_INI_PROCESO,FEC_FIN_PROCESO,TOTAL_REG,TOTAL_99,TOTAL_99_1,TOTAL_99_N_1,TOTAL_00,TOTAL_JSON,TOT99_SINRESP,TOT99_NOR_SINSEC,TOT99_NOR_SEC,TOT00_SINSEC,TOT00_CAMB_SEC,TOT00_MANT,TOT_ERROR) ";
		$inser_log.= " VALUES('$archivo','$ini_carga','$fin_carga','$count_total',";
		$inser_log.= "'$count_s99','$count_99_1','$count_99_N_1','$count_s00','$count_json','$count_sin_nor','$count_nor_sinsec','$count_nor_sec','$count_s00_sinsec',";
		$inser_log.= "'$count_s00_cambsec','$count_s00_mansec','$cont_error')";
		ECHO $inser_log;
		$P44= strtoupper(trim(fgets(STDIN,256)));
		$sql11 = mysqli_query($enlace_base,$inser_log);								
										
}

function parametros_entrada($tipo,$desc_par,$enlace_base){
	
	$param='';
	
	$consulta = "SELECT VALOR_PARAMETRO FROM TAPARAMETVAL 
			WHERE COD_PARAMETRO = '$tipo' AND DESC_PARAMETRO='$desc_par' ";
	//ECHO $consulta;
	$sql11 = mysqli_query($enlace_base,$consulta);	
	$rowP = mysqli_fetch_array($sql11);
	$param = $rowP[0];

	return $param;
}
function consulta_sector(&$SECTOR,$latitud,$longitud,&$valida_dir,$conection){
	
	$sector_v = '';
	$consulta = "SELECT DESCRIPCIO,ASTEXT(geom),CONTAINS(GEOMFROMTEXT(geom_txt),GEOMFROMTEXT('POINT($longitud $latitud)')) p FROM TABSECTORES 
					HAVING P=1; ";
					
					//ECHO "PASO: ".$consulta;$P= strtoupper(trim(fgets(STDIN,256)));
					
	$sql = mysqli_query($conection,$consulta);
	$row = mysqli_fetch_array($sql);
	
	$sector_v = $row[0];
	$valida_v = $row[2];
	
	if($valida_dir == 2 and $SECTOR != $sector_v){
		
		$valida_dir	= 3;		
	}
	
	return $sector_v;
}
function Conectar(){

	// PARAMETRIZACION DATOS DE CONEXION A BASE DE DATOS

	$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "normalizacion_beco_web";
		
	/*$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "normalizacion_beco_web";*/		
	
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
function extraer_caracteres_reservados($cadena){
	//Expresion regular
  	$pattern='[\\*|\'*|\"*|\$*|\"*|\'*]';
  	//"Se reemplazan por 1 espacio
  	$cadena_sin=ereg_replace($pattern,' ', $cadena);
  	return $cadena_sin;
}
/******************************************************************************/
function caracter_reservado($sBeginWord)
{
  $sNewWord=str_replace("'","\'",$sBeginWord);
  $sNewWord=str_replace("¤","ñ",$sNewWord);
  $sNewWord=str_replace("à","O",$sNewWord);
  $sNewWord=str_replace("¥","Ñ",$sNewWord);
  $sNewWord=str_replace("ù","\'",$sNewWord);
  $sNewWord=str_replace("Ý","°",$sNewWord);
  $sNewWord=str_replace("Ö","I",$sNewWord);
 

  return $sNewWord;
}

function crear_informe_beco($archivo,$fuentes,$conection){
	
	/*$s_archivo_alta ="SALIDA/ALTAS_".date('Ymd').".CSV";
	$archivo_alta=fopen ( $s_archivo_alta,"w");
	
	$s_archivo_baja ="SALIDA/BAJAS_".date('Ymd').".CSV";
	$archivo_baja=fopen ( $s_archivo_baja,"w");
	
	$s_archivo_99 ="SALIDA/PER_99".date('Ymd').".CSV";
	$archivo_99=fopen ( $s_archivo_99,"w");*/
	
	$aarchivo = explode('.',$archivo);
	
	$s_archivo_alta ="SALIDA/UMBMD_DIR_NOR_".date('Ymd')."_".$aarchivo[0].".TXT";
	$archivo_alta=fopen ( $s_archivo_alta,"w");
	
	$s_archivo_baja ="SALIDA/UMBMD_INA_NOR_".date('Ymd')."_".$aarchivo[0].".TXT";
	$archivo_baja=fopen ( $s_archivo_baja,"w");
	

	$s_archivo_99 ="SALIDA/PER99_".$aarchivo[0].".XLS";
	$archivo_99=fopen ( $s_archivo_99,"w");


	$fecha_hoy1			= date('Ymd');
	$usuario				= parametros_entrada('1','USUARIO',$conection);
	//$usuario			= $avalor[0][0];
	

	$consulta = "SELECT * FROM TABRESPMAP A,TABDIR_NOR B 
			WHERE A.ID_DIRECCION=B.ID_DIRECCION AND A.RUT_CLIENTE=B.RUT_CLIENTE
			AND A.NOM_ARCHIVO='$archivo'
			AND B.NOM_ARCHIVO='$archivo'
			and NUM_SECTOR = 99 AND VALIDA_DIR=1 ";
			
			//echo $consulta;$P44= strtoupper(trim(fgets(STDIN,256)));
	$sql = mysqli_query($conection,$consulta);
	
	$cont = 0;
	
	while($rowP = mysqli_fetch_array($sql)){
		
		echo $cont++;
		echo chr(13);
		
		$arut		= explode('-',$rowP['RUT_CLIENTE']);
		$rut		= intval($arut[0]);
		$dv			= $arut[1];
		
/*echo caracter_espacio(trim($rowP['CALLE']).$rowP['NUMERO'] ) .'----'. caracter_espacio( trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] ).'---'.$rowP['SECTOR'];
$P44= strtoupper(trim(fgets(STDIN,256)));*/
		
		if( caracter_espacio(trim($rowP['CALLE']).$rowP['NUMERO'] ) != caracter_espacio( trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] ) ){
			
			//echo "paso 11";$P44= strtoupper(trim(fgets(STDIN,256)));
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
			$RUT_CL				= PIC('9',9,trim($rut));
			

			$cod_region_N		= SACA_COD_REGION(trim($rowP['COD_CIUDAD']),$conection);
			
			$INPDM_CLAVE		= $RUT_CL.$dv.$cod_comuna_R.$numero_R.$calle_R;
			$INPDM_CLAVE		= PIC('X',45,caracter_espacio(trim($INPDM_CLAVE)));	//1 clave ident domi rut+cod_comu+nro+calle trun 45
			
			$INPDM_PER_CLAVE	= PIC('X',45,trim($RUT_CL.$dv));			//2 clave identificadora,rut sin guiones
			$INPDM_DEFAULT		= PIC('X',1,'N');							//3 marca de domicilio
			
			if(trim($rowP['TIPO']) == 'S/I'){$tipo_dir = 1;}
			else{$tipo_dir = trim($rowP['TIPO']);}
			
			$INPDM_DTI			= PIC('X',10,$tipo_dir);					//4 tipo de domicilio
			$INPDM_CALLE		= PIC('X',50,trim($rowP['CALLE']));			//5 calle
			$INPDM_NUMERO		= PIC('X',20,trim($rowP['NUMERO']));		//6 numero
			$INPDM_PISO			= PIC('X',20,trim($rowP['PISO']));			//7 piso
			$INPDM_DPTO			= PIC('X',6,trim($rowP['DEPTO']));			//8 departamento
			$INPDM_LOC_TEXTO	= PIC('X',30,trim($rowP['POB_VILLA']));		//9 comuna
			$INPDM_LOC			= PIC('X',10,trim($rowP['COD_COMUNA']));	//10 codigo comuna segun maestro comuna
			$INPDM_PRV			= PIC('X',10,trim($rowP['COD_CIUDAD']));	//11 ciudad
			$INPDM_COP			= PIC('X',10,'S/I');						//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('9',10,$cod_region_N);				//14 codigo segun maestro region
			$INPDM_FUENTE		= PIC('X',30,'BECO');						//15 fuente origen del dato
			$INPDM_ESTADO		= PIC('X',10,'INA');						//16 estado del domicilio
			$INPDM_GEOREF1		= PIC('X',100,trim($rowP['LATITUD']));		//17 latitud
			$INPDM_GEOREF2		= PIC('X',100,trim($rowP['LONGITUD']));		//18 longitud
			$INPDM_GEOREF3		= PIC('X',100,trim($rowP['SECTOR']));		//19 sector
			$INPDM_GEOREF4		= PIC('X',100,'');							//20 parametro georeferencia
			$INPDM_ESTADO_NORM	= PIC('X',30,'NORMALIZADO');				//21 estado de normalizacion
			
			$INPDM_FECHA_NORM	= PIC('X',8,$fecha_hoy1);					//22 fecha de normalizacion
			
			$INPDM_FUENTE_NORM	= PIC('X',100,$fuentes);					//23 detalle de fuente normalizacion
			
			$observacion		= $rowP['OBSER1'].' '.$fecha_hoy1.' NORM INA CAMBIO LLAVE';
			$INPDM_OBS			= PIC('X',255,trim($observacion));			//24 observaciones
		
			$USUARIO			= PIC('X',10,trim($usuario));				//usuario modificacion(1-10);
		
			$INPDM_FILLER		= PIC('X',245,'');							//25 usuario modificacion(1-10);marca dir(11-15);datos complementarios(16-116)
			$INPDM_ERROR		= PIC('X',10,'');							//26 blancos
			$INPDM_ERROR_CAMPO	= PIC('X',30,'');							//27 blancos	
			
			
			$s_buffer =	$INPDM_CLAVE.$INPDM_PER_CLAVE.$INPDM_DEFAULT.$INPDM_DTI.$INPDM_CALLE.$INPDM_NUMERO.$INPDM_PISO;
			$s_buffer.=	$INPDM_DPTO.$INPDM_LOC_TEXTO.$INPDM_LOC.$INPDM_PRV.$INPDM_COP.$INPDM_COD_POSTAL.$INPDM_PAI;
			$s_buffer.=	$INPDM_FUENTE.$INPDM_ESTADO.$INPDM_GEOREF1.$INPDM_GEOREF2.$INPDM_GEOREF3.$INPDM_GEOREF4.$INPDM_ESTADO_NORM;
			$s_buffer.=	$INPDM_FECHA_NORM.$INPDM_FUENTE_NORM.$INPDM_OBS.$USUARIO.$INPDM_FILLER.$INPDM_ERROR.$INPDM_ERROR_CAMPO;
			fwrite($archivo_baja,$s_buffer."\r\n");
			
		}
		elseif( caracter_espacio(trim($rowP['CALLE']).$rowP['NUMERO'] ) == caracter_espacio( trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] ) ){
			
			//echo "paso 22";$P44= strtoupper(trim(fgets(STDIN,256)));
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
			
			$cod_region_N		= SACA_COD_REGION(trim($rowP['COD_CIUDAD']),$conection);
			
			$RUT_CL				= PIC('9',9,trim($rut));		
			
			$INPDM_CLAVE		= $RUT_CL.$dv.$cod_comuna_R.$numero_R.$calle_R;
			$INPDM_CLAVE		= PIC('X',45,trim($INPDM_CLAVE));			//1 clave ident domi rut+cod_comu+nro+calle trun 45
			
			$INPDM_PER_CLAVE	= PIC('X',45,trim($RUT_CL.$dv));			//2 clave identificadora,rut sin guiones
			$INPDM_DEFAULT		= PIC('X',1,'N');							//3 marca de domicilio
			
			if(trim($rowP['TIPO']) == 'S/I'){$tipo_dir = 1;}
			else{$tipo_dir = trim($rowP['TIPO']);}
			
			$INPDM_DTI			= PIC('X',10,$tipo_dir);					//4 tipo de domicilio
			$INPDM_CALLE		= PIC('X',50,trim($rowP['CALLE']));			//5 calle
			$INPDM_NUMERO		= PIC('X',20,trim($rowP['NUMERO']));		//6 numero
			$INPDM_PISO			= PIC('X',20,trim($rowP['PISO']));			//7 piso
			$INPDM_DPTO			= PIC('X',6,trim($rowP['DEPTO']));			//8 departamento
			$INPDM_LOC_TEXTO	= PIC('X',30,trim($rowP['POB_VILLA']));		//9 comuna
			$INPDM_LOC			= PIC('X',10,trim($rowP['COD_COMUNA']));	//10 codigo comuna segun maestro comuna
			$INPDM_PRV			= PIC('X',10,trim($rowP['COD_CIUDAD']));	//11 ciudad
			$INPDM_COP			= PIC('X',10,'S/I');						//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('9',10,$cod_region_N);				//14 codigo segun maestro region
			$INPDM_FUENTE		= PIC('X',30,'BECO');						//15 fuente origen del dato
			$INPDM_ESTADO		= PIC('X',10,'INA');						//16 estado del domicilio
			$INPDM_GEOREF1		= PIC('X',100,trim($rowP['LATITUD']));		//17 latitud
			$INPDM_GEOREF2		= PIC('X',100,trim($rowP['LONGITUD']));		//18 longitud
			$INPDM_GEOREF3		= PIC('X',100,trim($rowP['SECTOR']));		//19 sector
			$INPDM_GEOREF4		= PIC('X',100,'');							//20 parametro georeferencia
			$INPDM_ESTADO_NORM	= PIC('X',30,'NORMALIZADO');				//21 estado de normalizacion
			
			$INPDM_FECHA_NORM	= PIC('X',8,$fecha_hoy1);					//22 fecha de normalizacion
			
			$INPDM_FUENTE_NORM	= PIC('X',100,$fuentes);					//23 detalle de fuente normalizacion
			
			$observacion		= $rowP['OBSER1'].' '.$fecha_hoy1.' NORM';
			$INPDM_OBS			= PIC('X',255,trim($observacion));			//24 observaciones
		
			$USUARIO			= PIC('X',10,trim($usuario));				//usuario modificacion(1-10);
		
			$INPDM_FILLER		= PIC('X',245,'');							//25 usuario modificacion(1-10);marca dir(11-15);datos complementarios(16-116)
			$INPDM_ERROR		= PIC('X',10,'');							//26 blancos
			$INPDM_ERROR_CAMPO	= PIC('X',30,'');							//27 blancos
			
			$s_buffer =	$INPDM_CLAVE.$INPDM_PER_CLAVE.$INPDM_DEFAULT.$INPDM_DTI.$INPDM_CALLE.$INPDM_NUMERO.$INPDM_PISO;
			$s_buffer.=	$INPDM_DPTO.$INPDM_LOC_TEXTO.$INPDM_LOC.$INPDM_PRV.$INPDM_COP.$INPDM_COD_POSTAL.$INPDM_PAI;
			$s_buffer.=	$INPDM_FUENTE.$INPDM_ESTADO.$INPDM_GEOREF1.$INPDM_GEOREF2.$INPDM_GEOREF3.$INPDM_GEOREF4.$INPDM_ESTADO_NORM;
			$s_buffer.=	$INPDM_FECHA_NORM.$INPDM_FUENTE_NORM.$INPDM_OBS.$USUARIO.$INPDM_FILLER.$INPDM_ERROR.$INPDM_ERROR_CAMPO;
			
			fwrite($archivo_baja,$s_buffer."\r\n");
			
		}
		
		/*$cod_comuna_N		= SACA_COD_COMUNA($rowP['COMUNA_LARGO'],$conection);
		$cod_ciudad_N		= SACA_COD_CIUDAD($rowP['COMUNA_LARGO'],$conection);
		$cod_region_N		= SACA_COD_REGION($rowP['COMUNA_LARGO'],$conection);*/
		
		$cod_ciudad = $cod_region = '';
		
		$cod_comuna_N		= SACA_COD_COMUNA($rowP['COMUNA_LARGO'],$cod_ciudad,$conection);
		$cod_ciudad_N		= $cod_ciudad;
		$cod_region_N		= SACA_COD_REGION($cod_ciudad,$conection);

		$numero_N			= trim($rowP['ALTURA']);
		$calle_N			= trim($rowP['NOMBRE_VIA_LARGO']);
		
		$RUT_CL				= PIC('9',9,trim($rut));
		
		$INPDM_CLAVE 		= $RUT_CL.$dv.$cod_comuna_N.$numero_N.$calle_N;

		$INPDM_CLAVE		= PIC('X',45,caracter_espacio(trim($INPDM_CLAVE)));		//1 clave ident domi rut+cod_comu+nro+calle trun 45

		$INPDM_PER_CLAVE	= PIC('X',45,$RUT_CL.$dv);			//2 clave identificadora,rut sin guiones
		$INPDM_DEFAULT		= PIC('X',1,'S');						//3 marca de domicilio
		
		if(trim($rowP['TIPO']) == 'S/I'){$tipo_dir = 1;}
		else{$tipo_dir = trim($rowP['TIPO']);}
			
		$INPDM_DTI			= PIC('X',10,$tipo_dir);				//4 tipo de domicilio
		$INPDM_CALLE		= PIC('X',50,$calle_N);					//5 calle
		$INPDM_NUMERO		= PIC('X',20,trim($rowP['ALTURA']));	//6 numero
		$INPDM_PISO			= PIC('X',20,trim($rowP['PISO']));		//7 piso
		$INPDM_DPTO			= PIC('X',6,trim($rowP['DEPTO']));		//8 departamento
		$INPDM_LOC_TEXTO	= PIC('X',30,trim($rowP['COMUNA_LARGO']));	//9 comuna TEXTO
		$INPDM_LOC			= PIC('X',10,$cod_comuna_N);			//10 codigo comuna segun maestro comuna
		$INPDM_PRV			= PIC('X',10,$cod_ciudad_N);			//11 ciudad
		$INPDM_COP			= PIC('X',10,'S/I');					//12 codigo postal
		$INPDM_COD_POSTAL	= PIC('X',50,'');						//13 codigo postal en texto
		$INPDM_PAI			= PIC('9',10,$cod_region_N);			//14 codigo segun maestro region
		$INPDM_FUENTE		= PIC('X',30,'BECO');					//15 fuente origen del dato
		$INPDM_ESTADO		= PIC('X',10,'VCT');					//16 estado del domicilio
		$INPDM_GEOREF1		= PIC('X',100,trim($rowP['LAT']));		//17 latitud
		$INPDM_GEOREF2		= PIC('X',100,trim($rowP['LNG']));		//18 longitud
		$INPDM_GEOREF3		= PIC('X',100,trim($rowP['NEW_SECTOR']));//19 sector
		$INPDM_GEOREF4		= PIC('X',100,'');						//20 parametro georeferencia
		$INPDM_ESTADO_NORM	= PIC('X',30,'NORMALIZADO');			//21 estado de normalizacion
		
		$INPDM_FECHA_NORM	= PIC('X',8,$fecha_hoy1);				//22 fecha de normalizacion
		
		$INPDM_FUENTE_NORM	= PIC('X',100,$fuentes);				//23 detalle de fuente normalizacion
		
		$observacion		= $rowP['OBSER1'].' '.$fecha_hoy1.' NUEVA NORM CAMBIO LLAVE';
		$INPDM_OBS			= PIC('X',255,trim($observacion));		//24 observaciones
	
		$USUARIO			= PIC('X',10,trim($usuario));			//usuario modificacion(1-10);
	
		$INPDM_FILLER		= PIC('X',245,'');						//25 usuario modificacion(1-10);marca dir(11-15);datos complementarios(16-116)
		$INPDM_ERROR		= PIC('X',10,'');						//26 blancos
		$INPDM_ERROR_CAMPO	= PIC('X',30,'');						//27 blancos
		
		$s_buffer =	$INPDM_CLAVE.$INPDM_PER_CLAVE.$INPDM_DEFAULT.$INPDM_DTI.$INPDM_CALLE.$INPDM_NUMERO.$INPDM_PISO;
		$s_buffer.=	$INPDM_DPTO.$INPDM_LOC_TEXTO.$INPDM_LOC.$INPDM_PRV.$INPDM_COP.$INPDM_COD_POSTAL.$INPDM_PAI;
		$s_buffer.=	$INPDM_FUENTE.$INPDM_ESTADO.$INPDM_GEOREF1.$INPDM_GEOREF2.$INPDM_GEOREF3.$INPDM_GEOREF4.$INPDM_ESTADO_NORM;
		$s_buffer.=	$INPDM_FECHA_NORM.$INPDM_FUENTE_NORM.$INPDM_OBS.$USUARIO.$INPDM_FILLER.$INPDM_ERROR.$INPDM_ERROR_CAMPO;
		//echo $s_buffer;$P44= strtoupper(trim(fgets(STDIN,256)));
		fwrite($archivo_alta,$s_buffer."\r\n");
	}
	
	$consulta = "SELECT * FROM TABDIR_NOR WHERE NOM_ARCHIVO='$archivo'
			AND NUM_SECTOR = 99 AND VALIDA_DIR=0 AND DIR_ASIGNACION=1 ";
	$sql = mysqli_query($conection,$consulta);
	
	$sSep = chr(9);
	$marca_def=$cod_region=$desc_region=$DESC_CIUDAD='';
	
	while($rowP = mysqli_fetch_array($sql)){
	
	echo $cont++;
	echo chr(13);
	
	$estado = '';
	$estado = saca_estado_xygo($rowP['ID_DIRECCION'], $archivo, $conection );
	
	$buffer = $usuario.$sSep.$rowP['RUT_CLIENTE'].$sSep.$marca_def.$sSep.$rowP['TIPO'].$sSep.$rowP['CALLE'].$sSep.$rowP['NUMERO'].$sSep;
	$buffer.= $rowP['COD_COMUNA'].$sSep.$rowP['POB_VILLA'].$sSep.$rowP['COD_CIUDAD'].$sSep.$DESC_CIUDAD.$sSep;
	$buffer.= $cod_region.$sSep.$desc_region.$sSep.$rowP['LATITUD'].$sSep.$rowP['LONGITUD'].$sSep.$rowP['SECTOR'].$sSep.$estado;
	fwrite($archivo_99,$buffer."\r\n");
	}		
	
}	

function PIC($sTipo,$nLargo_definicion,$sValor,$alineacion='L'){
  	$nLargo_valor = strlen(trim($sValor));
  	if ($nLargo_valor < $nLargo_definicion)
    { 
  		if ($sTipo == 'X')
      {
    		//Tipo CHAR rellena con espacios a la derecha
        if ($alineacion=='L')
        {
        	$sValor = $sValor.str_repeat(' ',$nLargo_definicion - $nLargo_valor);
       	}
        //Tipo CHAR rellena con espacios a la izquierda
        else if ($alineacion=='R')
        {
        	$sValor = str_repeat(' ',$nLargo_definicion - $nLargo_valor).$sValor;
        }
  		}
      else if ($sTipo == '9')
  		{
      	//Si es valor entero rellena con 0 a la izquierda
        $sValor = str_repeat('0',$nLargo_definicion - $nLargo_valor).$sValor;
      }
  	}
    else
    {
    	$sValor = substr($sValor,0,$nLargo_definicion);//Trunca al largo de campo al largo definido
    }
  	return $sValor;
  }
  
function buscaLugar($calle,$numero,&$direccion,$comuna) {

		$idPais = "56";
                              
        $direccion = $calle."".$numero.",".$comuna;
        // $direccion = $calle." ".$numero.", ".$comuna;
                              
        libxml_use_internal_errors(true);
                                                              
        $wsdl="http://demos.xygo.com/OPENGETS_WS/Metodos.asmx?WSDL";
                              
        $client = new nusoap_client($wsdl,'wsdl');
                              
        $param=array('idPais'=>$idPais, 'direccion' => $direccion);
        $resultado = $client->call('FindDireccionConcatenada', $param);
        //$resultado = $client->FindDireccionConcatenada($param);

        $response = simplexml_load_string($resultado['FindDireccionConcatenadaResult']);

        //print_r($response);
        //$P= strtoupper(trim(fgets(STDIN,256)));
		   
        if ($client->fault) { // si
                $error = $client->getError();
        if ($error) { // Hubo algun error
               
                        echo 'Error:  ' . $client->faultstring;
                                                              
                    }
                                              
                        die();
            }
        return $response;   

}

function buscaLugarGoogle1($calle,$numero,$comuna)
{
	$keyGoogle = 'AIzaSyCco9YY_9Z9wea7vkHbUiCkw03w-4JFRqM';
	
	/*Limpiar los espacios y los reemplazamos por un + para concatenar*/
	$calleLimpia = str_replace(' ','+', $calle);
	$comunaLimpia = str_replace(' ', '+', $comuna);

	/*Generamos la cadena completa de la direccion para enviar a procesar*/

	$address = $calleLimpia.$numero.','.$comunaLimpia;
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&components=country:CL&key=' . $keyGoogle;

	/*Obtenemos la respuesta en Formato JSON y la combertimos a un arreglo para manipularla*/

	$resp_json = file_get_contents($url);
	$response  = json_decode($url, TRUE);

	$lotactionType = $lotactionType = $response['results'][0]['geometry']['location_type'];

	/*Validamos que el tipo de ubicacion no sea aproximada y que tengamos resultado*/
	
	if($lotactionType != 'APPROXIMATE' AND $response['status'] === 'OK')
	{
		echo '<pre>';
		print_r($response);
		echo '</pre>';
		$P= strtoupper(trim(fgets(STDIN,256)));
	}
	else{
		echo 'no se puede normalizar por Google';
	}
}

function buscaLugarGoogle($calle,$numero,&$direccion,$comuna)
{
		$keyGoogle = 'AIzaSyCco9YY_9Z9wea7vkHbUiCkw03w-4JFRqM';
		
		 $direccion = $calle."".$numero.",".$comuna;
		 
		/*Limpiar los espacios y los reemplazamos por un + para concatenar*/
		$calleLimpia = str_replace(' ','+', $calle);
		$comunaLimpia = str_replace(' ', '+', $comuna);

		/*Generamos la cadena completa de la direccion para enviar a procesar*/

		$address = $calleLimpia.$numero.','.$comunaLimpia;
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&components=country:CL&key=' . $keyGoogle;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		
		$response  = json_decode($response);

		/*print_r($response);
		$P= strtoupper(trim(fgets(STDIN,256)));*/
		
		return $response;
		
}

function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}

function saca_fecha_archivo($archivo){
	
		$fecha_archivo = '';
		$afecha = explode('_',$archivo);
		$cuenta_fec = count($afecha)-1;
		$aafecha = explode('.',$afecha[$cuenta_fec]);
		$fecha_archivo = substr($aafecha[0],0,4)."-".substr($aafecha[0],4,2)."-".substr($aafecha[0],6,2);

		//ECHO "tipo_archivo: $fecha_archivo";$P= strtoupper(trim(fgets(STDIN,256)));
		
		return $fecha_archivo;
}

function saca_estado_xygo($id_direccion,$archivo,$enlace_base ){
	
	$descrip = '';
	$consulta	= "SELECT DESCRIP FROM TABESTADO_XYGO A,TABXYGO B  WHERE A.BUZON_G = B.BUZON_G 
					AND NOM_ARCHIVO = '$archivo' AND ID_DIR = '$id_direccion'";
			
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$descrip = $rowc[0];
	return $descrip;
}
function SACA_COD_COMUNA($comuna, &$cod_ciudad, $enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_REGISTRO,COD_RELACIONADO FROM TABCODBECO WHERE TIPO_REGISTRO=5 AND DESC_REGISTRO='$comuna'";
	//echo $consulta;exit;
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];
	$cod_ciudad = $rowc[1];
	return $cod_comu;
	
 }
function SACA_COD_REGION($ciudad,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_RELACIONADO FROM TABCODBECO WHERE TIPO_REGISTRO=16 AND COD_REGISTRO='$ciudad'";
	//echo $consulta;exit;
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];

	return $cod_comu;
	
 }
 
function VALIDA_ENVIO($id_dir,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COUNT(*) FROM TABRESPMAP WHERE ID_DIRECCION = '$id_dir' AND 
	CONCAT(YEAR(FECHA_PROCESO),MONTH(FECHA_PROCESO))= CONCAT(YEAR(CURDATE()),MONTH(CURDATE()))";
	//echo $consulta;
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$exite_dir = $rowc[0];

	return $exite_dir;
	
 }
 
function caracter_espacio($sBeginWord){
  $sNewWord=str_replace(" ","",$sBeginWord);
  return $sNewWord;
}

function manda_xygo($CALLE,$NUMERO,$POB_VILLA,$RUT_CLIENTE,$ID_DIRECCION,&$SECTOR,&$valida_dir,$direccion,$archivo,&$sector_validado,$conection){
	
	$xml = buscaLugar($CALLE,$NUMERO,$direccion,$POB_VILLA);

	if(empty($xml)){
		$desc_error = "Enviado a Xygo, y no trae respuesta: (id_dir) ".$ID_DIRECCION;
		graba_errores($archivo,$desc_error,$conection)	;
		$opc_val = 1;								
		}
	else{
			foreach ($xml->xpath('//nombre_calle_salida_geo') as $NombreCalleSalida);
			foreach ($xml->xpath('//id_segmento') as $id_segmento);
			foreach ($xml->xpath('//longitud_depen') as $longitud_depen);
			foreach ($xml->xpath('//latitud_depen') as $latitud_depen);
			foreach ($xml->xpath('//oficina') as $oficina);
			foreach ($xml->xpath('//local') as $local_1);
			foreach ($xml->xpath('//piso') as $piso);
			foreach ($xml->xpath('//longitud_dire') as $longitud_dire);
			foreach ($xml->xpath('//latitud_dire') as $latitud_dire);
			foreach ($xml->xpath('//longitud_dica') as $longitud_dica);
			foreach ($xml->xpath('//latitud_dica') as $latitud_dica);
			foreach ($xml->xpath('//buzon_g') as $buzon_g);
			foreach ($xml->xpath('//buzon_v') as $buzon_v);
			foreach ($xml->xpath('//comuna_xygo') as $comuna_xygo);
			foreach ($xml->xpath('//det_cuadra') as $det_cuadra);
			foreach ($xml->xpath('//det_flagprocesocd') as $det_flagprocesocd);
			
			foreach ($xml->xpath('//det_candidatogeo') as $det_candidatogeo);
			foreach ($xml->xpath('//det_flagprocesour') as $det_flagprocesour);
			foreach ($xml->xpath('//urbanizacion') as $urbanizacion);
			foreach ($xml->xpath('//nombre_calle_entrada_pre') as $nombre_calle_entrada_pre);
			foreach ($xml->xpath('//det_direccionintermedia') as $det_direccionintermedia);
			foreach ($xml->xpath('//det_manzana') as $det_manzana);
			foreach ($xml->xpath('//det_lote') as $det_lote);
			foreach ($xml->xpath('//det_flagprocesolt') as $det_flagprocesolt);
			foreach ($xml->xpath('//det_flagprocesomz') as $det_flagprocesomz);
			foreach ($xml->xpath('//id_original') as $id_original);
			foreach ($xml->xpath('//cuadra') as $cuadra);
			foreach ($xml->xpath('//numero_municipal_entrada') as $numero_municipal_entrada);
			foreach ($xml->xpath('//nombre_calle_inter') as $nombre_calle_inter);
			foreach ($xml->xpath('//can') as $can);
			foreach ($xml->xpath('//cpn') as $cpn);
			foreach ($xml->xpath('//id_correlativo') as $id_correlativo);
			
			foreach ($xml->xpath('//nombre_calle_salida') as $nombre_calle_salida);
			foreach ($xml->xpath('//pais_id') as $pais_id);
			foreach ($xml->xpath('//coord_y') as $coord_y);
			foreach ($xml->xpath('//coord_x') as $coord_x);
			foreach ($xml->xpath('//lote') as $lote);
			foreach ($xml->xpath('//urbanizacion_norm') as $urbanizacion_norm);
			foreach ($xml->xpath('//manzana') as $manzana);
			foreach ($xml->xpath('//id_block') as $id_block);
			foreach ($xml->xpath('//estado') as $estado);
			foreach ($xml->xpath('//longitud_com') as $longitud_com);
			foreach ($xml->xpath('//latitud_com') as $latitud_com);
			foreach ($xml->xpath('//id_direccion') as $id_direccion);
			foreach ($xml->xpath('//complemento') as $complemento);
			foreach ($xml->xpath('//comuna_entrada') as $comuna_entrada);
			foreach ($xml->xpath('//numero_municipal') as $numero_municipal);
			foreach ($xml->xpath('//nombre_calle_entrada') as $nombre_calle_entrada);
			foreach ($xml->xpath('//buzon_n') as $buzon_n);
			foreach ($xml->xpath('//id_metodo') as $id_metodo);
			foreach ($xml->xpath('//id_lote') as $id_lote);
			foreach ($xml->xpath('//id_sec_cliente') as $id_sec_cliente);

			/*$consulta = "INSERT INTO TMP_USO_WS (ID, TMP_CALLE, TMP_NUMERO, TMP_COMUNA, FECHA_PROCESO, ESTADO, NOM_ARCHIVO) VALUES (NULL, '".$nombre_calle_entrada."', ".$numero_municipal.", '".$comuna_entrada."', NOW(), 'INSERT TABXYGO', '".$archivo."')";
			$result = mysqli_query($conection,$consulta);*/
			/*Guarda Respuesta XYGO INDEPENDIENTE SI TIENE EXITO O NO, SI TIENE X E y*/
			$SQL_insertar = "INSERT INTO TABXYGO (RUT_CLIENTE,ID_DIR,NombreCalleSalida,id_segmento,longitud_depen,latitud_depen,oficina,local_1,piso,longitud_dire,latitud_dire,";
			$SQL_insertar.= "longitud_dica,latitud_dica,buzon_g,buzon_v,comuna_xygo,det_cuadra,det_flagprocesocd,det_candidatogeo,det_flagprocesour,urbanizacion,";
			$SQL_insertar.= "nombre_calle_entrada_pre,det_direccionintermedia,det_manzana,det_lote,det_flagprocesolt,det_flagprocesomz,id_original,";
			$SQL_insertar.= "cuadra,numero_municipal_entrada,nombre_calle_inter,can,cpn,id_correlativo,nombre_calle_salida,pais_id,coord_y,coord_x,";
			$SQL_insertar.= "lote,urbanizacion_norm,manzana,id_block,estado,longitud_com,latitud_com,id_direccion,complemento,comuna_entrada,";
			$SQL_insertar.= "numero_municipal,nombre_calle_entrada,buzon_n,id_metodo,id_lote,id_sec_cliente,FECHA_PROCESO,NOM_ARCHIVO) ";
			$SQL_insertar.= "VALUES('".$RUT_CLIENTE."','".$ID_DIRECCION."','".$NombreCalleSalida."','".$id_segmento."','".$longitud_depen."','".$latitud_depen."','".$oficina."','".$local_1."','".$piso."','".$longitud_dire."','".$latitud_dire."','";
			$SQL_insertar.= $longitud_dica."','".$latitud_dica."','".$buzon_g."','".$buzon_v."','".$comuna_xygo."','".$det_cuadra."','".$det_flagprocesocd."','".$det_candidatogeo."','".$det_flagprocesour."','".$urbanizacion."','";
			$SQL_insertar.= $nombre_calle_entrada_pre."','".$det_direccionintermedia."','".$det_manzana."','".$det_lote."','".$det_flagprocesolt."','".$det_flagprocesomz."','".$id_original."','";
			$SQL_insertar.= $cuadra."','".$numero_municipal_entrada."','".$nombre_calle_inter."','".$can."','".$cpn."','".$id_correlativo."','".$nombre_calle_salida."','".$pais_id."','".$coord_y."','".$coord_x."','";
			$SQL_insertar.= $lote."','".$urbanizacion_norm."','".$manzana."','".$id_block."','".$estado."','".$longitud_com."','".$latitud_com."','".$id_direccion."','".$complemento."','".$comuna_entrada."','";
			$SQL_insertar.= $numero_municipal."','".$nombre_calle_entrada."','".$buzon_n."','".$id_metodo."','".$id_lote."','".$id_sec_cliente."',CURDATE(),'".$archivo."') ";
														
			//ECHO $SQL_insertar ;$P= strtoupper(trim(fgets(STDIN,256)));
			
			$result		= mysqli_query($conection,$SQL_insertar);									
			
			if( strlen($latitud_dire) > 5 ){
				
				/*$consulta = "INSERT INTO TMP_USO_WS (ID, TMP_CALLE, TMP_NUMERO, TMP_COMUNA, FECHA_PROCESO, ESTADO, NOM_ARCHIVO) VALUES (NULL, '".$nombre_calle_entrada."', ".$numero_municipal.", '".$comuna_entrada."', NOW(), 'INSERT TABRESPMAP', '".$archivo."')";
				$result = mysqli_query($conection,$consulta);*/									
				
				$comuna_corto		= $comuna_largo = $comuna_xygo;
				$nombre_via_largo	= $nombre_via_corto = '';
				$tipo_via_corto		= $tipo_via_largo = $nombre_calle_salida;							
				$anexo				= '';
				$provincia_corto	= $provincia_largo		= '';
				$region_corto		= $region_largo			= '';
				$pais_largo			= $pais_corto			= '';							
		
				
				$valida_dir = '1';
				
				//$count_json++;
				
				$SQL_insertar = "INSERT INTO TABRESPMAP (RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,";
				$SQL_insertar.= "NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,";
				$SQL_insertar.= "COMUNA_CORTO,COMUNA_LARGO,PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,";
				$SQL_insertar.= "PAIS_LARGO,PAIS_CORTO,LAT,LNG,FECHA_PROCESO,NOM_ARCHIVO,FUENTE) ";
				
				$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$ID_DIRECCION','$direccion','$nombre_calle_salida','$nombre_calle_salida','$nombre_calle_salida','$tipo_via_corto',";
				$SQL_insertar.= "'$tipo_via_largo','$estado','$numero_municipal','$anexo','$comuna_corto','$comuna_largo','$provincia_corto',";
				$SQL_insertar.= "'$provincia_largo','$region_corto','$region_largo','$pais_largo','$pais_corto','$latitud_dire','$longitud_dire',";
				$SQL_insertar.= "CURDATE(),'$archivo','XYGO')";
					
				
				//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
				
				$result		= mysqli_query($conection,$SQL_insertar);
				$sector_validado = consulta_sector($SECTOR,$latitud_dire,$longitud_dire,$valida_dir,$conection);
				$opc_val = 0;
			}
			else{$opc_val = 1;}									
	}
	
	return $opc_val;
}
									
									
function manda_google($CALLE,$NUMERO,$POB_VILLA,$RUT_CLIENTE,$ID_DIRECCION,&$SECTOR,&$valida_dir,$direccion,$archivo,&$sector_validado,$conection){

	$xml = buscaLugarGoogle($CALLE,$NUMERO,$direccion,$POB_VILLA);

	if(empty($xml)){

		$desc_error = "Enviado a Google, y no trae respuesta: (id_dir) ".$ID_DIRECCION;
		graba_errores($archivo,$desc_error,$conection)	;	
		$opc_val = 1;	
		}
	else{
		
			$lotactionType			= $xml->results[0]->geometry->location_type;						
			$status					= $xml->status;
				
			if(( $lotactionType != 'APPROXIMATE'  ) AND $status === 'OK')
			{
				
					$cuenta = count($xml->results[0]->address_components);
					
					$direccion_formateada=$nombre_via_corto=$nombre_via_largo=$tipo_via_corto='';
					$tipo_via_largo=$estado=$altura=$anexo=$comuna_corto=$comuna_largo=$provincia_corto='';
					$provincia_largo=$region_corto=$region_largo=$pais_largo=$pais_corto=$latitud=$longitud='';
					
					for($i=0;$i<$cuenta;$i++){
						
						
						if($xml->results[0]->address_components[$i]->types[0] == 'street_number'){
							
							$altura		= $xml->results[0]->address_components[$i]->short_name;
							$anexo		= $xml->results[0]->address_components[$i]->long_name;
						}
						
						elseif($xml->results[0]->address_components[$i]->types[0] == 'route'){
							
							$nombre_via_corto		= $xml->results[0]->address_components[$i]->short_name;
							$nombre_via_largo		= $xml->results[0]->address_components[$i]->long_name;
						}
						elseif($xml->results[0]->address_components[$i]->types[0] == 'locality'){
							
							$tipo_via_corto			= $xml->results[0]->address_components[$i]->short_name;
							$tipo_via_largo			= $xml->results[0]->address_components[$i]->short_name;
						}
						elseif($xml->results[0]->address_components[$i]->types[0] == 'administrative_area_level_3'){
							
							$comuna_corto			= $xml->results[0]->address_components[$i]->short_name;
							$comuna_largo			= $xml->results[0]->address_components[$i]->short_name;
						}
						elseif($xml->results[0]->address_components[$i]->types[0] == 'administrative_area_level_2'){
							
							$provincia_corto		= $xml->results[0]->address_components[$i]->short_name;
							$provincia_largo		= $xml->results[0]->address_components[$i]->short_name;
						}
						elseif($xml->results[0]->address_components[$i]->types[0] == 'administrative_area_level_1'){
							
							$region_corto			= $xml->results[0]->address_components[$i]->short_name;
							$region_largo			= $xml->results[0]->address_components[$i]->short_name;
						}
						elseif($xml->results[0]->address_components[$i]->types[0] == 'country'){
							
							$pais_largo				= $xml->results[0]->address_components[$i]->short_name;
							$pais_corto				= $xml->results[0]->address_components[$i]->short_name;
						}															
						
					}
					
					$latitud				= $xml->results[0]->geometry->location->lat;
					$longitud				= $xml->results[0]->geometry->location->lng;
					$direccion_formateada	= $xml->results[0]->formatted_address;
					$estado					= $lotactionType;
					
					//$count_json++;
						
					$valida_dir = '1';
					
					//$count_jsonG++;

					$SQL_insertar = "INSERT INTO TABRESPMAP (RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,";
					$SQL_insertar.= "NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,";
					$SQL_insertar.= "COMUNA_CORTO,COMUNA_LARGO,PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,";
					$SQL_insertar.= "PAIS_LARGO,PAIS_CORTO,LAT,LNG,FECHA_PROCESO,NOM_ARCHIVO,FUENTE) ";
					
					$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$ID_DIRECCION','$direccion','$direccion_formateada','$nombre_via_corto','$nombre_via_largo','$tipo_via_corto',";
					$SQL_insertar.= "'$tipo_via_largo','$estado','$altura','$anexo','$comuna_corto','$comuna_largo','$provincia_corto',";
					$SQL_insertar.= "'$provincia_largo','$region_corto','$region_largo','$pais_largo','$pais_corto','$latitud','$longitud',";
					$SQL_insertar.= "CURDATE(),'$archivo','GOOGLE')";

					//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
					
					$result		= mysqli_query($conection,$SQL_insertar);
					$sector_validado = consulta_sector($SECTOR,$latitud,$longitud,$valida_dir,$conection);
					$opc_val = 0;
			}
			else{$opc_val = 1;}	
		}

	return $opc_val;
}
?>