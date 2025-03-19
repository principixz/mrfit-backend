<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "BaseController.php";

  header("Access-Control-Allow-Credentials: true");
  header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Content-Type: application/json; charset=UTF-8');
class Procesos extends BaseController {

  public function  __construct(){
    parent::__construct();
  }

  public function proceso_envio_boleta(){
	  // Configura la zona horaria a Perú
	  date_default_timezone_set('America/Lima');
  
	  // URL y token (en producción)
	  $ruta = 'https://factura.selvafood.com/api/summaries';
	  $token = '8qn5JHCUSsWkm2Q7hemQ57HNCDV0t4n0PE8iSIvT5cBuLfphmC';
  
	  // Preparar datos para el POST
	  $data = array();
	  $data["fecha_de_emision_de_documentos"] = date("Y-m-d"); // Fecha actual en formato Y-m-d (hora según zona Perú)
	  $data["codigo_tipo_proceso"] = "1";
	  $data_json = json_encode($data);
  
	  // Inicializar cURL
	  $curl = curl_init();
	  curl_setopt_array($curl, array(
		  CURLOPT_URL => $ruta,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $data_json,
		  CURLOPT_HTTPHEADER => array(
			  "authorization: Bearer " . $token,
			  "cache-control: no-cache",
			  "content-type: application/json",
		  ),
	  ));
  
	  $response = curl_exec($curl);
	  $err = curl_error($curl);
	  curl_close($curl);
  
	  if ($err) {
		  echo "cURL Error #:" . $err;
	  } else {
		  $json = json_decode($response, true);
		  print_r($json);
		  if ($json["success"] == 1) {
			  $data_update = array(
				  "venta_resumen_external_id" => $json["data"]["external_id"],
				  "venta_resumen_ticket"        => $json["data"]["ticket"],
				  "venta_resumen_fecha"         => date("Y-m-d H:i:s")
			  );
  
			  $sql = "date(venta.venta_pedidofecha)='" . date("Y-m-d") . "' and (venta_resumen_external_id is null or venta_resumen_external_id='') and ventas_idtipodocumento=2";
			  $this->db->where($sql);
			  $r = $this->db->update("venta", $data_update);
		  }
	  }
  
	  // Al finalizar el envío, invoca la función para verificar el estado de la boleta
	  $this->verificar_envio_boleta();
  }


    public function proceso_envio_boleta_fecha(){
    // Obtener la fecha desde el parámetro GET, si no se envía se usa la fecha actual
    $fecha = $this->input->get('fecha');
    if (empty($fecha)) {
        $fecha = date("Y-m-d");
    }

    // Definir la URL y el token (en producción)
    $ruta = 'https://factura.selvafood.com/api/summaries';
    $token = '8qn5JHCUSsWkm2Q7hemQ57HNCDV0t4n0PE8iSIvT5cBuLfphmC';

    // Preparar los datos para el POST
    $data = array();
    $data["fecha_de_emision_de_documentos"] = $fecha;
    $data["codigo_tipo_proceso"] = "1";
    $data_json = json_encode($data);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $ruta,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data_json,
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer " . $token,
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $json = json_decode($response, true);
        print_r($json);
        if ($json["success"] == 1) {
            $data_update = array(
                "venta_resumen_external_id" => $json["data"]["external_id"],
                "venta_resumen_ticket" => $json["data"]["ticket"],
                "venta_resumen_fecha" => date("Y-m-d H:i:s")
            );
             
            // Utiliza el parámetro fecha en la condición de actualización
            $sql = "date(venta.venta_pedidofecha)='" . $fecha . "' and (venta_resumen_external_id is null or venta_resumen_external_id='') and ventas_idtipodocumento=2";
            $this->db->where($sql);
            $r = $this->db->update("venta", $data_update);
        }
    }
}





public function verificar_envio_boleta()
{

 date_default_timezone_set('America/Lima');
 $ruta = 'https://factura.selvafood.com/api/summaries';
		$token = '8qn5JHCUSsWkm2Q7hemQ57HNCDV0t4n0PE8iSIvT5cBuLfphmC';

		$sql="SELECT DISTINCT(venta.venta_resumen_external_id) ,
					venta.venta_resumen_ticket
					from venta
					where
					venta_estado=1 and
					venta_resumen_external_id   IS NOT NULL and
					date(venta_pedidofecha)='".date("Y-m-d")."' and
ventas_idtipodocumento=2";

		$tok=$this->db->query($sql)->row_array();
		$external_id=$tok["venta_resumen_external_id"];
		$ticket=$tok["venta_resumen_ticket"];

           if($ticket!="")
           {

			 $request=array();
             $ruta = 'https://factura.selvafood.com/api/summaries/status';
		   	
		   	  $request["external_id"]=$external_id;
		   	  $request["ticket"]=$ticket;


						   	  $data_json = json_encode($request);

						//echo $data_json;exit();
						$curl = curl_init();

						curl_setopt_array($curl, array(
						  CURLOPT_URL => $ruta ,
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => "",
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 30,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => "POST",
						  CURLOPT_POSTFIELDS => $data_json,
						  CURLOPT_HTTPHEADER => array(
						    "authorization: Bearer ".$token,
						    "cache-control: no-cache",
						    "content-type: application/json",
						   
						  ),
						));

						$response = curl_exec($curl);
						$err = curl_error($curl);
//						print_r($response);

						curl_close($curl);

						if ($err) {
						  echo "cURL Error #:" . $err;
						} else {
                              $json=json_decode($response,true);
                              print_r($json);
                              if($json["success"])
                              {
                                       
							     $data=array(
							              "venta_resumen_xml"=>$json["links"]["xml"],
							              "venta_resumen_cdr"=>$json["links"]["cdr"],
							              "venta_resumen_cdr_fecha"=>date("Y-m-d H:i:s")


							            );
							             
							             $sql="venta_resumen_external_id='".$external_id."' and venta_resumen_ticket='".$ticket."'";
							            $this->db->where($sql);

							             $r=$this->db->update("venta",$data);



                              }

						}



          }




}

}