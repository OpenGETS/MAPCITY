<?php
/*
include("funciones_sftp.php");


// definir algunas variables
$ftp_user_name = 'beco_prce';
$ftp_user_pass ='be.pr#2og';
$ftp_server ='172.16.7.7';

$local_file1 = '/prce/ENTRADA/ITF_CUP_PRCE_' . date('Ymd', strtotime("-1 days")) . '.txt';
$local_file2 = '/prce/ENTRADA/ITF_CLD_PAG_PRCE_' . date('Ymd',strtotime("-1 days")) . '.txt';

$server_file1 = '/INPUT/ITF_CUP_PRCE_' . date('Ymd',strtotime("-1 days")) . '.txt';
$server_file2 = '/INPUT/ITF_CLD_PAG_PRCE_' . date('Ymd',strtotime("-1 days")) . '.txt';

//ir a buscar la carga de hoy
get_files_beco($ftp_server, $ftp_user_name, $ftp_user_pass, $server_file1, $server_file2, $local_file1, $local_file2  );
*/
$conection = Conectar();

$ruta_entrada	= "ENTRADA/";
$ruta_salida	= "SALIDA/";
$ruta_cargados	= "PROCESADOS/";
$ruta_errores	= "ERRORES/";

echo "\nProceso iniciado: ".date('d-M-Y H:i:s')."\n\n";

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
				echo $buffer;
				$val_err = 1;
				//exit;
			}		
			else{
				
				$INI_CARGA = date('Y-m-d H:i:s');
				$tipo_archivo = '';
				if(substr(strtoupper($archivo),0,7)=='INFOEMX'){$tipo_archivo = 'asignacion';}
				else{$tipo_archivo = 'on_demand';}
				
				//ECHO "tipo_archivo: $tipo_archivo";$P= strtoupper(trim(fgets(STDIN,256)));
				
				$fec_arch = saca_fecha_archivo($archivo);
								
				$avalor			= parametros_entrada('2','URL',$conection);
				$SURL			= $avalor[0][0];
				
				$avalor			= parametros_entrada('3','FUENTES',$conection);
				$fuentes		= $avalor[0][0];
				
				$avalor			= parametros_entrada('4','TIPO_ENTRADA',$conection);
				$tipo_entrada	= $avalor[0][0];


				//ECHO "SURL: $SURL";$P= strtoupper(trim(fgets(STDIN,256)));
				$archivoentrada = fopen ($ruta_entrada.$archivo, "r");

				$cont=$cont_cargados=$cont_error=$count_total=$count_s00=$count_s99=$count_json=$count_sin_nor=0;
				$count_nor_sec=$count_nor_sinsec=$count_s00_sinsec=$count_s00_cambsec=$count_s00_mansec=0;
				$count_99_1=$count_99_N_1=0;
				$sector_validado = '';
				
				while(!feof($archivoentrada))			
				{
					echo $cont++;
					echo chr(13);
					
					$count_total++;
											
					$strLinea 	= fgets($archivoentrada,1000);

					$aCampos 	= explode(";",$strLinea);			
					
					if(count($aCampos) >= 10){
						
						$n = 0;
							
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
						
						$valida_dir	= 2;
						
						if($nsector == '99' ){$count_s99++;}
						
						if($nsector == '99' and $DIR_ASIGNACION == 1){

							$count_99_1++;
							
							$direccion = $CALLE.' '.$NUMERO.' '.$BLOCK.' '.$PISO.' '.$DEPTO.' '.$POB_VILLA;
	
							//$ajson = buscaLugar($direccion,$SURL);							
							
							if($tipo_entrada == 'XML' AND $fuentes == 'XYGO'){
								
									$response_a = '<?xml version="1.0" encoding="utf-8" ?>
										<ArrayofDireccion xmlns:xsd="http://www.w3.org/2001/XMLSchema">
											<Direccion>
												<ID>0</ID>
												<DireccionConcatenada>MAC IVER</DireccionConcatenada>
												<NombreCalle>Enrique Mac iver</NombreCalle>
												<NumeroMunicipal>440</NumeroMunicipal>
												<Comuna>santiago</Comuna>
												<IdDireccion>2092359</IdDireccion>
												<Latitud>-33.43757995</Latitud>
												<Longitud>-70.64729743</Longitud>
												<IdBlock>34100195</IdBlock>
												<IdBuzon>101</IdBuzon>
												<IdLote>
													<ID>43</ID>
												</IdLote>
												<CntDep>44</CntDep>
												<Pisos>15</Pisos>
												<IdSegmento>890330</IdSegmento>
												<Normalizo>true</Normalizo>
												<Distancia>0</Distancia>
												<Enriquecimientos>	
													<EnriquecimientoRetorno>
														<TipoVia>CALLE</TipoVia>
														<CodigoPostal>8320123</CodigoPostal>
													</EnriquecimientoRetorno>
												</Enriquecimientos>		
											</Direccion>
										</ArrayofDireccion>';

									$xml = simplexml_load_string($response_a);

									foreach ($xml->xpath('//NombreCalle') as $direccion_formateada);
									foreach ($xml->xpath('//NumeroMunicipal') as $altura);
									foreach ($xml->xpath('//Comuna') as $comuna_largo);
									foreach ($xml->xpath('//IdDireccion') as $IdDireccion);
									foreach ($xml->xpath('//Latitud') as $latitud);
									foreach ($xml->xpath('//Longitud') as $longitud);
									foreach ($xml->xpath('//IdBlock') as $IdBlock);
									foreach ($xml->xpath('//IdBuzon') as $estado);
									foreach ($xml->xpath('//ID') as $ID_Lote);
									foreach ($xml->xpath('//CntDep') as $CntDep);
									foreach ($xml->xpath('//Pisos') as $Pisos);
									foreach ($xml->xpath('//IdSegmento') as $IdSegmento);
									foreach ($xml->xpath('//Normalizo') as $Normalizo);
									foreach ($xml->xpath('//Distancia') as $Distancia);
									foreach ($xml->xpath('//TipoVia') as $tipo_via_largo);
									foreach ($xml->xpath('//CodigoPostal') as $CodigoPostal);

									$SQL_insertar = "INSERT INTO TABXYGO (NombreCalle,NumeroMunicipal,Comuna,IdDireccion,Latitud,Longitud,IdBlock,
									IdBuzon,IDDAT,CntDep,Pisos,IdSegmento,Normalizo,Distancia,TipoVia,CodigoPostal,FECHA_PROCESO,
									NOM_ARCHIVO)
									VALUES('".$direccion_formateada."','".$altura."','".$comuna_largo."','".$IdDireccion."','".$latitud."','".$longitud."','".$IdBlock."'
									,'".$estado."','".$ID_Lote."','".$CntDep."','".$Pisos."','".$IdSegmento."','".$Normalizo."','".$Distancia."','".$tipo_via_largo."','".$CodigoPostal."'
									,CURDATE(),'".$archivo."') ";
									
									//ECHO $SQL_insertar ;$P= strtoupper(trim(fgets(STDIN,256)));
									
									$result		= mysqli_query($conection,$SQL_insertar);
									
									$comuna_corto		= $comuna_largo;
									$nombre_via_largo	= $nombre_via_corto = '';
									$tipo_via_corto		= $tipo_via_largo;							
									$anexo				= '';
									$provincia_corto	= $provincia_largo		= '';
									$region_corto		= $region_largo			= '';
									$pais_largo			= $pais_corto			= '';
									
							}
							elseif($tipo_entrada == 'JSON' AND $fuentes == 'MAPCITY'){
								
									$response_a = json_decode($ajson);
								
									$ajson	= '{  
									   "direccion_consulta":"MAC-IVER 440 SANTIAGO",
									   "results":[  
										  {  
											 "direccion":{  
												"direccion_formateada":"CALLE SAN SEBASTIAN 2952 LAS CONDES",
												"nombre_via_corto":"SAN SEBASTIAN",
												"nombre_via_largo":"SAN SEBASTIAN",
												"tipo_via_corto":"CALLE",
												"tipo_via_largo":"CALLE",
												"estado":"4",
												"altura":"2952",
												"anexo":"",
												"comuna_corto":"LAS CONDES",
												"comuna_largo":"LAS CONDES",
												"provincia_corto":"SANTIAGO",
												"provincia_largo":"SANTIAGO",
												"region_corto":13,
												"region_largo":"METROPOLITANA",
												"pais_largo":"CHILE",
												"pais_corto":"CL"
											 },
											 "location":{  
												"lat":-33.413573072919746,
												"lng":-70.59994951888604,
												"sistema_coordenadas":"geografico",
												"metodo_geocoding":"GPS"
											 },
											 "caracterizacion":{  
												"destino":"EDIFICIO",
												"codigo_postal":"",
												"gse":"ABC1",
												"direccion_valida":true
											 }
										  }
									   ]
									}';
										
									$direccion_formateada	= $response_a->results[0]->direccion->direccion_formateada;
									$nombre_via_corto		= $response_a->results[0]->direccion->nombre_via_corto;
									$nombre_via_largo		= $response_a->results[0]->direccion->nombre_via_largo;
									$tipo_via_corto			= $response_a->results[0]->direccion->tipo_via_corto;
									$tipo_via_largo			= $response_a->results[0]->direccion->tipo_via_largo;
									$estado					= $response_a->results[0]->direccion->estado;
									$altura					= $response_a->results[0]->direccion->altura;
									$anexo					= $response_a->results[0]->direccion->anexo;
									$comuna_corto			= $response_a->results[0]->direccion->comuna_corto;
									$comuna_largo			= $response_a->results[0]->direccion->comuna_largo;
									$provincia_corto		= $response_a->results[0]->direccion->provincia_corto;
									$provincia_largo		= $response_a->results[0]->direccion->provincia_largo;
									$region_corto			= $response_a->results[0]->direccion->region_corto;
									$region_largo			= $response_a->results[0]->direccion->region_largo;
									$pais_largo				= $response_a->results[0]->direccion->pais_largo;
									$pais_corto				= $response_a->results[0]->direccion->pais_corto;			
									$latitud				= $response_a->results[0]->location->lat;
									$longitud				= $response_a->results[0]->location->lng;
							
							}
							
							$valida_dir = '0';
							if($estado == '4' or $estado == '5' or $estado == 101 or  $estado == 102){$valida_dir = '1';}
							
							$count_json++;
							
							$SQL_insertar = "INSERT INTO TABRESPMAP (RUT_CLIENTE,ID_DIRECCION,DIR_ENVIADA,DIR_FORMATEADA,NOMBRE_VIA_CORTO,";
							$SQL_insertar.= "NOMBRE_VIA_LARGO,TIPO_VIA_CORTO,TIPO_VIA_LARGO,ESTADO,ALTURA,ANEXO,";
							$SQL_insertar.= "COMUNA_CORTO,COMUNA_LARGO,PROVINCIA_CORTO,PROVINCIA_LARGO,REGION_CORTO,REGION_LARGO,";
							$SQL_insertar.= "PAIS_LARGO,PAIS_CORTO,LAT,LNG,FECHA_PROCESO,NOM_ARCHIVO,FUENTE) ";
							
							$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$ID_DIRECCION','$direccion','$direccion_formateada','$nombre_via_corto','$nombre_via_largo','$tipo_via_corto',";
							$SQL_insertar.= "'$tipo_via_largo','$estado','$altura','$anexo','$comuna_corto','$comuna_largo','$provincia_corto',";
							$SQL_insertar.= "'$provincia_largo','$region_corto','$region_largo','$pais_largo','$pais_corto','$latitud','$longitud',";
							$SQL_insertar.= "CURDATE(),'$archivo','$fuentes')";
								
							
							//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
							
							$result		= mysqli_query($conection,$SQL_insertar);
							$sector_validado = consulta_sector($SECTOR,$latitud,$longitud,$valida_dir,$conection);
							
						}
						elseif($nsector != '99'){
							$count_s00++;
							$sector_validado = consulta_sector($SECTOR,$LATITUD,$LONGITUD,$valida_dir,$conection);
						}						
						else{$count_99_N_1++;}
						
						if($sector_validado == '' and $nsector == '99' AND $valida_dir == 1 ){
							$count_nor_sinsec++;
							$valida_dir=4;
							}
						elseif($sector_validado != '' and $nsector == '99' AND $valida_dir == 1 ){
							$count_nor_sec++;
							}
						elseif($sector_validado == '' and $nsector != '99' ){
							$count_s00_sinsec++;
							$valida_dir=5;
							}
						elseif($sector_validado != '' and $nsector != '99' and $valida_dir	== 3 ){
							$count_s00_cambsec++;
							}
						elseif($sector_validado != '' and $nsector != '99' and $valida_dir	== 2 ){
							$count_s00_mansec++;
							}
						
						$SQL_insertar = "INSERT INTO TABDIR_NOR (RUT_CLIENTE,ID_DIRECCION,TIPO,CALLE,NUMERO,COD_COMUNA,";
						$SQL_insertar.= "BLOCK,PISO,DEPTO,POB_VILLA,COD_CIUDAD,ESTADO_DIR,OBSER1,DIR_ASIGNACION,SECTOR,";
						$SQL_insertar.= "DATOS_COMPLE,LATITUD,LONGITUD,FECHA_PROCESO,HORA_PROCESO,NOM_ARCHIVO,NUM_SECTOR,VALIDA_DIR,NEW_SECTOR,TIPO_ARCHIVO,FECHA_ORIG_ARCH ) ";
						
						$SQL_insertar.= "VALUES ('$RUT_CLIENTE','$ID_DIRECCION','$TIPO','$CALLE','$NUMERO','$COD_COMUNA',";
						$SQL_insertar.= "'$BLOCK','$PISO','$DEPTO','$POB_VILLA','$COD_CIUDAD','$ESTADO_DIR','$OBSER1','$DIR_ASIGNACION','$SECTOR',";
						$SQL_insertar.= "'$DATOS_COMPLE','$LATITUD','$LONGITUD',CURDATE(),CURTIME(),'$archivo','$nsector','$valida_dir','$sector_validado','$tipo_archivo','$fec_arch') ";
						
						//ECHO "PASO: ".$SQL_insertar;$P= strtoupper(trim(fgets(STDIN,256)));
						
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
				if($cont >0){
					
					$FIN_CARGA = date('Y-m-d H:i:s');
					
					graba_log_carga($archivo,$INI_CARGA,$FIN_CARGA,$cont_error,$count_total,$count_s00,$count_s99,$count_json,
						$count_99_1,$count_99_N_1,$count_sin_nor,
						$count_nor_sec,$count_nor_sinsec,$count_s00_sinsec,$count_s00_cambsec,
						$count_s00_mansec,$conection);
						
					crear_informe_beco($archivo,$fuentes,$conection);
				}
				mover_archivo($ruta_entrada.$archivo,$ruta_cargados.$archivo,$archivoError);
				//del_files_beco($ftp_server, $ftp_user_name, $ftp_user_pass, $server_file1, $server_file2); 
			}
		}
	}
}

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
		
		$inser_log = "INSERT INTO TABLOG_CARGA VALUES('$archivo','$ini_carga','$fin_carga','$count_total',";
		$inser_log.= "'$count_s99','$count_99_1','$count_99_N_1','$count_s00','$count_json','$count_sin_nor','$count_nor_sinsec','$count_nor_sec','$count_s00_sinsec',";
		$inser_log.= "'$count_s00_cambsec','$count_s00_mansec','$cont_error')";
		/*ECHO $inser_log;
		$P44= strtoupper(trim(fgets(STDIN,256)));*/
		$sql11 = mysqli_query($enlace_base,$inser_log);	
										
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
	$servername = "192.168.9.4";
	$username = "ediaz";
	$password = "c53392";
	$dbname = "MAPCITY";
	
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
  return $sNewWord;
}
/*function crear_informe_beco($archivo,$fuentes,$conection){	
	

	$s_archivo_alta ="SALIDA/UMBMD_DIR_NOR_".date('Ymd').".TXT";
	$archivo_alta=fopen ( $s_archivo_alta,"w");
	
	$s_archivo_baja ="SALIDA/UMBMD_INA_NOR_".date('Ymd').".TXT";
	$archivo_baja=fopen ( $s_archivo_baja,"w");
	
	$aarchivo = explode('.',$archivo);
	$s_archivo_99 ="SALIDA/PER99_".$archivo[0].".XLS";
	$archivo_99=fopen ( $s_archivo_99,"w");


	$fecha_hoy1			= date('Ymd');
	$avalor				= parametros_entrada('1','USUARIO',$conection);
	$usuario			= $avalor[0][0];


	$consulta = "SELECT * FROM TABRESPMAP A,TABDIR_NOR B WHERE A.ID_DIRECCION=B.ID_DIRECCION AND A.RUT_CLIENTE=B.RUT_CLIENTE
					AND A.FECHA_PROCESO=CURDATE() AND A.NOM_ARCHIVO='$archivo'
			AND B.FECHA_PROCESO=CURDATE() AND B.NOM_ARCHIVO='$archivo'
			and NUM_SECTOR = 99 AND VALIDA_DIR=1 ";
			
			//echo $consulta;
	$sql = mysqli_query($conection,$consulta);
	
	while($rowP = mysqli_fetch_array($sql)){
		
		$arut		= explode('-',$rowP['RUT_CLIENTE']);
		$rut		= intval($arut[0]);
		$dv			= $arut[1];
		
		if( (trim($rowP['CALLE']).$rowP['NUMERO'] ) != (trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] )){
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
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
			$INPDM_COP			= PIC('X',10,'');							//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('X',10,'');							//14 codigo segun maestro region
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
		elseif( (trim($rowP['CALLE']).$rowP['NUMERO'] ) == (trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] )){
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
			$INPDM_CLAVE		= $rut.$dv.$cod_comuna_R.$numero_R.$calle_R;
			$INPDM_CLAVE		= PIC('9',45,trim($INPDM_CLAVE));			//1 clave ident domi rut+cod_comu+nro+calle trun 45
			$RUT_CL				= PIC('9',9,trim($rut));
			$INPDM_PER_CLAVE	= PIC('9',45,trim($RUT_CL.$dv));			//2 clave identificadora,rut sin guiones
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
			$INPDM_COP			= PIC('X',10,'');							//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('X',10,'');							//14 codigo segun maestro region
			$INPDM_FUENTE		= PIC('X',30,'BECO');						//15 fuente origen del dato
			$INPDM_ESTADO		= PIC('X',10,'INA');						//16 estado del domicilio
			$INPDM_GEOREF1		= PIC('X',100,'');							//17 latitud
			$INPDM_GEOREF2		= PIC('X',100,'');							//18 longitud
			$INPDM_GEOREF3		= PIC('X',100,'');							//19 sector
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
		
		$cod_ciudad = $cod_region = '';
		
		//$cod_comuna_N		= SACA_COD_COMUNA($rowP['COMUNA_LARGO'],$cod_ciudad,$conection);
		//$cod_ciudad_N		= SACA_COD_CIUDAD($cod_ciudad,$cod_region,$conection);
		//$cod_region_N		= SACA_COD_REGION($cod_region,$conection);
		
		$cod_comuna_N		= SACA_COD_COMUNA($rowP['COMUNA_LARGO'],$cod_ciudad,$conection);
		$cod_region_N		= SACA_COD_REGION($cod_ciudad,$conection);

		$numero_N			= trim($rowP['ALTURA']);
		$calle_N			= trim($rowP['NOMBRE_VIA_LARGO']);
		
		$INPDM_CLAVE 		= $rut.$dv.$cod_comuna_N.$numero_N.$calle_N;

		$INPDM_CLAVE		= PIC('9',45,trim($INPDM_CLAVE));		//1 clave ident domi rut+cod_comu+nro+calle trun 45
		$RUT_CL				= PIC('9',9,trim($rut));
		$INPDM_PER_CLAVE	= PIC('9',45,trim($rut).$dv);			//2 clave identificadora,rut sin guiones
		$INPDM_DEFAULT		= PIC('X',1,'N');						//3 marca de domicilio
		
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
		$INPDM_COP			= PIC('X',10,'');						//12 codigo postal
		$INPDM_COD_POSTAL	= PIC('X',50,'');						//13 codigo postal en texto
		$INPDM_PAI			= PIC('X',10,$cod_region_N);			//14 codigo segun maestro region
		$INPDM_FUENTE		= PIC('X',30,'BECO');					//15 fuente origen del dato
		$INPDM_ESTADO		= PIC('X',10,'INA');					//16 estado del domicilio
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
	
		$INPDM_FILLER		= PIC('X',245,trim($rowP['COD_COMUNA']));//25 usuario modificacion(1-10);marca dir(11-15);datos complementarios(16-116)
		$INPDM_ERROR		= PIC('X',10,'');						//26 blancos
		$INPDM_ERROR_CAMPO	= PIC('X',30,'');						//27 blancos
		
		$s_buffer =	$INPDM_CLAVE.$INPDM_PER_CLAVE.$INPDM_DEFAULT.$INPDM_DTI.$INPDM_CALLE.$INPDM_NUMERO.$INPDM_PISO;
		$s_buffer.=	$INPDM_DPTO.$INPDM_LOC_TEXTO.$INPDM_LOC.$INPDM_PRV.$INPDM_COP.$INPDM_COD_POSTAL.$INPDM_PAI;
		$s_buffer.=	$INPDM_FUENTE.$INPDM_ESTADO.$INPDM_GEOREF1.$INPDM_GEOREF2.$INPDM_GEOREF3.$INPDM_GEOREF4.$INPDM_ESTADO_NORM;
		$s_buffer.=	$INPDM_FECHA_NORM.$INPDM_FUENTE_NORM.$INPDM_OBS.$USUARIO.$INPDM_FILLER.$INPDM_ERROR.$INPDM_ERROR_CAMPO;
		fwrite($archivo_alta,$s_buffer."\r\n");
	}
	
	$consulta = "SELECT * FROM TABDIR_NOR WHERE FECHA_PROCESO=CURDATE() AND NOM_ARCHIVO='$archivo'
			AND NUM_SECTOR = 99 AND VALIDA_DIR=0 ";
	$sql = mysqli_query($conection,$consulta);
	
	$sSep = chr(9);
	$marca_def=$cod_region=$desc_region=$DESC_CIUDAD='';
	
	while($rowP = mysqli_fetch_array($sql)){
	
	$buffer = $usuario.$sSep.$rowP['RUT_CLIENTE'].$sSep.$marca_def.$sSep.$rowP['TIPO'].$sSep.$rowP['CALLE'].$sSep.$rowP['NUMERO'].$sSep;
	$buffer.= $rowP['COD_COMUNA'].$sSep.$rowP['POB_VILLA'].$sSep.$rowP['COD_CIUDAD'].$sSep.$DESC_CIUDAD.$sSep;
	$buffer.= $cod_region.$sSep.$desc_region.$sSep.$rowP['LATITUD'].$sSep.$rowP['LONGITUD'].$sSep.$rowP['SECTOR'];
	fwrite($archivo_99,$buffer."\r\n");
	}		
	
}*/

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
	$avalor				= parametros_entrada('1','USUARIO',$conection);
	$usuario			= $avalor[0][0];
	

	$consulta = "SELECT * FROM TABRESPMAP A,TABDIR_NOR B WHERE A.ID_DIRECCION=B.ID_DIRECCION AND A.RUT_CLIENTE=B.RUT_CLIENTE
					AND A.FECHA_PROCESO='2017-03-13' AND A.NOM_ARCHIVO='$archivo'
			AND B.FECHA_PROCESO='2017-03-13' AND B.NOM_ARCHIVO='$archivo'
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
		
		if( (trim($rowP['CALLE']).$rowP['NUMERO'] ) != (trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] )){
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
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
			$INPDM_COP			= PIC('X',10,'');							//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('X',10,'');							//14 codigo segun maestro region
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
		elseif( (trim($rowP['CALLE']).$rowP['NUMERO'] ) == (trim($rowP['NOMBRE_VIA_LARGO']).$rowP['ALTURA'] )){
			
			$cod_comuna_R		= trim($rowP['COD_COMUNA']);
			$numero_R			= trim($rowP['NUMERO']);
			$calle_R			= trim($rowP['CALLE']);
			$INPDM_CLAVE		= $rut.$dv.$cod_comuna_R.$numero_R.$calle_R;
			$INPDM_CLAVE		= PIC('X',45,trim($INPDM_CLAVE));			//1 clave ident domi rut+cod_comu+nro+calle trun 45
			$RUT_CL				= PIC('9',9,trim($rut));
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
			$INPDM_COP			= PIC('X',10,'');							//12 codigo postal
			$INPDM_COD_POSTAL	= PIC('X',50,'');							//13 codigo postal en texto
			$INPDM_PAI			= PIC('X',10,'');							//14 codigo segun maestro region
			$INPDM_FUENTE		= PIC('X',30,'BECO');						//15 fuente origen del dato
			$INPDM_ESTADO		= PIC('X',10,'INA');						//16 estado del domicilio
			$INPDM_GEOREF1		= PIC('X',100,'');							//17 latitud
			$INPDM_GEOREF2		= PIC('X',100,'');							//18 longitud
			$INPDM_GEOREF3		= PIC('X',100,'');							//19 sector
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
		
		$INPDM_CLAVE 		= $rut.$dv.$cod_comuna_N.$numero_N.$calle_N;

		$INPDM_CLAVE		= PIC('X',45,trim($INPDM_CLAVE));		//1 clave ident domi rut+cod_comu+nro+calle trun 45
		$RUT_CL				= PIC('9',9,trim($rut));
		$INPDM_PER_CLAVE	= PIC('X',45,trim($rut).$dv);			//2 clave identificadora,rut sin guiones
		$INPDM_DEFAULT		= PIC('X',1,'N');						//3 marca de domicilio
		
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
		$INPDM_COP			= PIC('X',10,'');						//12 codigo postal
		$INPDM_COD_POSTAL	= PIC('X',50,'');						//13 codigo postal en texto
		$INPDM_PAI			= PIC('X',10,$cod_region_N);			//14 codigo segun maestro region
		$INPDM_FUENTE		= PIC('X',30,'BECO');					//15 fuente origen del dato
		$INPDM_ESTADO		= PIC('X',10,'INA');					//16 estado del domicilio
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
	
		$INPDM_FILLER		= PIC('X',245,trim($rowP['COD_COMUNA']));//25 usuario modificacion(1-10);marca dir(11-15);datos complementarios(16-116)
		$INPDM_ERROR		= PIC('X',10,'');						//26 blancos
		$INPDM_ERROR_CAMPO	= PIC('X',30,'');						//27 blancos
		
		$s_buffer =	$INPDM_CLAVE.$INPDM_PER_CLAVE.$INPDM_DEFAULT.$INPDM_DTI.$INPDM_CALLE.$INPDM_NUMERO.$INPDM_PISO;
		$s_buffer.=	$INPDM_DPTO.$INPDM_LOC_TEXTO.$INPDM_LOC.$INPDM_PRV.$INPDM_COP.$INPDM_COD_POSTAL.$INPDM_PAI;
		$s_buffer.=	$INPDM_FUENTE.$INPDM_ESTADO.$INPDM_GEOREF1.$INPDM_GEOREF2.$INPDM_GEOREF3.$INPDM_GEOREF4.$INPDM_ESTADO_NORM;
		$s_buffer.=	$INPDM_FECHA_NORM.$INPDM_FUENTE_NORM.$INPDM_OBS.$USUARIO.$INPDM_FILLER.$INPDM_ERROR.$INPDM_ERROR_CAMPO;
		//echo $s_buffer;$P44= strtoupper(trim(fgets(STDIN,256)));
		fwrite($archivo_alta,$s_buffer."\r\n");
	}
	
	$consulta = "SELECT * FROM TABDIR_NOR WHERE FECHA_PROCESO='2017-03-13' AND NOM_ARCHIVO='$archivo'
			AND NUM_SECTOR = 99 AND VALIDA_DIR=0 ";
	$sql = mysqli_query($conection,$consulta);
	
	$sSep = chr(9);
	$marca_def=$cod_region=$desc_region=$DESC_CIUDAD='';
	
	while($rowP = mysqli_fetch_array($sql)){
	
	echo $cont++;
	echo chr(13);
	
	$buffer = $usuario.$sSep.$rowP['RUT_CLIENTE'].$sSep.$marca_def.$sSep.$rowP['TIPO'].$sSep.$rowP['CALLE'].$sSep.$rowP['NUMERO'].$sSep;
	$buffer.= $rowP['COD_COMUNA'].$sSep.$rowP['POB_VILLA'].$sSep.$rowP['COD_CIUDAD'].$sSep.$DESC_CIUDAD.$sSep;
	$buffer.= $cod_region.$sSep.$desc_region.$sSep.$rowP['LATITUD'].$sSep.$rowP['LONGITUD'].$sSep.$rowP['SECTOR'];
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
/*
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
	$consulta	= "SELECT COD_REGISTRO FROM TABCODBECO WHERE TIPO_REGISTRO=33 AND COD_RELACIONADO='$ciudad'";
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];

	return $cod_comu;
	
 }
function SACA_COD_CIUDAD($ciudad, &$cod_region,$enlace_base){
	
	$cod_comu = '';
	$consulta	= "SELECT COD_REGISTRO, COD_RELACIONADO FROM TABCODBECO WHERE TIPO_REGISTRO=16 AND DESC_REGISTRO='$ciudad'";
	$sqlc = mysqli_query($enlace_base,$consulta);
	$rowc = mysqli_fetch_array($sqlc);
	$cod_comu = $rowc[0];
	$cod_region = $rowc[1];
	
	return $cod_comu;
	
 }
*/
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
function saca_fecha_archivo($archivo){
	
		$fecha_archivo = '';
		$afecha = explode('_',$archivo);
		$cuenta_fec = count($afecha)-1;
		$aafecha = explode('.',$afecha[$cuenta_fec]);
		$fecha_archivo = substr($aafecha[0],0,4)."-".substr($aafecha[0],4,2)."-".substr($aafecha[0],6,2);

		//ECHO "tipo_archivo: $fecha_archivo";$P= strtoupper(trim(fgets(STDIN,256)));
		
		return $fecha_archivo;
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
?>