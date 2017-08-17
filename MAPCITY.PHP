<?php

$conection = Conectar();

//echo "\nProceso iniciado: ".date('d-M-Y H:i:s')."\n\n";
$ruta_entrada	= "ENTRADA/";
$ruta_salida	= "SALIDA/";
$ruta_cargados	= "PROCESADOS/";
$ruta_errores	= "ERRORES/";

$s_archivo_baja ="SALIDA/AGOSTO_01_2017.CSV";
$archivo_baja=fopen ( $s_archivo_baja,"w");

$sSep = ";";

$buffer = "id_direccion".$sSep."calle".$sSep."piso".$sSep."depto".$sSep."numero".$sSep."comuna".$sSep;
$buffer.= "ciudad".$sSep."region";
fwrite($archivo_baja,$buffer."\n");
				
//echo "\nProceso iniciado: ".date('d-M-Y H:i:s')."\n\n";

if( is_dir($ruta_entrada) && $gDir = opendir($ruta_entrada) )
{
	$bValArch = false;
	while( ($archivo = readdir($gDir) ) !== false )
	{
		if ($archivo!= '.' AND $archivo!= '..'){
			
			$cont = $cont_cargados = $cont_error = 0;
			
			$archivoError 	= fopen ($ruta_errores.$archivo.".ERR","w");

			$select = "";
			$select = "SELECT FECHA_PROCESO,COUNT(*) FROM TABDIR_NOR WHERE NOM_ARCHIVO='".$archivo."' GROUP BY 1";
			
			$sql = mysqli_query($conection,$select);
			$row = mysqli_fetch_array($sql);

			if (intval($row[1]) > 0) {
				$buffer = "Archivo a Procesar: ".$archivo." fue cargado el dia ".$row[0]." con un total de ".$row[1]." registros \r\n";
				fwrite($archivoError,$buffer."\r\n");
				graba_errores($archivo,$buffer,$conection);
				//echo $buffer;
				$val_err = 1;
				//exit;
			}		
			else{
				
			
				$INI_CARGA = date('Y-m-d H:i:s');
				$tipo_archivo = '';
				if(substr(strtoupper($archivo),0,7)=='INFOEMX'){$tipo_archivo = 'asignacion';}
				else{$tipo_archivo = 'on_demand';}
				
				////echo "tipo_archivo: $tipo_archivo";$P= strtoupper(trim(fgets(STDIN,256)));
				
				$fec_arch = saca_fecha_archivo($archivo);
								
				$avalor			= parametros_entrada('2','URL',$conection);
				$SURL			= $avalor[0][0];
				
				$avalor			= parametros_entrada('3','FUENTES',$conection);
				$fuentes		= $avalor[0][0];
				
				$avalor			= parametros_entrada('4','TIPO_ENTRADA',$conection);
				$tipo_entrada	= $avalor[0][0];


				////echo "SURL: $SURL";$P= strtoupper(trim(fgets(STDIN,256)));
				$archivoentrada = fopen ($ruta_entrada.$archivo, "r");

				$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
				$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=0;
				$count_99_1=$count_99_N_1=0;
				$sector_validado = '';
				
				while(!feof($archivoentrada))			
				{
					////echo $cont++;
					//echo "Cargando registros de archivo $archivo en tabla TABDIR_NOR : ".$cont++;
					//echo chr(13);
					
					$count_total++;
											
					$strLinea 	= fgets($archivoentrada,1000);

					$aCampos 	= explode(";",$strLinea);			
					
					if(count($aCampos) >= 10){
						
						$n = 0;
							//echo "PASO: ".$strLinea;$P= strtoupper(trim(fgets(STDIN,256)));
						$RUT_CLIENTE 	= trim($aCampos[$n++]);
						$ID_DIRECCION 	= trim($aCampos[$n++]);
						$TIPO 			= trim($aCampos[$n++]);
						$CALLE 			= caracter_reservado(trim($aCampos[$n++]));
						$NUMERO 		= trim($aCampos[$n++]);
						$COD_COMUNA 	= caracter_reservado(trim($aCampos[$n++]));
						$BLOCK 			= trim($aCampos[$n++]);
						$PISO 			= trim($aCampos[$n++]);
						$DEPTO 			= trim($aCampos[$n++]);
						$POB_VILLA 		= caracter_reservado(trim($aCampos[$n++]));
						$COD_CIUDAD 	= caracter_reservado(trim($aCampos[$n++]));
						$ESTADO_DIR 	= trim($aCampos[$n++]);
						$OBSER1 		= caracter_reservado(trim($aCampos[$n++]));
						$DIR_ASIGNACION = trim($aCampos[$n++]);
						$SECTOR 		= trim($aCampos[$n++]);
						$DATOS_COMPLE 	= caracter_reservado(trim($aCampos[$n++]));
						$LATITUD 		= caracter_reservado_x(trim($aCampos[$n++]));
						$LONGITUD 		= caracter_reservado_x(trim($aCampos[$n++]));
						
						$aSECTOR	= explode('_',$SECTOR);
						$ind		= count($aSECTOR)-1;
						$nsector	= $aSECTOR[$ind];
						
						////echo "PASO: ".$SECTOR."--".$ind."--".$nsector;$P= strtoupper(trim(fgets(STDIN,256)));
						
						$valida_dir	= 0;
						
						$SQL_insertar = "INSERT INTO TABDIR_NOR (RUT_CLIENTE,ID_DIRECCION,TIPO,CALLE,NUMERO,COD_COMUNA,";
						$SQL_insertar.= "BLOCK,PISO,DEPTO,POB_VILLA,COD_CIUDAD,ESTADO_DIR,OBSER1,DIR_ASIGNACION,SECTOR,";
						$SQL_insertar.= "DATOS_COMPLE,LATITUD,LONGITUD,FECHA_PROCESO,HORA_PROCESO,NOM_ARCHIVO,NUM_SECTOR,VALIDA_DIR,NEW_SECTOR,TIPO_ARCHIVO,FECHA_ORIG_ARCH ) ";
						
						$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$ID_DIRECCION','$TIPO','$CALLE','$NUMERO','$COD_COMUNA',";
						$SQL_insertar.= "'$BLOCK','$PISO','$DEPTO','$POB_VILLA','$COD_CIUDAD','$ESTADO_DIR','$OBSER1','$DIR_ASIGNACION','$SECTOR',";
						$SQL_insertar.= "'$DATOS_COMPLE','$LATITUD','$LONGITUD',CURDATE(),CURTIME(),'$archivo','$nsector','$valida_dir','$sector_validado','$tipo_archivo','$fec_arch') ";
						
						////echo "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
						
						if ($result		= mysqli_query($conection,$SQL_insertar)){
							
								$cont_cargados++;
								
						}else{
						$cont_error++;
						$buffer = "reg: $cont.- query no pudo ser ejecutada:.".$SQL_insertar."\r\n";
						fwrite($archivoError,$buffer."\r\n");
						graba_errores($archivo,$buffer,$conection);
						}
					}else{
						$buffer = "reg: $cont.- registros en archivo no coinciden con estructura enviada.\r\n";
						fwrite($archivoError,$buffer."\r\n");
						graba_errores($archivo,$buffer,$conection);
						}
				}
				//echo "Cargando registros de archivo $archivo en tabla TABDIR_NOR : ".$cont."\n";
		
				mover_archivo($ruta_entrada.$archivo,$ruta_cargados.$archivo,$archivoError);				

				$consulta = "SELECT ID_DIRECCION,CALLE,PISO,DEPTO,NUMERO,COD_COMUNA,COD_CIUDAD 
				FROM TABDIR_NOR 
				WHERE NUM_SECTOR='99' AND DIR_ASIGNACION=1 AND NOM_ARCHIVO = '$archivo' GROUP BY 1,2,3,4,5,6,7 ";
				
				$sql = mysqli_query($conection,$consulta);

				$cont1 = 0;
				
				while($rowP = mysqli_fetch_array($sql)){
					
					//echo "Generando registros para archivo $archivo : ".$cont1++;
					//echo chr(13);

					$id_direccion 	= $rowP[0];
					$calle	 		= $rowP[1];
					$piso	 		= $rowP[2];
					$depto	 		= $rowP[3];
					$numero	 		= $rowP[4];
					$cod_comuna	 	= $rowP[5];
					$cod_ciudad	 	= $rowP[6];
					
					$Select1	= "SELECT DESC_REGISTRO FROM TABCODBECO WHERE COD_REGISTRO='$cod_comuna' AND TIPO_REGISTRO=5 LIMIT 1";
					$sqlc = mysqli_query($conection,$Select1);
					$row1 = mysqli_fetch_array($sqlc);
					
					$comuna		= $row1[0];
					
					$Select1	= "SELECT DESC_REGISTRO,COD_RELACIONADO FROM TABCODBECO WHERE COD_REGISTRO='$cod_ciudad' AND TIPO_REGISTRO=16 LIMIT 1";
					$sqlc = mysqli_query($conection,$Select1);
					$row1 = mysqli_fetch_array($sqlc);
					
					$ciudad		= $row1[0];
					$cod_rela	= $row1[1];
					
					$Select1	= "SELECT DESC_REGISTRO FROM TABCODBECO WHERE COD_REGISTRO='$cod_rela' AND TIPO_REGISTRO=33 LIMIT 1";

					$sqlc = mysqli_query($conection,$Select1);
					$row1 = mysqli_fetch_array($sqlc);
					$region		= $row1[0];
					
					
					$buffer = $id_direccion.$sSep.$calle.$sSep.$piso.$sSep.$depto.$sSep.$numero.$sSep.$comuna.$sSep;
					$buffer.= $ciudad.$sSep.$region;
					fwrite($archivo_baja,$buffer."\n");
				}
				//echo "Generando registros para archivo $archivo : ".$cont1."\n";
				
				//del_files_beco($ftp_server, $ftp_user_name, $ftp_user_pass, $server_file1, $server_file2); 
	
			}
		}
	}//WHILE ARCHIVOS
}	
//echo "\nProceso finalizado: ".date('d-M-Y H:i:s')."\n\n";

function caracter_reservado_x($sBeginWord)
{
  $sNewWord=str_replace(",",".",$sBeginWord);
  return $sNewWord;
}
function graba_errores($archivo,$desc_error,$enlace_base){
		
		$inser_log = "INSERT INTO TABERRORES VALUES('$archivo',CURDATE(),CURTIME(),'$desc_error')";
		/*//echo $inser_log;
		$P44= strtoupper(trim(fgets(STDIN,256)));*/
		$sql11 = mysqli_query($enlace_base,$inser_log);								
										
}
function saca_fecha_archivo($archivo){
	
		$fecha_archivo = '';
		$afecha = explode('_',$archivo);
		$cuenta_fec = count($afecha)-1;
		$aafecha = explode('.',$afecha[$cuenta_fec]);
		$fecha_archivo = substr($aafecha[0],0,4)."-".substr($aafecha[0],4,2)."-".substr($aafecha[0],6,2);

		////echo "tipo_archivo: $fecha_archivo";$P= strtoupper(trim(fgets(STDIN,256)));
		
		return $fecha_archivo;
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
		////echo 'Connection OK '.$conection;
	}
	
	return $conection;
}

function mover_archivo($archivo_ent,$archivo_salida,$archivoError){
	
	if (!copy($archivo_ent, $archivo_salida)) {
		
		$buffer = "Error al copiar $archivo_ent en directorio  $archivo_salida ...\n";
		fwrite($archivoError,$buffer."\r\n");
		graba_errores($archivo,$buffer,$conection);
	}
	else {
		if (!unlink ( $archivo_ent )){
			$buffer = "Error al borrar $archivo_ent ...\n";
			fwrite($archivoError,$buffer."\r\n");
			graba_errores($archivo,$buffer,$conection);
		}
	}
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
  return $sNewWord;
}

function parametros_entrada($tipo,$desc_par,$enlace_base){
	
	$arr=array();
	
	$consulta = "SELECT VALOR_PARAMETRO FROM TAPARAMETVAL 
			WHERE COD_PARAMETRO = '$tipo' AND DESC_PARAMETRO='$desc_par' ";
	////echo $consulta;
	$sql11 = mysqli_query($enlace_base,$consulta);	
	while($rowP = mysqli_fetch_array($sql11)){
		$arr[]=$rowP;
	}
	return $arr;
}
?>