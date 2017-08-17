<?php
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 900);
ini_set('default_socket_timeout', 600);

$scalle = $_GET['calle'];
$snro = $_GET['nro'];
$scomuna = $_GET['comuna'];
$sfin = $_GET['desc'];

$client = new SoapClient("http://demos.xygo.com/OPENGETS_WS/Metodos.asmx?WSDL");

if (isset($sfin) && $sfin=='SI') {
    var_dump($client->__getFunctions());
};

if (isset($scalle) && isset($snro) && isset($scomuna)) {
 try {

 	$direccion = $scalle .' '.$snro.', ' . $scomuna;
  	print("DIRECCION CONCATENADA ENTRADA:><h3> <b>".$direccion."\n </b></H3>");
    $result = $client->FindDireccionConcatenada(array("idPais" => '56', "direccion" => $direccion));
    echo 'Metodo 2: FindirecciónConcatenada:';
    $xml=simplexml_load_string($result->FindDireccionConcatenadaResult);
    print("Resultado  ==>" . $xml->buzon_g ."\n");
    if ($xml->longitud_dire<>0){
    	print("\n<b>Dirección Normalizada OK:</b>");
    	print($xml->nombre_calle_salida_geo . ",". $xml->numero_municipal . ",". $xml->comuna_xygo. " (".$xml->latitud_dire . "," . $xml->longitud_dire .")");
    }
    else
    	print('<h4><b><span style="background-color:red">Dirección NO Normalizada!!!</span></b></h4>');	
   }
 catch (SoapFault $exception) {
    echo $exception;      
  }
} 
else{
	echo "no  ha proporcionado parametro [calle]";
    }

?>

<?php
$client = new SoapClient("http://demos.xygo.com/OPENGETS_WS/Metodos.asmx?WSDL");
  ?>