<?php

$conection = Conectar();

$avalor			= parametros_entrada('3','FUENTES',$conection);
$fuentes		= $avalor[0][0];

echo "\nProceso iniciado: ".date('d-M-Y H:i:s')."\n\n";

$archivoentrada = fopen ("JULIO_2017_GEO_01_18.csv", "r");

$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=$count_s00_sinsec=0;

$opc=0;

if($opc==0){
$strLinea 	= fgets($archivoentrada,1000);
echo $strLinea;
While (!feof($archivoentrada))
{
		echo $cont++;
		echo chr(13);

		$strLinea 	= fgets($archivoentrada,10000);
		$rowP	 	= explode(";",$strLinea);	
		if ($strLinea != ''){
		
			$id_direccion 			= $rowP[0];
			$direccion				= caracter_reservado_x(trim($rowP[1]." ".$rowP[2]." ".$rowP[3]." ".$rowP[4]));
			
			$comuna					= $rowP[5];
			$ciudad 				= $rowP[6];
			$region		 			= $rowP[7];
			
			$ID_XYGO	 			= $rowP[8];
			$ID_LOTE	 			= $rowP[9];
			$ID_METODO	 			= $rowP[10];
			$ID_BUZON	 			= $rowP[11];
			$NOMBRE_CALLE	 		= caracter_reservado_x($rowP[12]." ".$rowP[15]);
			$NUMERO_MUNICIPAL	 	= $rowP[13];
			$COMUNA	 				= $rowP[14];
			//$COMPLEMENTO	 		= $rowP[15];
			$ID_DIRECCION_x	 		= $rowP[16];
			$LAT_84	 				= caracter_reservado($rowP[17]);
			$LON_84	 				= caracter_reservado($rowP[18]);
			$ID_BLOCK	 			= $rowP[19];
			$CALLE_ESQUINA	 		= $rowP[20];
			
			$valida_dir = 0;
			
			if (strlen($LAT_84)>5){
			
				$valida_dir = 1;
				
				$Select1	= "SELECT RUT_CLIENTE,NOM_ARCHIVO,FECHA_PROCESO FROM TABDIR_NOR WHERE ID_DIRECCION = '$id_direccion' 
								AND DIR_ASIGNACION = 1 AND NUM_SECTOR=99 ORDER BY FECHA_ORIG_ARCH ASC LIMIT 1";
				$sqlc 		= mysqli_query($conection,$Select1);
				$row1 		= mysqli_fetch_array($sqlc);
				
				$RUT_CLIENTE		= $row1[0];
				$NOM_ARCHIVO		= $row1[1];
				$FECHA_PROCESO		= $row1[2];
				
				$fec_arch 			= saca_fecha_archivo($NOM_ARCHIVO);
				
				$SQL_insertar = "INSERT INTO TABRESPMAP (RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,";
				$SQL_insertar.= "NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,";
				$SQL_insertar.= "COMUNA_CORTO,COMUNA_LARGO,PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,";
				$SQL_insertar.= "PAIS_LARGO,PAIS_CORTO,LAT,LNG,FECHA_PROCESO,NOM_ARCHIVO,FUENTE) ";
									
				$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$id_direccion','$direccion','$NOMBRE_CALLE','','$NOMBRE_CALLE','',";
				$SQL_insertar.= "'','4','$NUMERO_MUNICIPAL','','$COMUNA','$COMUNA','',";
				$SQL_insertar.= "'','','','','','$LAT_84','$LON_84',";
				$SQL_insertar.= "'$FECHA_PROCESO','$NOM_ARCHIVO','XYGO')";
									
									//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
									
				$result			 = mysqli_query($conection,$SQL_insertar);
				$SECTOR = $sector_validado = '';
				$sector_validado = consulta_sector($SECTOR,$LAT_84,$LON_84,$valida_dir,$conection);
				
				if($sector_validado == '' AND $valida_dir == 1 ){
					//$count_nor_sinsec++;
					$valida_dir=4;
					}
				/*elseif($sector_validado != '' AND $valida_dir == 1 ){
					$count_nor_sec++;
					}*/
			
				$tipo_archivo = '';
				if(substr(strtoupper($NOM_ARCHIVO),0,7)=='INFOEMX'){$tipo_archivo = 'asignacion';}
				else{$tipo_archivo = 'on_demand';}
						
				$updat 		= "UPDATE TABDIR_NOR SET NEW_SECTOR='$sector_validado',VALIDA_DIR='$valida_dir' ,
							FECHA_ORIG_ARCH = '$fec_arch', TIPO_ARCHIVO='$tipo_archivo'
							WHERE ID_DIRECCION='$id_direccion' AND NOM_ARCHIVO ='$NOM_ARCHIVO' ";
				
				//ECHO "PASO: ".$updat;$P= strtoupper(trim(fgets(STDIN,256)));
				$result 	= mysqli_query($conection,$updat);
			}
			//ECHO "PASO 111 ";$P= strtoupper(trim(fgets(STDIN,256)));
		}
	}		

	/*$updat 		= "UPDATE TABLOG_CARGA SET TOT99_NOR_SINSEC='$count_nor_sinsec',
					 TOT99_NOR_SEC = '$count_nor_sec',TOTAL_JSON ='' WHERE ID_DIRECCION='$id_direccion' ";
	$result 	= mysqli_query($conection,$updat);*/
}
//ECHO "PASO 111 ";$P= strtoupper(trim(fgets(STDIN,256)));
$cont=0;
echo "\nProceso finalizado: ".date('d-M-Y H:i:s')."\n\n";

$consulta = "SELECT NOM_ARCHIVO,COUNT(*)
			FROM TABDIR_NOR GROUP BY 1 ";

$sql11 = mysqli_query($conection,$consulta);

while($row1 = mysqli_fetch_array($sql11)){

	$NOM_ARCHIVO		= $row1[0];
	$count_total		= $row1[1];
	
	//crear_informe_beco($NOM_ARCHIVO,$fuentes,$conection);
	
	$updat 	= "UPDATE TABDIR_NOR SET NEW_SECTOR='',VALIDA_DIR='' ,FECHA_ORIG_ARCH=''
						WHERE NOM_ARCHIVO ='$NOM_ARCHIVO' AND NUM_SECTOR != '99' ";
						//$P= strtoupper(trim(fgets(STDIN,256)));
	$result = mysqli_query($conection,$updat);
			
	$consulta = "SELECT RUT_CLIENTE,NOM_ARCHIVO,FECHA_PROCESO,LATITUD,LONGITUD,SECTOR,NUM_SECTOR,ID_DIRECCION 
				FROM TABDIR_NOR 
				WHERE  NOM_ARCHIVO ='$NOM_ARCHIVO' AND NUM_SECTOR!='99' ";
				
				//ECHO "PASO: ".$consulta;$P= strtoupper(trim(fgets(STDIN,256)));

		$sql112 = mysqli_query($conection,$consulta);
		
		while($row12 = mysqli_fetch_array($sql112)){
			
			echo "Procesando $NOM_ARCHIVO, total $count_total : ".$cont++;
			echo chr(13);
			
			$valida_dir = 2;
			
			$RUT_CLIENTE		= $row12[0];
			$NOM_ARCHIVO		= $row12[1];
			$FECHA_PROCESO		= $row12[2];
			$LAT_84				= caracter_reservado($row12[3]);
			$LON_84				= caracter_reservado($row12[4]);
			$SECTOR				= $row12[5];
			$nsector			= $row12[6];
			$id_direccion		= $row12[7];
			$count_s00++;
			
			$sector_validado 	= consulta_sector($SECTOR,$LAT_84,$LON_84,$valida_dir,$conection);
			
			$fec_arch = saca_fecha_archivo($NOM_ARCHIVO);
			
			$tipo_archivo = '';
			if(substr(strtoupper($NOM_ARCHIVO),0,7)=='INFOEMX'){$tipo_archivo = 'asignacion';}
			else{$tipo_archivo = 'on_demand';}
				
			if($sector_validado == '' and $nsector != '99' ){
				$count_s00_sinsec++;
				$valida_dir=5;
				}
			elseif($sector_validado != '' and $nsector != '99' and $valida_dir	== 3 ){
				$count_s00_cambsec++;
				}
			elseif($sector_validado != '' and $nsector != '99' and $valida_dir	== 2 ){
				$count_s00_mansec++;
				}

			$updat 		= "UPDATE TABDIR_NOR SET NEW_SECTOR='$sector_validado',VALIDA_DIR='$valida_dir' ,FECHA_ORIG_ARCH='$fec_arch', TIPO_ARCHIVO='$tipo_archivo'
						WHERE ID_DIRECCION='$id_direccion' AND NOM_ARCHIVO ='$NOM_ARCHIVO'  AND NUM_SECTOR != '99' ";
						
						//ECHO "PASO: ".$updat;$P= strtoupper(trim(fgets(STDIN,256)));
						
			$result 	= mysqli_query($conection,$updat);
			
		}
		echo "Procesando $NOM_ARCHIVO, total $count_total : ".$cont."\n";
		$FIN_CARGA = date('Y-m-d H:i:s');
		$cont_error = 0;
		
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
				FROM TABDIR_NOR A WHERE A.NOM_ARCHIVO IN ('$NOM_ARCHIVO') GROUP BY 1 ";
				
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
				
				graba_log_carga($NOM_ARCHIVO,$FECHA_PROCESO,$FECHA_PROCESO,
					$cont_error,
					$count_total,
					$count_s00,
					$count_s99,
					$count_json,
					$count_99_1,
					$count_99_N_1,
					$count_sin_nor,
					$count_nor_sec,
					$count_nor_sinsec,
					$count_s00_sinsec,
					$count_s00_cambsec,
					$count_s00_mansec,
					$conection);
				crear_informe_beco($NOM_ARCHIVO,$fuentes,$conection);

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

function graba_log_carga($archivo,
						$ini_carga,
						$fin_carga,
						$cont_error,
						$count_total,
						$count_s00,
						$count_s99,
						$count_json,
						$count_99_1,
						$count_99_N_1,
						$count_sin_nor,
						$count_nor_sec,
						$count_nor_sinsec,
						$count_s00_sinsec,
						$count_s00_cambsec,
						$count_s00_mansec,
						$enlace_base){
		
		$inser_log = "INSERT INTO TABLOG_CARGA VALUES('$archivo','$ini_carga','$fin_carga','$count_total',";
		$inser_log.= "'$count_s99','$count_99_1','$count_99_N_1','$count_s00','$count_json','$count_sin_nor','$count_nor_sinsec','$count_nor_sec','$count_s00_sinsec',";
		$inser_log.= "'$count_s00_cambsec','$count_s00_mansec','$cont_error')";
		/*ECHO $inser_log;
		$P44= strtoupper(trim(fgets(STDIN,256)));*/
		$sql11 = mysqli_query($enlace_base,$inser_log);								
										
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

function caracter_reservado($sBeginWord){
  $sNewWord=str_replace(",",".",$sBeginWord);
  return $sNewWord;
}

function caracter_reservado_x($sBeginWord){
  $sNewWord=str_replace("'","\'",$sBeginWord);
  return $sNewWord;
}

function Conectar(){
	// PARAMETRIZACION DATOS DE CONEXION A BASE DE DATOS
	$servername = "127.0.0.1";
	$username = "root";
	$password = "";
	$dbname = "norm_01_18";
	
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

function parametros_entrada($tipo,$desc_par,$enlace_base){
	
	$arr=array();
	
	$consulta = "SELECT VALOR_PARAMETRO FROM TAPARAMETVAL 
			WHERE COD_PARAMETRO = '$tipo' AND DESC_PARAMETRO='$desc_par' ";
	//ECHO $consulta;
	$sql11 = mysqli_query($enlace_base,$consulta);	
	while($rowP = mysqli_fetch_array($sql11)){
		$arr[]=$rowP;
	}
	return $arr;
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
/*
function SACA_COD_COMUNA($comuna,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_REGISTRO FROM TABCODBECO WHERE TIPO_REGISTRO=5 AND DESC_REGISTRO='$comuna'";
	//echo $consulta;exit;
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];
	return $cod_comu;
	
 }
function SACA_COD_REGION($ciudad,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_REGISTRO FROM TABCODBECO WHERE TIPO_REGISTRO=33 AND DESC_REGISTRO='$ciudad'";
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];
	return $cod_comu;
	
 }
function SACA_COD_CIUDAD($ciudad,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_REGISTRO FROM TABCODBECO WHERE TIPO_REGISTRO=16 AND DESC_REGISTRO='$ciudad'";
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];
	return $cod_comu;
	
 }*/

function buscaLugar($direccion,$url) {
		$direccion = str_replace(" ", "+", $direccion);
		//$url = "http://www.jamax.cl/ws/normalizardireccion.php?direccion=$direccion";
		//echo $url;exit;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
		
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

 function caracter_espacio($sBeginWord){
  $sNewWord=str_replace(" ","",$sBeginWord);
  return $sNewWord;
}
?>