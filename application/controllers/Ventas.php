 

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Content-type: text/html; charset=utf8");
require_once "BaseController.php";

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;


class Ventas extends BaseController {

	public function __construct() {
		parent::__construct();


	}

	public function index(){
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
		$data=array();
		$data["titulo_descripcion"]="Lista de Ventas";
		$this->vista("Ventas/index",$data);
	}

	public function nuevo(){
		$data=array();
		$data["titulo_descripcion"]="Nueva Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["tipo_entrega"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_entrega where tipoentrega_estado = 1");
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]." LIMIT 12");
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
			tipo_documento
			INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id IN(1,2,5)");
		////$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);
		$data["estadostock"] =$this->estado_configuracion(11); 

     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
		$this->vista("Ventas/nuevo1",$data);
	}

	public function nuevaprueba(){
		$data=array();
		$data["titulo_descripcion"]="Nueva Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
			tipo_documento
			INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id<5");
		//$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);
		$data["estadostock"] =$this->estado_configuracion(11); 
		$this->vista("Ventas/nuevaprueba",$data);
	}

	public function nuevaventa($id,$opcion,$mesanombre){
		$data=array();
		$data["titulo_descripcion"]="Nueva Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["data_envio"] = array($id,$opcion);
		$data["nombre"] = array($mesanombre);
		$data["tipo_entrega"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_entrega where tipoentrega_estado = 1");
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
				tipo_documento
				INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
				INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
				where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id IN(1,2,5,6)");
		$data["tipo_documento_cliente"]=$this->db->query("select * from tipo_cliente where tipo_cliente_estado=1")->result_array();

		//$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
		$data["estado_impresion"]=1;
		$data["estadostock"] =1; 
		$data["empresa"]=$this->db->query("select * from empresa")->row_array();
		$this->load->view("Ventas/jsventas",compact('data'));
	}
	public function nuevaventa1(){
		$data=array();

		$data["titulo_descripcion"]="Nueva Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["data_envio"] = array($id,$opcion);
		$data["nombre"] = array($mesanombre);
		$data["tipo_entrega"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_entrega where tipoentrega_estado = 1");
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
			tipo_documento
			INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id<5");
		//$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
		$data["estado_impresion"]=1;
		$data["tipo_documento_cliente"]=$this->db->query("select * from tipo_cliente where tipo_cliente_estado=1")->result_array();
		
		$data["estadostock"] =1; 
		$this->load->view("Ventas/jsventas",compact('data'));
	}
	public function traerventas($idventa,$idvendedor,$idsilla,$nombremesas){
		$data = array();
		$data["titulo_descripcion"]="Actualizar Venta";
		$data["categoriaproducto"] = $this->Mantenimiento_m->consulta3("SELECT categoria_producto.categoria_producto_id,categoria_producto.id_sede,categoria_producto.categoria_producto_descripcion,categoria_producto.categoria_imagen FROM categoria_producto where categoria_producto.categoria_producto_estado = 1 and categoria_producto.id_sede =".$_COOKIE["id_sede"]);
		$data["data_actualizar"] = array($idventa,$idvendedor,$idsilla);
		$data["nombre"] = array($nombremesas);
		$data["productos"]=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,
			producto.producto_descripcion,
			producto.producto_precio,
			producto.producto_stock,
			producto.producto_id_tipoproducto,
			if(detalle_producto_almacen.detalle_almacen is null,'null',detalle_producto_almacen.detalle_almacen) as detalle_almacen,	
				detalle_producto_almacen.detalle_stock,
			producto.producto_imagen from producto LEFT JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
		$data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
		$data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
		$data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * FROM
			tipo_documento
			INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
			INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
			where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id<=6");
		$data["empresa"]=$this->Mantenimiento_m->consulta3("select * FROM empresa LIMIT 1");
		$data["estado_impresion"]=1;
		$data["estadostock"] =1; 
		$data["tipo_documento_cliente"]=$this->db->query("select * from tipo_cliente where tipo_cliente_estado=1")->result_array();

		$this->load->view("Ventas/jsventas",compact('data'));
	}

	public function traerdetalleventas(){

		$data = array();
		$data["venta"] = $this->Mantenimiento_m->consulta3("SELECT * FROM venta INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id where venta.venta_idventas =".$_POST["idventa"]);
		$data["detalleventa"] = $this->Mantenimiento_m->consulta3("SELECT venta.venta_idventas,detalle_venta.descripcion,detalle_venta.id_detalle_venta,detalle_venta.cantidad,detalle_venta.observaciones,detalle_venta.estado_pedido,detalle_venta.detalle_estado_preparado,
			detalle_venta.cod_producto_venta,detalle_venta.precio,producto.producto_descripcion
			FROM venta
			INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
			WHERE estado_pedido=1 and
			venta.venta_idventas =".$_POST["idventa"]);
		echo json_encode($data);
	}

	public function guardar(){

		$data=array(
			"categoria_descripcion"=>$this->input->post("descripcion"),

			"id_sede"=>$_SESSION["id_sede"]
			


		);
		if($this->input->post("id")=="")
		{
			$estado=$this->db->insert('categoria', $data);
		}
		else
		{
			$this->db->where('categoria_id',$this->input->post("id"));
			$estado=$this->db->update('categoria', $data);
		}
		header('Location: '.base_url()."categoria");
	}

	public function editar($id)
	{
		$data=array();
		$data["titulo_descripcion"]="Editar Categoria";
		$data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
		$this->vista("Categoria/nuevo",$data);

	}

	public function traer_uno()
	{
		$dat=$this->Mantenimiento_m->consulta3("select * from categoria where categoria_id=".$this->input->post("id"));
		echo json_encode($dat);
	}

	public function ver_ruc()
	{


		$cliente = new \Sunat\Sunat();
		$data=   $cliente->search( $this->input->post("ruc") );
		echo json_encode($data);


	}




	public function eliminarventa(){

		if(!empty($_POST["eliminar_usuario"])){
			$administrador = array();
			$administrador= $datos_venta=$this->Mantenimiento_m->consulta3("SELECT empleados.empleado_id FROM empleados WHERE empleados.perfil_id=12 AND empleados.empleado_usuario='".$_POST['eliminar_usuario']."' AND empleados.empleado_clave='".$_POST['eliminar_contrasena']."' AND empleados.empresa_sede=".$_COOKIE["id_sede"]);
			if(count($administrador)==0){
				$respuesta["estado"]=0;
				$respuesta["texto"]="Usuario invalido";
				echo json_encode($respuesta);
				return 1;
			}
		}
		$id=$_POST["id_detalle_venta_modal"];
		$saldocaja = $this->verificar_saldo();
		$traer_venta = $this->Mantenimiento_m->consulta2("SELECT * FROM venta where venta_idventas = ".$id."");
		$consultar_restante = $saldocaja - $traer_venta->venta_monto;
		$monto = $traer_venta->venta_monto;
		if ($consultar_restante < 0) {
			$respuesta["estado"]=0;
			$respuesta["texto"]="Saldo Insuficiente para la devolución";
				echo json_encode($respuesta);
				return 1;
		}
		else{
			
			/*$dat=$this->Mantenimiento_m->consulta3("SELECT * FROM detalle_doc_sede INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
				INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=11");
			if (count($dat) == 0) {
				$respuesta["estado"]=0;
				$respuesta["texto"]="USTED AÚN NO CONFIGURA SU COMPROBANTE NOTA DE CREDITO";
				echo json_encode($respuesta);
				exit();
			}*/
			/*$descripcion_comprobante=$dat[0]["doc_serie"]."-".$dat[0]["doc_correlativo"];*/
			$descripcion_comprobante="";

			/*$serie=$dat[0]["doc_serie"];
			$correlativo=$dat[0]["doc_correlativo"];			
			$id_documento=$dat[0]["id_documento"]; 
			$nuevo_correlativo=(int)$correlativo+1;*/
			$id_movimiento=$this->generar_movimiento(1,$monto,1,5,'DEVOLUCIÓN DE VENTA',2,18,$descripcion_comprobante,'',2);
			if($id_movimiento==0){
				$respuesta["estado"]=0;
				$respuesta["texto"]="No se pudo generar el movimiento porque usted no abrio caja";
				echo json_encode($respuesta);
				exit();
			}else{
				$id_movimiento;
			}
			/*$datos=array(
				"doc_correlativo"=>$nuevo_correlativo
			);
			$this->Mantenimiento_m->actualizar("documento",$datos,$id_documento,"id_documento");*/
			$data=array(
				"tipo_comprobante_descripcion"=>$descripcion_comprobante
			);
			$this->db->where('mov_id',$id_movimiento);
			$estado=$this->db->update('movimiento', $data);

			/*$this->db->query("UPDATE venta SET venta_estado_pagado = 2,venta_tidocelimi = 11, venta_serie_eliminado = '".$serie."',venta_correlativo_eliminado = ".$correlativo." , venta_idpersonal_eliminado = ".$_COOKIE["usuario_id"]."  WHERE venta_idventas=".$id."; ");*/

			$this->db->query("UPDATE venta SET venta_movimiento_anulacion=".$id_movimiento.", venta_eliminacion_descripcion='".$_POST['motivo']."',venta_fecha_eliminacion='".date("Y-m-d H:i:s")."', venta_estado_pagado = 2,venta_tidocelimi = 11, venta_idpersonal_eliminado = ".$_COOKIE["usuario_id"]."  WHERE venta_idventas=".$id."; ");


			$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
				IF(producto.producto_id_tipoproducto = 1 ,detalle_venta.precio ,producto.producto_precio) AS precio,detalle_venta.cantidad
				FROM detalle_venta
				INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_venta=".$id." and detalle_venta.estado_pedido<>0");


			foreach ($detalle_venta as $key => $value){
				$tipo_producto=$this->Mantenimiento_m->consulta3("select * from producto where producto_id=".$value["cod_producto_venta"]);
				$tipo_product = array();
				$stock_detalle = array();
				if($tipo_producto[0]["producto_id_tipoproducto"]==3){
					//if ($this->estado_configuracion(11)) {
						$tipo_product=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$value["cod_producto_venta"].";")->row();
						$producto=array(
							"producto_stock" => ((float)$tipo_product->producto_stock + (float)$value["cantidad"])
						);
						$this->db->where('producto_id',$value["cod_producto_venta"]);
						$estado=$this->db->update('producto', $producto);

						/*$stock_detalle=$this->db->query("SELECT detalle_producto_almacen.detalle_stock,detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"].";")->row();
					$detalle_almacen= array(
						"detalle_stock" => ((float)$stock_detalle->detalle_stock + (float)$value["cantidad"])
					);
					$this->db->where('detalle_producto',$value["cod_producto_venta"]);
					$estado=$this->db->update('detalle_producto_almacen', $detalle_almacen);*/
					//}

				}else{
					$stock_detalle=$this->db->query("SELECT detalle_producto_almacen.detalle_stock,detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"].";")->row();
					//echo "SELECT detalle_producto_almacen.detalle_stock,detalle_producto_almacen.detalle_stock_temporal FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"].";";
					$detalle_almacen= array(
						"detalle_stock" => ((float)$stock_detalle->detalle_stock + (float)$value["cantidad"])
					);
					$this->db->where('detalle_producto',$value["cod_producto_venta"]);
					$estado=$this->db->update('detalle_producto_almacen', $detalle_almacen);
				}
				//$this->venta_actualizacion_stock($value["cod_producto_venta"],$value["cantidad"],$value["precio"],"" ,"",18);

			}
           
           $this->darbajaventa($id,$_POST['motivo']);
		//	$this->relacion_cuentas($id,14,2);
			$respuesta["estado"]=1;
			$respuesta["texto"]="Se realizo la transacción correctamente";
			echo json_encode($respuesta);
		}
	}



	public function darbajaventa($idventa,$mensaje)
{


$empresa=$this->db->query("select * from empresa ")->row_array();

      $ruta = '';
          $ruta_status='';
           //    $ruta = 'http://facturacion.gokiebox.pe/api/summaries';
      //prueba
    //  $token = 'S5ihugP7qVC8f6mCP2Abc3d4CmEAA9MCheQ5ivvfwET06bQql4';


          //produccion



        
        $token = $empresa["empresa_token_facturacion"];
        $ruta_universal=$empresa["empresa_link_facturacion"];
      $venta=$this->db->query("select *,
            venta.*,DATE_FORMAT(venta.venta_fecha_pago, '%Y-%m-%d') as 'fecha'
       from venta where venta_idventas=".$idventa)->row_array();


      if((int)$venta["ventas_idtipodocumento"]==1){

        $ruta = $ruta_universal.'api/voided';
        $ruta_status= $ruta_universal.'api/voided/status';


          // factura
      }else{
           // boleta
        $ruta =  $ruta_universal.'api/summaries';
        $ruta_status= $ruta_universal.'api/summaries/status';

      }
      //venta_ticket_external_id

   //  echo $ruta;

 

    $data=array();
    $data["fecha_de_emision_de_documentos"]=$venta['fecha'];
    $data["codigo_tipo_proceso"]="3";

    $documento=array(
      "external_id"=>$venta["venta_ticket_external_id"],
      "motivo_anulacion"=>$mensaje,

    );

    $data["documentos"][0]=$documento;

     $data_json = json_encode($data);

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

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
        
       $json=json_decode($response,true);
       

       if($json["success"]==1){

/////////////////////////////////////////////////

                  //print_r($response);
      
         $request=array();
          //$ruta = 'http://facturacion.gokiebox.pe/api/summaries/status';
          $da=$json["data"];
          $external_id=$da["external_id"];
          $ticket=$da["ticket"];
          $request["external_id"]=$external_id;
          $request["ticket"]=$ticket;
            // print_r($request);

                  $data_json = json_encode($request);

            //echo $data_json;exit();
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $ruta_status ,
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
            //print_r($response);
///exit();
            curl_close($curl);

            if ($err) {
              echo "cURL Error #:" . $err;
            } else {
                              $json=json_decode($response,true);
                              if($json["success"]==1)
                              {
                                  ///print_r($json);
                              }

            }










        ////////////////////
       }
     }

























}
 

	public function verificar_saldo(){
		$monto_ingreso=$this->Mantenimiento_m->consulta3("SELECT sum(movimiento.mov_monto) as monto FROM sede_caja INNER JOIN sesion_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja INNER JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id where sede_caja.id_sede=".$_COOKIE["id_sede"]." and concepto.id_tipo_movimiento=1 and sesion_caja.ses_estado=1 and movimiento.mov_estado=1 and concepto.con_estado=1 and sede_caja.id_caja=1");
		$monto_egreso =$this->Mantenimiento_m->consulta3("SELECT sum(movimiento.mov_monto) as monto FROM sede_caja INNER JOIN sesion_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja INNER JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id where sede_caja.id_sede=".$_COOKIE["id_sede"]." and concepto.id_tipo_movimiento=2 and sesion_caja.ses_estado=1 and movimiento.mov_estado=1 and concepto.con_estado=1 and sede_caja.id_caja=1");
		$monto_inicio= $this->Mantenimiento_m->consulta3("SELECT  sum(sesion_caja.ses_montoinicial) AS monto FROM   sede_caja INNER JOIN sesion_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja WHERE   sede_caja.id_sede = ".$_COOKIE["id_sede"]." AND sesion_caja.ses_estado = 1 and sede_caja.id_caja=1");
		$total_caja = $monto_ingreso[0]["monto"]+$monto_inicio[0]["monto"]-$monto_egreso[0]["monto"];
		return $total_caja;
	}


	public function buscar_mozo(){
		$data=$this->Mantenimiento_m->consulta3("SELECT * FROM empleados INNER JOIN perfiles ON empleados.perfil_id = perfiles.perfil_id where empleados.estado=1 and empleados.perfil_id=2 AND empleados.empresa_ruc=".$_COOKIE["ruc_empresa"]." and (empleados.empleado_apellidos like '%".$this->input->get("term")."%' or empleados.empleado_nombres like '%".$this->input->get("term")."%') limit 10");
		$data1=array();
		foreach ($data as $key => $value) {
			$data1["results"][$key]["id"]=$value["empleado_id"];
			$data1["results"][$key]["text"]=$value["empleado_nombres"].' '.$value["empleado_apellidos"];
		}
		echo json_encode($data1);

	}



	function cronograma_prestamo(){
		
		$monto_prest = $_POST["monto"];
		$cuotas=$_POST["cuotas"];
		$intervalo = $_POST["intervalo"];
		$fecha = $_POST["fecha_venta"];
		$this->load->view('Ventas/cronograma',compact('monto_prest','cuotas','intervalo','fecha'));
		
	}



	function traercantidades(){

		$data=$this->Mantenimiento_m->consulta3("select * from producto where producto_estado=1 and id_sede='".$_COOKIE["id_sede"]."' and producto_id= '".$this->input->post("id")."' and id_sede=".$_COOKIE["id_sede"]);
		echo json_encode($data);
	}

	public function buscar(){
		$html="";
		$tipo=0;
		$datos=array();
		$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
		// if ($this->estado_configuracion(6) == 1 ) {
		// 	$data1=$this->Mantenimiento_m->consulta3("select * from detalle_diasemana_plato INNER JOIN dia_semana ON detalle_diasemana_plato.iddia = dia_semana.iddia
		// 		INNER JOIN producto ON detalle_diasemana_plato.producto_id = producto.producto_id or (producto.producto_id_tipoproducto != 1 and producto.producto_estado=1) where detalle_diasemana_plato.estado = 1 and dia_semana.dia_descripcion = '".$dias[date('w')]."' and  producto.producto_estado=1 and producto.producto_codigobarra='".$this->input->post("valor")."'  and producto.id_sede=".$_COOKIE["id_sede"].' LIMIT 12');
		// }else{
		// 	$data1=$this->Mantenimiento_m->consulta3("select * from producto where producto_estado=1 and producto_codigobarra='".$this->input->post("valor")."'  and id_sede=".$_COOKIE["id_sede"].' LIMIT 12');
		// }
		// foreach ($data1 as $key => $value) {
		// 	$tipo=1;
		// 	$id=$value["producto_id"];
		// 	$descripcion=$value["producto_descripcion"];
		// 	$precio=$value["producto_precio"];
		// 	$datos["tipo"]=$tipo;
		// 	$datos["id"]=$id;
		// 	$datos["descripcion"]=$descripcion;
		// 	$datos["precio"]=$precio;
		// 	echo json_encode($datos);
		// 	exit();
		// }
$id_almacen = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
if($this->input->post("categoriaproducto")=="")
{


	$sql="(select producto.producto_encendido,producto.producto_id_tipoproducto,producto.producto_id,producto.producto_descripcion,producto.producto_precio,detalle_producto_almacen.detalle_stock as stock,producto.producto_imagen from producto INNER JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id
INNER JOIN almacenes ON detalle_producto_almacen.detalle_almacen = almacenes.almacen_id  where producto_estado=1 and (producto_descripcion LIKE '%".$this->input->post("valor")."%' or producto_codigobarra LIKE '%".$this->input->post("valor")."%' ) AND producto.producto_id_tipoproducto=1 and almacenes.id_sede=".$_COOKIE["id_sede"]." AND detalle_producto_almacen.detalle_almacen = ".$id_almacen->almacen_id." ) 
UNION 
(SELECT producto.producto_encendido,producto.producto_id_tipoproducto,producto.producto_id,producto.producto_descripcion,producto.producto_precio,producto.producto_stock,producto.producto_imagen FROM producto WHERE  producto.id_sede=".$_COOKIE["id_sede"]."  and producto.producto_estado = 1 AND producto.producto_id_tipoproducto=3 and (producto.producto_descripcion LIKE '%".$this->input->post("valor")."%' or producto.producto_codigobarra LIKE '%".$this->input->post("valor")."%' ) and producto.id_sede=".$_COOKIE["id_sede"]." GROUP BY  producto.producto_id order by producto.producto_id_tipoproducto  )";
	$data=$this->Mantenimiento_m->consulta3($sql);


}else{
	$data=$this->Mantenimiento_m->consulta3("(select producto.producto_encendido,producto.producto_id_tipoproducto,producto.producto_id,producto.producto_descripcion,producto.producto_precio,detalle_producto_almacen.detalle_stock as stock,producto.producto_imagen from producto INNER JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id
INNER JOIN almacenes ON detalle_producto_almacen.detalle_almacen = almacenes.almacen_id  where
producto.categoria_producto_id=".$this->input->post("categoriaproducto")." and 
 producto_estado=1 and (producto_descripcion LIKE '%".$this->input->post("valor")."%' or producto_codigobarra LIKE '%".$this->input->post("valor")."%' ) AND producto.producto_id_tipoproducto=1 and almacenes.id_sede=".$_COOKIE["id_sede"]." AND detalle_producto_almacen.detalle_almacen = ".$id_almacen->almacen_id." ) 
UNION 
(SELECT producto.producto_encendido,producto.producto_id_tipoproducto,producto.producto_id,producto.producto_descripcion,producto.producto_precio,producto.producto_stock,producto.producto_imagen FROM producto WHERE 
producto.categoria_producto_id=".$this->input->post("categoriaproducto")." and 
 producto.id_sede=".$_COOKIE["id_sede"]."  and
 producto.producto_estado = 1 AND producto.producto_id_tipoproducto=3 and (producto.producto_descripcion LIKE '%".$this->input->post("valor")."%' or producto.producto_codigobarra LIKE '%".$this->input->post("valor")."%' ) and producto.id_sede=".$_COOKIE["id_sede"]." GROUP BY  producto.producto_id order by producto.producto_id_tipoproducto  )");



}

	

		if (count($data) != 0) {

			foreach ($data as $key => $value) {
				$url="";

				if($value["producto_imagen"]!="")
				{
					$url= base_url().'public/img/productos/'.$value['producto_imagen'];
				}
				else{
					$url= base_url().'public/img/productos/default.jpg';
				}

                  if((int)$value["producto_encendido"]==0)
                  {
                  	$value["stock"]=0;
                  }



				if($value["producto_id_tipoproducto"]=="1"){
						$stock=$value["stock"];
						if($stock==0){

							$url= base_url().'public/img/productos/agotado.png';
						}
				}else{
					$stock=$value["stock"];
					if($stock==0){
						$url= base_url().'public/img/productos/agotado.png';
					}
				} 


                       
                  if((int)$value["producto_encendido"]==0)
                  {
                  	$stock=0;

                  }

				$a=$value["producto_id"].",'".$value["producto_descripcion"]."',".$value["producto_precio"].",".$stock;
				$html.='<input type="hidden" id="productid'.$value["producto_id"].'" value="'.$url.'">';
				$html.='<div class="col-xs-6 col-md-3" id="'.$value['producto_descripcion'].'">
				<div class="panel panel-default card-view pa-0">
				<div class="panel-wrapper collapse in">
				<div class="panel-body pa-0">
				<article class="col-item">
				<div class="photo">';
				/*if($stock==0){
					$html.='<a> <img style="width: 125px;height: 125px;" src="'.$url.'" class="img-responsive" alt="Product Image"> </a>';
				}else{
					$html.='<a onclick="agregar_cantidad('.$a.')"> <img style="width: 125px;height: 125px;" src="'.$url.'" class="img-responsive" alt="Product Image"> </a>';
				}*/
				$imagen_error="'".base_url()."public/default.jpg'";
				$html.='<a onclick="agregar_cantidad('.$a.')"> <img
                 onerror="this.src='.$imagen_error.';"
				 style="width: 125px;height: 125px;" src="'.$url.'" class="img-responsive" alt="Product Image"> </a>';
				
				$html.='</div>
				<div class="info">';
				if(strlen($value["producto_descripcion"]) >= 15 ){
					$html.='<h6 class="claseproducto" style="font-size: 20px;line-height:82%;padding-top: 0px;padding-left: 0px;padding-bottom: 0px;padding-right: 0px;border-top-width: 0px;border-left-width: 0px;border-bottom-width: 0px;border-right-width: 0px;margin-left: 0px;margin-right: 0px;">'.substr($value['producto_descripcion'], 0, 120).'</h6>';
				}else{ 
					$html.='<h6 class="claseproducto" style="font-size: 20px;line-height:82%;padding-top: 0px;padding-left: 0px;padding-bottom: 0px;padding-right: 0px;border-top-width: 0px;border-left-width: 0px;border-bottom-width: 0px;border-right-width: 0px;margin-left: 0px;margin-right: 0px;">'.$value["producto_descripcion"].'</h6>';
				}
				$html.='<span style="font-size: 10px;"class="head-font block text-warning font-16">S/.'.$value["producto_precio"].'</span>
				</div>
				</article>
				</div>
				</div>  
				</div>  
				</div>';
				$data[$key]["url"] = $url;
			$data[$key]["stock"] = $stock;

			}
			$contador = 1;
		}else{
			//$html.='<center>
			//<img class="iderror" src="https://www.formassembly.com/images/illustrations/robot-msg-error.png" width="450" />
			//<p style="margin: 0 0 0 0; font-size: 32px; font-weight: 700;">No sé encontraron productos</p>
			//</center> ';
			$contador = 0;
		} 
		$datos["contador"] =$contador;
		// print_r($data);exit();
		$datos["tipo"]=2; 
		$datos["cont"] = $contador;
		$datos["info"] = $data;
		echo json_encode($datos);
	}

	function verificar_stock(){
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$ventas_producto=array();
		parse_str($res["venta_producto"], $ventas_producto);  
		$stock = array();
		$tipo_producto= array();
		$html= "";
		$cont=0; 
		if (isset($ventas_producto["id_productotraido"])>0) {
			foreach ($ventas_producto["id_productotraido"] as $key => $value) {
				$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto,producto.producto_descripcion FROM producto WHERE producto.producto_id=".$value.";")->row();
				if($tipo_producto->producto_id_tipoproducto==3){
					$stock=$this->db->query("SELECT producto.producto_stock as stock FROM producto WHERE producto.producto_id=".$value." and producto.id_sede=".$_COOKIE["id_sede"].";")->row();
				}else{
					$stock=$this->db->query("SELECT detalle_producto_almacen.detalle_stock as stock FROM detalle_producto_almacen INNER JOIN almacenes ON detalle_producto_almacen.detalle_almacen = almacenes.almacen_id WHERE detalle_producto_almacen.detalle_producto=".$value." and almacenes.almacen_estado=1 and almacenes.id_sede=".$_COOKIE["id_sede"].";")->row();

				}
				if($stock->stock<$ventas_producto["cantidadtraido"][$key]){
					$cont= $cont +1;
					$html = $html . $tipo_producto->producto_descripcion." QUEDA EN STOCK ".$stock->stock."<br>";
				}
			}
		}else{
			foreach ($ventas_producto["id_producto"] as $key => $value) {
				$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto,producto.producto_descripcion FROM producto WHERE producto.producto_id=".$value.";")->row();
				if($tipo_producto->producto_id_tipoproducto==3){
					$stock=$this->db->query("SELECT producto.producto_stock as stock FROM producto WHERE producto.producto_id=".$value." and producto.id_sede=".$_COOKIE["id_sede"].";")->row();
				}else{
					$stock=$this->db->query("SELECT detalle_producto_almacen.detalle_stock as stock FROM detalle_producto_almacen INNER JOIN almacenes ON detalle_producto_almacen.detalle_almacen = almacenes.almacen_id WHERE detalle_producto_almacen.detalle_producto=".$value." and almacenes.almacen_estado=1 and almacenes.id_sede=".$_COOKIE["id_sede"].";")->row();
				}
				if($stock->stock<$ventas_producto["cantidad"][$key]){
					$cont= $cont +1;
					$html = $html . $tipo_producto->producto_descripcion." QUEDA EN STOCK ".$stock->stock."<br>";
				}
			}
		}
		
if($cont==0){
				echo json_encode(3);
			}else{
				echo json_encode($html, JSON_UNESCAPED_UNICODE);
			}
		
		
		
	}
	
	function guardarpedido(){ 
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$ventas_producto=array();
		parse_str($res["venta_producto"], $ventas_producto);
		$ventas_total_pagar = '';
		if (isset($ventas_producto["igv"])) {
			$igv = 1;
		}else{
			$igv = 0;
		}
		$id = $ventas_producto["idsilla"];
		$cantidadproductos = count($ventas_producto["id_producto"]);
            $sql="SELECT IF(".$id."=1,1,(mesa.mesa_disponible)) AS band,mesa.mesa_empleado,
				mesa.mesa_empleado,empleados.empleado_nombres,empleados.empleado_apellidos,mesa.mesa_id
				FROM mesa
				LEFT JOIN empleados ON empleados.empleado_id = mesa.mesa_empleado
				where mesa.mesa_id = ".$id." ";
			$estado_ubicacion=$this->db->query($sql)->row(); 
			if (isset($estado_ubicacion->band)) {
				$estadoubicacion =$estado_ubicacion->band; 
				$empleado = $estado_ubicacion->mesa_empleado;
			}else{
				$estadoubicacion = 0; 
			} 
		
		if ($estadoubicacion == 0 ) {			
			$ayuda["accion"] = $this->pedidoconsumoaqui($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,1);
		}else{
			$ayuda["accion"] = $this->pedidoconsumoaqui($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,1);
		/*	if ($estadoubicacion == 1 && ($empleado == $_COOKIE["usuario_id"] || $id==1)) {				
				$ayuda["accion"] = $this->pedidoconsumoaqui($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,1);
			}else{
				$ayuda["accion"] = 3;
				
			}*/
		}

		echo json_encode($ayuda);
		
	}




	public function verificar($datoss, $tipo){
		if(count($datoss)!=0){
			$datos=array();
			if($tipo == "I"){
				$string=$this->arrayAstring($datoss,",");
				$datos["insumo"]=$data=$this->Mantenimiento_m->consulta3("SELECT detalle_insumoanalitica.plan_contable_analitica_codigo FROM detalle_insumoanalitica INNER JOIN insumo ON detalle_insumoanalitica.insumo_id = insumo.insumo_id where insumo.insumo_id in (".$string.")");
				if(count($datoss)==count($datos["insumo"])){
					return true;

				}else{
					return false;
				}
			}else{
				$string=$this->arrayAstring($datoss,",");
				$datos["producto"]=$data=$this->Mantenimiento_m->consulta3("SELECT detalle_insumoanalitica.plan_contable_analitica_codigo FROM detalle_insumoanalitica INNER JOIN producto ON detalle_insumoanalitica.producto_id = producto.producto_id where producto.producto_id in (".$string.")");
				if(count($datoss)==count($datos["producto"])){
					return true;

				}else{
					return false;
				}

			}
		}else{
			return true;
		}

	}
	public function pedidoconsumoaqui($ventas_producto,$ventas_total_pagar,$igv,$cantidadproductos,$id_tipoentrega){
			$contador_tipoproducto=2;
			foreach ($ventas_producto["id_producto"] as $k => $v) {
				$prod_tipo=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id_tipoproducto FROM producto where producto.producto_id_tipoproducto=1 and producto.producto_id=".$v);
				if(count($prod_tipo)>0){
					$contador_tipoproducto=2;
				}
			}
		
		
		$this->db->trans_begin();
		$id_mozo = null;
		if (isset($ventas_producto["id_mozo"])) {
			$id_mozo = $ventas_producto["id_mozo"];
		}else{
			if ($_COOKIE["usuario_perfil"] == 2) {
				$id_mozo = $_COOKIE["usuario_id"];
			}else{
				$id_mozo = $_COOKIE["usuario_id"];
			}
		}
			$pantallaestado =1 ;
		$suma = 0;
		for ($i=0; $i <$cantidadproductos ; $i++) {
			$suma = $suma +  ($ventas_producto["cantidad"][$i]*$ventas_producto["precio"][$i]);
		}
		$id = $ventas_producto["idsilla"];	

			$estado_ubicacion=$this->db->query("SELECT IF(".$id."=1,1,(mesa.mesa_disponible)) as 'mesa_disponible' FROM mesa where mesa.mesa_id =".$id.";")->row();
			$nombre_variable = 'venta_codigomesa';
			$estado_ubicacion = $estado_ubicacion->mesa_disponible;
			if ($estado_ubicacion == 1) {
				$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto FROM venta where venta.venta_idventas =".$ventas_producto["idventa"]." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
				$id_venta = $consulta_venta->venta_idventas;
				$id_mozo =$consulta_venta->venta_codigomozo;
				$suma = $suma + $consulta_venta->venta_monto;				
				$venta_monto = $suma;

			}else{
				$id_mozo = $_COOKIE["usuario_id"];
				$venta_monto = $suma;
			}
		

		if ($estado_ubicacion == 1){
			$ventaupdate = array(
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_codigocliente" =>1,
				"venta_igv_estado" => $igv,
				"venta_monto" => $venta_monto,
				"venta_estadococina"=>$pantallaestado,
				$nombre_variable => $id
			);	

			$this->db->where('venta_idventas',$id_venta);
			$estado=$this->db->update('venta', $ventaupdate);
			$tipo_registro = 2;
		}else{
			$data = array(
				"venta_fechapedido" => time(), 
				"venta_pedidofecha" => date("Y-m-d H:i:s"),
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_codigocliente" =>1,
				"venta_igv_estado" => $igv,
				"venta_estadococina"=>$pantallaestado,
				"venta_monto" => $venta_monto,
				$nombre_variable => $id 
			);	
			
			$this->db->insert('venta', $data);
			$id_venta = $this->db->insert_id();	
 
				$mesa = array(
					'mesa_empleado' => $id_mozo,
					'mesa_disponible' => 1
				);
				$this->db->where('mesa_id',$id);
				$estado=$this->db->update('mesa', $mesa);	
			 $tipo_registro = 2;

		}
		
		$impr = array(
			"detalle_venta_imprimir" => 0
		);
		$this->db->where('id_venta',$id_venta);
		$this->db->update('detalle_venta',$impr);
		$tipo_producto = array();
		$stock_detalle = array();
		for ($i=0; $i <$cantidadproductos ; $i++) {
			$estado_preparado=1;
			//milton
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$ventas_producto["id_producto"][$i].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$ventas_producto["id_producto"][$i]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$ventas_producto["id_producto"][$i]);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$ventas_producto["id_producto"][$i],$id_unidad_medida->detalle_unidad_producto_id,$ventas_producto["cantidad"][$i],$ventas_producto["precio"][$i],2,$tipo_registro);

			}else{
 				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida HERE producto.producto_id = ".$ventas_producto["id_producto"][$i]);
				$this->movimiento_stock_tplatos($ventas_producto["id_producto"][$i],null,$ventas_producto["cantidad"][$i],$ventas_producto["precio"][$i],2,$tipo_registro);				
			}
			//fin milton
			$datos = array(
				"descripcion" => $ventas_producto["comentarioventa"][$i],
				"cantidad" => $ventas_producto["cantidad"][$i],
				"precio" => $ventas_producto["precio"][$i],
				"id_venta" => $id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" => $id_tipoentrega,
				"cod_producto_venta" => $ventas_producto["id_producto"][$i],
				"detalle_estado_preparado"=>$estado_preparado
			);
			
			$this->db->insert('detalle_venta', $datos);
		}
 
		//$this->imprimir_comanda($id_venta);
	 
		//$this->imprimir_comprobante($id_venta);
		/*$data = array(
			"venta_idventas" => $id_venta,
			"sesion_impresora_id" => 84
		);
		$this->db->insert('impresion',$data);	*/
		if ($this->db->trans_status() === FALSE){       
			$this->db->trans_rollback();      
			return false;    
		}else{      
			$this->db->trans_commit();    
			return true;    
		}
	}
	



	public function procesar_venta(){
		$post=file_get_contents("php://input");  
		$res=json_decode($post, true);
		$ventas_producto=array();
		$ventas_pago=array();
		$id_caja=0;
		$monto=0;
		$descripcion_comprobante="";
		$serie="";
		$correlativo="";
		$id_documento="";
		$nuevo_correlativo=0;
		parse_str($res["venta_producto"], $ventas_producto);
		parse_str($res["venta_pago"], $ventas_pago);
		parse_str($res["total_pagar"], $total_pagar);	
		$igv=0;
		$estado_igv="";
		$monto_igv=0;
		$monto_parcial=0;
		$id_mozo = null;		
		$dinero_entrego=$ventas_pago["dinero_entregado"]; 
		if ($ventas_producto["productos_por_vender"]>0) {
			$cantidadproductos = count($ventas_producto["id_producto"]);
		}else{
			$cantidadproductos = 0;
		}
		if (isset($ventas_producto["id_mozo"])) {
			$id_mozo = $ventas_producto["id_mozo"];
		}else{
			if ($_COOKIE["usuario_perfil"] == 2) {
				$id_mozo = $_COOKIE["usuario_id"];
			}else{
				$id_mozo = $_COOKIE["usuario_id"];
			}
		}
			$pantallaestado =1 ;
		$this->db->trans_begin();
		$id = $ventas_producto["idsilla"];
		$idsilla_grupo=$ventas_producto["idsilla"];
			$estado_ubicacion=$this->db->query("SELECT mesa.mesa_disponible FROM mesa where mesa.mesa_id =".$id.";")->row();
			$nombre_variable = 'venta_codigomesa';
			$estado_ubicacion = $estado_ubicacion->mesa_disponible;
			if ($estado_ubicacion == 1) {
				$consulta_venta = $this->db->query("SELECT venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto,venta.venta_estadococina,venta.venta_monto FROM venta where venta.venta_codigomesa =".$id." and venta.venta_estado != 2 and venta.venta_estado != 0;")->row();
				$id_venta = $consulta_venta->venta_idventas;
				$cocina_estado = $consulta_venta->venta_estadococina;
				$id_mozo =$consulta_venta->venta_codigomozo;
				//$suma_venta = ($consulta_venta->venta_monto + $ventas_producto["total_pagaroc"]);
				$suma_venta = $ventas_producto["total_pagaroc"];
				$venta_monto = number_format($suma_venta,2);
			}else{
				$id_mozo = $_COOKIE["usuario_id"];
				$venta_monto = $ventas_producto["total_pagaroc"];
			}
		
		if($ventas_pago["forma_pago"]==1){
			$id_caja=1;
		}else{
			$id_caja=2;
		}
		$total=$venta_monto;
		$id_tipo_comprobante=$ventas_pago["tipo_comprabante"];
		if ($id_tipo_comprobante == 1) {
			$td = 'BVE';
		}else{
			if ($id_tipo_comprobante == 2) {
				$td = 'BFE';
			}else{
				$td = 'SVE';
			}
		}
		$comprobante_detalle=0;
		if(isset($ventas_pago["comprobante_detalle"]))
		{
			$comprobante_detalle=1;
		}
		//$monto=$total;
		$formapago=$ventas_pago["forma_pago"];
		$concepto=1;
		$descripcion="COBRO DE VENTA DE PRODUCTOS";
		$tipomovimiento=1;
		$id_movimiento=null;
		$id_movimiento_transporte=0;
		$monto = $venta_monto;
		if(isset($_POST["igv"])){
			$igv=(float)$_POST["igv"];
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;
			
		}else{
			$igv=0;
			$estado_igv="";
			$monto_igv=0;
			$monto_parcial=0;
			
		}
		if($igv==0){
			$estado_igv="0";
			$monto_igv=0;
			$monto_parcial=$monto;
			$idigv = 8;
		}else{
			$estado_igv="1";
			$sumaigv = (1+$igv);
			$monto_parcial=round(($monto/$sumaigv),2);
			$monto_igv=round(($monto-$monto_parcial),2);
			$idigv = 1;
		}
		if($ventas_pago["tipo_pago"]==1){
			$id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,$descripcion_comprobante);
			if($id_movimiento==0){
				echo 2;exit();
			}else{
				$id_movimiento;
			}
		}
		if($id_tipo_comprobante!=0){
			$dat=$this->Mantenimiento_m->consulta3("SELECT * FROM detalle_doc_sede INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
				INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=".$ventas_pago["tipo_comprabante"]);
			$descripcion_comprobante=$dat[0]["doc_serie"]."-".$dat[0]["doc_correlativo"];
			$serie=$dat[0]["doc_serie"];
			$correlativo=$dat[0]["doc_correlativo"];
			$id_documento=$dat[0]["id_documento"];
			$nuevo_correlativo=(int)$correlativo+1;
			$datos=array(
				"doc_correlativo"=>$nuevo_correlativo
			);
			$this->Mantenimiento_m->actualizar("documento",$datos,$id_documento,"id_documento");
			$data=array(
				"tipo_comprobante_descripcion"=>$descripcion_comprobante
			);
			$this->db->where('mov_id',$id_movimiento);
			$estado=$this->db->update('movimiento', $data);
		}
		$fechaactual = date("Y-m-d H:i:s");
            
		$id_cliente=$ventas_pago["cliente"];
		$array_cliente = explode("/", $id_cliente);
		if (isset($id_venta)){
			$ventaupdate = array(
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_monto" => $monto,
				"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"],
				"venta_tipopago" => $ventas_pago["tipo_pago"],
				"venta_formapago" => $ventas_pago["forma_pago"],
				'venta_num_serie' =>$serie,
				'venta_num_documento' => $correlativo,
				"venta_idmoneda"=>1,
				"venta_codigocliente" => $ventas_pago["cliente"],
				"venta_idmovimiento" => $id_movimiento,
				"venta_credito_usuario" => $_COOKIE["usuario_id"],
				"venta_fecha_pago"=>$fechaactual,
				"venta_monto_entregado"=>$dinero_entrego,
				"venta_igv_estado"=>$estado_igv	,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_estado_pagado" => 1,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_estadococina"=>$pantallaestado,
				"venta_estado" => 2,
				"ventaid_tipo_venta" => $idigv,
				$nombre_variable => $id,
						"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcion"],
				"venta_id_cajero"=> $_COOKIE["usuario_id"],
			);
			$this->db->where('venta_idventas',$id_venta);
			$this->db->update('venta', $ventaupdate);
			$tipo_registro = 1;
		}else{
			$ventainsert = array(
				"venta_fechapedido" => time(),
				"venta_pedidofecha" => date("Y-m-d H:i:s"),
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_monto" => $monto,
				"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"],
				"venta_tipopago" => $ventas_pago["tipo_pago"],
				"venta_formapago" => $ventas_pago["forma_pago"],
				'venta_num_serie' =>$serie,
				"venta_estado_pagado" => 1,
				"venta_monto_entregado"=>$dinero_entrego,
				'venta_num_documento' => $correlativo,
				"venta_idmoneda"=>1,
				"venta_codigocliente" => $ventas_pago["cliente"],
				"venta_idmovimiento" => $id_movimiento,
				"venta_credito_usuario" => $_COOKIE["usuario_id"],
				"venta_fecha_pago"=>$fechaactual,
				"venta_estadococina"=>$pantallaestado,
				"venta_igv_estado"=>$estado_igv	,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_estado" => 2,
				"ventaid_tipo_venta" => $idigv,
				$nombre_variable => $id,
						"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcion"],
				"venta_id_cajero"=> $_COOKIE["usuario_id"],
			);
			$this->db->insert('venta', $ventainsert);
			$id_venta = $this->db->insert_id();	
			$tipo_registro = 1;
		}

			$mesa = array(
				'mesa_empleado' => $id_mozo,
				'mesa_disponible' => 0
			);
			$this->db->where('mesa_id',$id);
			$estado=$this->db->update('mesa', $mesa);
		
		//$sql = "UPDATE detalle_venta set detalle_venta_imprimir where id_venta =".$id_venta;
		//$this->db->query($ql);
		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
			detalle_venta.precio,detalle_venta.cantidad
			FROM detalle_venta
			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_venta=".$id_venta." and detalle_venta.estado_pedido<>0");

		foreach ($detalle_venta as $key => $value){		
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$value["cod_producto_venta"].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){	
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$value["cod_producto_venta"]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$value["cod_producto_venta"],$id_unidad_medida->detalle_unidad_producto_id,$value["cantidad"],$value["precio"],2,3,$serie,$correlativo,$ventas_pago["tipo_comprabante"]);
			}else{ 
				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$value["cod_producto_venta"]);
				$this->movimiento_stock_tplatos($value["cod_producto_venta"],null,$value["cantidad"],$value["precio"],2,$tipo_registro);
			}


			//$this->venta_actualizacion_stock($value["cod_producto_venta"],$value["cantidad"],$value["precio"],$serie ,$correlativo,$id_tipo_comprobante);
		}

		for ($i=0; $i <$cantidadproductos ; $i++) { 
			$tipo_producto=$this->db->query("SELECT producto.producto_id_tipoproducto, producto.producto_stock,producto.producto_stock_temporal FROM producto WHERE producto.producto_id=".$ventas_producto["id_producto"][$i].";")->row();
			if($tipo_producto->producto_id_tipoproducto==1){	
				$almacen_id = $this->Mantenimiento_m->consulta2("SELECT almacen_id FROM almacenes where almacenes.id_sede = ".$_COOKIE["id_sede"]." and almacen_uso = 1 ");
				$id_almacen=$this->Mantenimiento_m->consulta2("SELECT detalle_producto_almacen.detalle_almacen as id_almacen FROM detalle_producto_almacen WHERE detalle_producto_almacen.detalle_producto=".$ventas_producto["id_producto"][$i]." AND detalle_producto_almacen.detalle_almacen = ".$almacen_id->almacen_id.";");
 				$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida
 					INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida 
 					WHERE producto.producto_id = ".$ventas_producto["id_producto"][$i]);
				$this->movimiento_stock_temporal($id_almacen->id_almacen,$ventas_producto["id_producto"][$i],$id_unidad_medida->detalle_unidad_producto_id,$ventas_producto["cantidad"][$i],$ventas_producto["precio"][$i],2,$tipo_registro,$serie,$correlativo,$ventas_pago["tipo_comprabante"]);
			}else{ 
				//$id_unidad_medida = $this->Mantenimiento_m->consulta2("SELECT detalle_unidad_producto.detalle_unidad_producto_id FROM producto INNER JOIN unidad_medida ON producto.unidad_medida_id = unidad_medida.id_unidad_medida INNER JOIN detalle_unidad_producto ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida WHERE producto.producto_id = ".$ventas_producto["id_producto"][$i]);
				$this->movimiento_stock_tplatos($ventas_producto["id_producto"][$i],null,$ventas_producto["cantidad"][$i],$ventas_producto["precio"][$i],2,$tipo_registro);
			}
			$datos = array(
				"descripcion" => $ventas_producto["comentarioventa"][$i],
				"cantidad" => $ventas_producto["cantidad"][$i],
				"precio" => $ventas_producto["precio"][$i],
				"id_venta" => $id_venta,
				"fecha_preparar" => date("Y/m/d"),
				"id_tipoentrega" => 1,
				"cod_producto_venta" => $ventas_producto["id_producto"][$i]
			);
			$this->db->insert('detalle_venta', $datos);
			$iddetalleventa = $this->db->insert_id();	

		}

		if($ventas_pago["tipo_pago"]==2){
			$dato=array(
				"venta_credito_estado"=>1,
				"venta_credito_cuotas"=> $ventas_pago["cuotas"]
			);
			$this->db->where('venta_idventas',$id_venta);
			$estado=$this->db->update('venta', $dato);
			foreach ($ventas_pago["nrocuotas"] as $key => $value) {
				$datos=array(
					"cuo_venta"=> $id_venta,
					"cuo_ventanrocuota"=>$value,
					"cuo_ventafechavence"=>$ventas_pago["fechavence"][$key] ,
					"cuo_ventamontocuota"=>$ventas_pago["montocuota"][$key]  ,
					"cuo_ventamontopagado"=>0,
				);
				$this->Mantenimiento_m->insertar("cuotaventa", $datos);
			}
			
		}
		//$this->relacion_cuentas($id_venta,14);

		//$this->llamardescuento($id_venta,$serie,$correlativo);
		   // actualizar stock y kardex
//		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.id_detalle_venta, detalle_venta.cod_producto_venta,producto.producto_id_tipoproducto,
//			IF(producto.producto_id_tipoproducto = 1 ,detalle_venta.precio ,producto.producto_preciocompra) AS precio,detalle_venta.cantidad
//			FROM detalle_venta
//			INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  where id_venta=".$id_venta." and detalle_venta.estado_pedido<>0");
//		foreach ($detalle_venta as $key => $value){
			//pagos del mozo
//			if(!empty($res["identificador"])){
		
//			}else{
				
//			}
//		}


		if ($this->db->trans_status() === FALSE){
        	$this->db->trans_rollback();
 			$enviados["estado"] = 99;
		}else{
        	$this->db->trans_commit();
        	$enviados["estado"] = 1;
		}

		//if ($this->estado_configuracion(4) == 1) {
			//$this->imprimir_comanda($id_venta);
		//}
		//$this->imprimir_comprobante($id_venta);
	//	$this->imprimir_comprobante($id_venta);

		//$this->relacion_cuentas($id_venta,14);
		//if ($this->db->trans_status() === FALSE){     
		//	$this->db->trans_rollback();      
		//	return false;    
		//}else{    
		//ob_start();
		//$this->generar_qr($id_venta);
			//$this->facturacion_electronica($id_venta);
		//ob_end_clean();
		 $this->anular_agrupacion_mesas($idsilla_grupo);
          if((int)$id_tipo_comprobante!=6){

		 $this->facturacion_electronica($id_venta);

          }
		

		$enviados["idventa"] = $id_venta;
		echo json_encode($enviados);   
		//$this->db->trans_commit();    
		//	return true;    
		//}  
	}

	public function buscar_tabla(){

		$columns = array( 
			0 =>'cliente', 
			1 =>'mozo',
			2=> 'mesa',
			3=> 'correlativo',
			4=> 'monto',
			5=> 'fecha',

			6=>'acciones',
		);
		$limit = $this->input->post('length');
		$start = $this->input->post('start');
		$order = $columns[$this->input->post('order')[0]['column']];
		$dir = $this->input->post('order')[0]['dir'];
		$d=$this->input->post('order')[0]["column"];
	

			$sql1="SELECT * FROM venta
			INNER JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id
			INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
			INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
			where venta.venta_estado=2 and venta.venta_idsede=".$_COOKIE["id_sede"]." and venta.venta_estado_pagado != 0 order by 
			venta.venta_fecha_pago desc";
		



		$lista=$this->db->query($sql1)->result();
		$formar_order="";
		if( $d==0)
		{
			$formar_order="venta.venta_fecha_pago";
		}
		if( $d==1)
		{
			$formar_order="venta.venta_fecha_pago";

		}
		if( $d==2)
		{
			//$formar_order="mesa.mesa_TableName";

		}

		if( $d==2)
		{
			$formar_order="venta.venta_fecha_pago";

		}
		if( $d==3){
			$formar_order="venta.venta_fecha_pago";
		}
		if( $d==4){
			$formar_order="venta.venta_fecha_pago";
		}
		if( $d==5){
			$formar_order="venta.venta_fecha_pago";
		}





		$totalFiltered = count($lista); 
		$totalData = count($lista); 




		$data;
		if(empty($this->input->post('search')['value']))
        { // cuando no existe buscar
        	
        		$sql="SELECT * FROM venta
        		INNER JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id
        		INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
        		INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
        		where venta.venta_estado=2 and venta.venta_idsede=".$_COOKIE["id_sede"]." and venta.venta_estado_pagado != 0 
        		ORDER BY ".$formar_order." desc limit ".$start.",".$limit;
        	
        	
        	$lista_datos=$this->db->query($sql);

        	if($lista_datos->num_rows()>0)
        	{
        		$data= $lista_datos->result(); 
        	}
        	else
        	{
        		$data= null;
        	}
        }
        else
        {
          // cuando  existe buscar
        	$search = $this->input->post('search')['value']; 
        
        		$sql="SELECT * FROM venta
        		INNER JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id
        		INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
        		INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
        		where venta.venta_estado=2  and venta.venta_estado_pagado != 0 and (empleados.empleado_nombres like '%". $search."%' or clientes.cliente_nombres like '%". $search."%' or (CONCAT(venta.venta_num_serie,'-',venta.venta_num_documento) like '%".$search."%'  )  ) and venta.venta_idsede=".$_COOKIE["id_sede"]."";
        		$dat=$this->db->query($sql);
        		$sql="SELECT * FROM venta        	
        		INNER JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id
        		INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
        		INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
        		where venta.venta_estado=2 and venta.venta_estado_pagado != 0 and (empleados.empleado_nombres like '%". $search."%' or clientes.cliente_nombres like '%". $search."%' or  (CONCAT(venta.venta_num_serie,'-',venta.venta_num_documento) like '%".$search."%'  )  ) and venta.venta_idsede=".$_COOKIE["id_sede"]."
        		ORDER BY ".$formar_order." desc limit ".$start.",".$limit;        	
        		$lista_datos=$this->db->query($sql);
        	
        	if($lista_datos->num_rows()>0)
        	{
        		$data= $lista_datos->result(); 
        		$totalFiltered = $dat->num_rows();
        	}
        	else
        	{
        		$data= null;
        		$totalFiltered = 0;
        	}



        }


        $tabla = "'ventas'";
        $data1 = array();
        if(!empty($data)){
        	foreach ($data as $post){
        		$html1="";
        		$nestedData['cliente'] = $post->cliente_nombres;
        		$nestedData['mozo'] = $post->empleado_nombres;
        		//$nestedData['mesa'] = $post->mesa_TableName;
        		$nestedData['correlativo'] = $post->venta_num_serie.'-'.$post->venta_num_documento;
        		$nestedData['monto'] =$post->venta_monto;
        		$nestedData['fecha'] =$post->venta_fecha_pago;

        		if ($post->venta_estado_pagado == 2) {
        			$nestedData['acciones'] ='<span class="badge badge-danger">Eliminado</span>';
        			/*$nestedData['acciones'] ='<td> 
        			<a href="'.base_url().'ventas/ver_ventas/'.$post->venta_idventas.'" class="text-inverse" title="Ver Ventas" data-toggle="tooltip">
        			<i class="fa fa-file-text txt-primary"></i>
        			</a>
        			<a href="'.base_url().'pedido/mostrar_comprobante/'.$post->venta_idventas.'" class="text-inverse" title="Ver Nota de Credito" data-toggle="tooltip">
        			<i class="fa fa-file-pdf-o txt-warning"></i>
        			</a>
        			</td>';*/
        		}
        		if ($post->venta_estado_pagado == 1) {

        					$pdf="'".$post->venta_pdf_facturacion."'";
        			$xml="'".$post->venta_xml_facturacion."'";

        			$nestedData['acciones'] ='<td>

	<a href="#" class="text-inverse" title="PDF" onclick="descargar_pdf('.$pdf.')" data-toggle="tooltip">
        				<i class="fa fa-file-text txt-primary"></i>
        			</a>
        				<a href="#" class="text-inverse" title="XML" onclick="descargar_pdf('.$xml.')" data-toggle="tooltip">
        				<i class="fa fa-file-archive-o" aria-hidden="true"></i>
        			</a>

        			
        				<a href="#" class="text-inverse" title="ENVIAR CORREO" onclick="enviar_correo('.$post->venta_idventas.')" data-toggle="tooltip">
        			<i class="fa fa-envelope" aria-hidden="true"></i>
        			</a>



        				<a target="_blank" href="'.base_url().'Ventas/mostrar_comprobante/'.$post->venta_idventas.'" class="text-inverse" title="Ver Comprobante" data-toggle="tooltip">
        			<i class="fa fa-file-text txt-primary"></i>
        			</a>
        			<a style="cursor:pointer" onclick="eliminarventa('.$tabla.','.$post->venta_idventas.')" class="text-inverse" title="Eliminar" data-toggle="tooltip">
        			<i style="color:red;" class="fa fa-trash txt-danger"></i>
        			</a> 
        			<a href="'.base_url().'ventas/ver_ventas/'.$post->venta_idventas.'" class="text-inverse" title="Ver Ventas" data-toggle="tooltip">
        			<i class="fa fa-file-text txt-primary"></i>
        			</a>
        			<a href="'.base_url().'pedido/mostrar_comprobante/'.$post->venta_idventas.'" class="text-inverse" title="Ver Comprobante" data-toggle="tooltip">
        			<i class="fa fa-file-pdf-otxt-info"></i>
        			</a>
        			</td>';
        			
        		}
        		

        		$html="";

        		$data1[] = $nestedData;

        	}

        }

        $json_data = array(
        	"draw"            => intval($this->input->post('draw')),  
        	"recordsTotal"    => intval($totalData),  
        	"recordsFiltered" => intval($totalFiltered), 
        	"data"            => $data1   
        );

        echo json_encode($json_data); 
    }

    public function llamardescuento($id,$serie,$correlativo){

    	$data=$this->Mantenimiento_m->consulta3("SELECT producto.producto_id,detalle_venta.cantidad FROM 	venta
    		INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
    		INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id
    		WHERE venta.venta_idsede=".$_COOKIE["id_sede"]." and venta.venta_estado = 2 and producto.producto_id_tipoproducto = 1 and venta_idventas=".$id);
    	foreach ($data as $key => $value) {
    		$id_producto =$value["producto_id"] ;
    		$cantidad = $value["cantidad"];
    		$this->actualizar_stock_insumo_salida($id_producto,$cantidad,$serie,$correlativo);
    	}
    }


    public function ver_ventas($id){
    	$data=array();
    
    		$dat=$this->Mantenimiento_m->consulta3("SELECT
    			mesa.mesa_id AS identificador,
    			mesa.mesa_numero AS ubicacion,
    			CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre_mesera,
    			movimiento.mov_monto,
    			movimiento.mov_fecha as fecha,DAY(movimiento.mov_fecha) as dia,MONTH(movimiento.mov_fecha) as mes,YEAR(movimiento.mov_fecha) as anio,
    			movimiento.mov_hora
    			FROM
    			venta
    			INNER JOIN mesa ON venta.venta_codigomesa = mesa.mesa_id
    			INNER JOIN empleados ON venta.venta_codigomozo = empleados.empleado_id
    			INNER JOIN movimiento ON venta.venta_idmovimiento = movimiento.mov_id
    			where venta_idventas=".$id);
    	

    	$data["venta"] = $dat;
    	$data["titulo_descripcion"]="Lista de la Venta" ; 
    	$data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT detalle_venta.cantidad,detalle_venta.precio,detalle_venta.fecha_preparar,producto.producto_descripcion
    		FROM venta
    		INNER JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
    		INNER JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id 
    		where venta.venta_idventas=".$id." ;");
    	$this->vista("Ventas/verventas",$data);
    }

    public function  actualizar_stock_insumo_salida($id_producto,$cantidad,$serie,$correlativo){
    	$dat=$this->Mantenimiento_m->consulta3("select *  from producto_insumo where producto_id=".$id_producto);
    	$sede=$this->Mantenimiento_m->consulta3("select * from producto where producto_id=".$id_producto);
    	$id_sede=$sede[0]["id_sede"];
    	$almacen=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$id_sede);
    	$primer_almacen=$almacen[0]["almacen_id"];
    	$tipo = 2;
    	$descripcion_kardex = "SALIDA POR MOTIVO DE PREPARACION PLATO";
    	foreach ($dat as $key => $value)      {
    		$id_insumoproducto = $value["insumo_id"];
    		$id_unidad_medida_detalle=$value["id_unidad_medida"];
    		$cantidad_insumo_detalle=(float)$value["detalle_producto_insumo_cantidad"];
    		$cantidad_total=$cantidad_insumo_detalle*(float)$cantidad;
    		$costo=$this->Mantenimiento_m->consulta3("select ROUND(avg(detalle_compras.dc_preciounitatio),2) as precio from detalle_compras where detalle_compras.producto_id=".$value["insumo_id"]);
    		$insumoprecio = $this->Mantenimiento_m->consulta3("SELECT insumo.insumo_precio_compra FROM insumo where insumo_id=".$value["insumo_id"]);
    		if (isset($costo[0]["precio"])) {
    			$costo_total=(float)$costo[0]["precio"]*(float)$cantidad;
    		}else{
    			$costo_total = $insumoprecio[0]["insumo_precio_compra"];
    		}       
    		$this->actualizar_stock_insumo($id_insumoproducto,$cantidad_total,$primer_almacen,$id_unidad_medida_detalle,$costo_total,$tipo,$descripcion_kardex,$serie,$correlativo,$costo[0]["precio"]);
    	}


    }
    public function dar_baja_factura($id_venta)
    {

    	$url = 'http://grupoesconsultores.com/facturador/Declaracion/facturacion/';

    	$venta=$this->Mantenimiento_m->consulta3("select * from venta where venta_idventas=".$id_venta);
//----------------------------------------------------------------------------------------------------------------------------------
//API key
    	$apiKey = 'aIOWopWbl6xrZmQ.';



			//Auth credentials
			//obligatorio
    	$tipo_documento=$venta[0]["ventas_idtipodocumento"];
    	$codigo_documento="";
    	$json=array();
    	if($tipo_documento=="1")
    	{
    		$codigo_documento="03";

    		$json["status"]=false;
    		echo json_encode($json);
    		exit();
    	}
    	if($tipo_documento=="2")
    	{
    		$codigo_documento="01";
    	}
    	if($tipo_documento=="3")
    	{
    		$codigo_documento="";
    		$json["status"]=false;
    		echo json_encode($json);
    		exit();
    	}
    	if($tipo_documento=="4")
    	{
    		$codigo_documento="";
    		$json["status"]=false;
    		echo json_encode($json);
    		exit();
    	}

    	$datos["apikey"]=$apiKey;
    	$datos["serie"]=$venta[0]["venta_num_serie"];
    	$datos["correlativo"]=$venta[0]["venta_num_documento"];
    	$datos["codigo_comprobante"]=$codigo_documento;
    	$datos["fecha_emision"]=$venta[0]["venta_fecha_pago"];



    	$data=json_encode($datos);
//create a new cURL resource
    	$ch = curl_init($url);

    	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-API-KEY: " . $apiKey));

    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

    	echo $result = curl_exec($ch);
    }




	public function mostrar_comprobante($id){
		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa");
		$venta=$this->Mantenimiento_m->consulta3("
		SELECT * 
		from 
		membresia,
		venta,formapago  
		where venta.venta_formapago=formapago.for_id and membresia.membresia_idventa=venta.venta_idventas and venta.venta_idventas=".$id);
		$detalle_venta=$this->Mantenimiento_m->consulta3("SELECT *,
		m.membresia_meses as cantidad, m.membresia_precio_mes as precio,
		m.membresia_precio_mes as producto_precio,
		m.membresia_precio_mes as venta_monto_sinigv,
		tm.tipo_membresia_descripcion as producto_descripcion, '1' as venta_estado_consumo FROM  membresia m
			INNER JOIN tipo_membresia as tm ON m.tipo_membresia_id = tm.tipo_membresia_id
			where m.membresia_idventa =".$id);
		$total=0;
		foreach ($detalle_venta as $key => $val) {
			$total+=(float)$val["cantidad"]*$val["producto_precio"];
		}

		$dat=intval($total); 
		$fl=$total-$dat;
		$flotante=(string)(round($fl*100));

		$vendedor=$this->Mantenimiento_m->consulta3("select * from venta,empleados where venta.venta_codigomozo=empleados.empleado_id and venta.venta_idventas=  ".$id);

		$cliente=$this->Mantenimiento_m->consulta3("select * from venta,clientes where venta.venta_codigocliente=clientes.cliente_id and venta.venta_idventas=".$id);
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=1");
		$estado_mesa="";


		$delivery=$this->db->query("select * from datos_delivery where id_venta=".$id)->result_array();

		$letras= NumeroALetras::convertir($dat);
		$this->load->view("Comprobante/boleta",compact("sede","estado_mesa","id","empresa","venta","detalle_venta","letras","flotante","vendedor","cliente","delivery"));  

	}




    public function facturacion($id)

    {

     //  $data_token = json_decode($this->consultar_token(),true);

 // $response=array();

  $postdata = file_get_contents("php://input");

  $request = json_decode($postdata,true); 

  $response=array();

$ruta = 'http://facturacion.selvafood.com/api/documents';
//produccion
$token = '8lqV6Y8G0eSwG5G0hgthgB8CD1g4N2W5xdVYUoWodrqreh82z9';
//prueba
//$token='8lqV6Y8G0eSwG5G0hgthgB8CD1g4N2W5xdVYUoWodrqreh82z9';


   $sql="SELECT
venta.venta_idventas as 'id',
venta.venta_num_serie  as 'serie',
venta.venta_num_documento as 'documento',
DATE_FORMAT(venta.venta_fecha_pago,'%Y-%m-%d') as 'fecha',
TIME(venta.venta_fecha_pago) as 'hora',
if(ventas_idtipodocumento=1,'01','03') as 'codigo_documento',
venta.venta_documento_descripcion as 'documento_dni',
if(LENGTH(venta.venta_documento_descripcion)=8,'1','6') as 'tam_dni',
venta.venta_direccion_descripcion as 'direccion',
clientes.cliente_telefono as 'telefono',
venta.venta_nombre_descripcion as 'cliente',
clientes.cliente_email as 'correo',
if(venta.venta_cdr_facturacion IS NULL ,'',venta.venta_cdr_facturacion) as 'cdr',
if(venta.venta_xml_facturacion IS NULL ,'',venta.venta_xml_facturacion) as  'xml',
if(venta.venta_pdf_facturacion IS NULL ,'',venta.venta_pdf_facturacion) as 'pdf',
venta.*,
empleados.*
FROM
venta
LEFT JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id
INNER JOIN empleados ON empleados.empleado_id=venta.venta_id_cajero
where venta.venta_idventas=".$id;
//echo $sql;



        $venta=$this->db->query($sql)->row_array();

















        $detalle=array();

        $sql="select 
producto.producto_descripcion as 'descrip',
detalle_venta.*


from detalle_venta 
INNER JOIN producto ON detalle_venta.cod_producto_venta=producto.producto_id
where detalle_venta.estado_pedido=1 and id_venta=".$id;

        $rec=$this->db->query($sql)->result_array();

        foreach ($rec as $key => $value) {

           $detalle[$key]=array(

              "codigo_interno"=>  "P".$value["id_detalle_venta"],

              "descripcion"=> $value["descrip"],

              "codigo_producto_sunat"=>  "51121703",

              "unidad_de_medida"=>  "NIU",

              "cantidad"=>  $value["cantidad"],

              "valor_unitario"=>  $value["precio"],

              "codigo_tipo_precio"=>  "01",

              "precio_unitario"=>  $value["precio"],

              "codigo_tipo_afectacion_igv"=> "20",

              "total_base_igv"=>  (double)$value["cantidad"]*(double)$value["precio"],

              "porcentaje_igv"=>  18,

              "total_igv"=>  0,

              "total_impuestos"=>  0,

              "total_valor_item"=>  $value["cantidad"]*$value["precio"],

              "total_item"=>  $value["cantidad"]*$value["precio"],

           );

        }



      $empresa=$this->db->query("select * from empresa")->row_array();

$data = array(

   "serie_documento"=> $venta["serie"],

  "numero_documento"=>$venta["documento"],

  "fecha_de_emision"=>  $venta["fecha"],

  "hora_de_emision"=>  $venta["hora"],

  "codigo_tipo_operacion"=>  "0101",

  "codigo_tipo_documento"=>$venta["codigo_documento"],

  "codigo_tipo_moneda"=>  "PEN",

  "fecha_de_vencimiento"=>  $venta["fecha"],

  "numero_orden_de_compra"=>  $venta["id"],

  "datos_del_emisor"=>  array(

    "codigo_pais"=>  "PE",

    "ubigeo"=>  "220901",

    "direccion"=>  $empresa["empresa_direccion"],

    "correo_electronico"=>  $empresa["empresa_correo"],

    "telefono"=> $empresa["empresa_telefono"],

    "codigo_del_domicilio_fiscal"=>  "0000"

  ),  

  "datos_del_cliente_o_receptor"=>array(

    "codigo_tipo_documento_identidad"=> $venta["tam_dni"],

    "numero_documento"=> $venta["documento_dni"],

    "apellidos_y_nombres_o_razon_social"=>$venta["cliente"],

    "codigo_pais"=> "PE",

    "ubigeo"=>"220901",

    "direccion"=> $venta["direccion"],

    "correo_electronico"=>$venta["correo"],

    "telefono"=>"933122626"

  ), "totales"=>array(

    "total_exportacion"=> 0.00,

    "total_operaciones_gravadas"=> 0.00,

    "total_operaciones_inafectas"=> 0.00,

    "total_operaciones_exoneradas"=>$venta["venta_monto"],

    "total_operaciones_gratuitas"=> 0.00,

    "total_igv"=>0.00,

    "total_impuestos"=> 0.00,

    "total_valor"=> $venta["venta_monto"],

    "total_venta"=> $venta["venta_monto"]

  )

,



   "items"=>$detalle,

   "informacion_adicional"=> "OBSERVACIÓN: ".$venta["venta_observaciones"]."|EMITIDO POR:|".$venta["empleado_nombres"]."|".$venta["empleado_usuario"]

);





//print_r($data);exit();

$data_json = json_encode($data);



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



curl_close($curl);



if ($err) {

  echo "cURL Error #:" . $err;

} else {

   $json=json_decode($response,true);

  //print_r($json);exit();





     $filename = $json["links"]["pdf"]; 

   // $origen   = fopen($filename, 'r');

    //$nombre_pdf=$this->generar_token_seguro(30);

   // $destino1 = fopen('public/factura/'.$nombre_pdf.'.pdf', 'w');



     //stream_copy_to_stream($origen, $destino1); 





   $data=array(

       "venta_pdf_facturacion"=>$json["links"]["pdf"],

       "venta_xml_facturacion"=>$json["links"]["xml"],

       "venta_cdr_facturacion"=>$json["links"]["cdr"],

      // "venta_ticket_facturacion"=>$nombre_pdf,

       "venta_ticket_external_id"=>$json["data"]["external_id"],
       "venta_qr"=>$json["data"]["qr"],

       "venta_hash"=>$json["data"]["hash"],
       "venta_number_to_letter"=>$json["data"]["number_to_letter"]


   );



     $this->db->where("venta_idventas",$id);

     $r = $this->db->update("venta",$data);
//print_r($json);exit();



     return $json["links"]["pdf"];


  // echo json_encode($json);exit();

}







    }











public function ws_enviar_factura()
  {


 // $response=array();

  $postdata = file_get_contents("php://input");

  $request = json_decode($postdata,true); 
  $id=$request["id"];
  $correo=$request["correo"];
  $this->enviar_factura($id,$correo);

  
  }

 public function enviar_factura($id_venta,$correo)
  {

 $mail = new PHPMailer(true); 
      $dat=array();  

     /* $mail->Host = "mail.capsanmartin.org.pe";
      $mail->SMTPAuth = true; // enable SMTP authentication
      $mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent

      $mail->Port = 587; // set the SMTP port for the GMAIL server
      $mail->Username = "mesadepartes@capsanmartin.org.pe"; // SMTP account username
      $mail->Password = "cap2020*"; // SMTP account password
      $mail->SetFrom('mesadepartes@capsanmartin.org.pe', 'COMPROBANTE DE PAGO');
      $mail->AddReplyTo('selvafood.jjingenieros@gmail.com', '');*/



       $mail->IsSMTP();
      $mail->Host = "mail.jjingenieros.pe";
      //$mail->SMTPDebug  = 0 ;  
      $mail->SMTPAuth   = TRUE;
      $mail->SMTPSecure = "tls";
      $mail->Port       = 587;// set the SMTP port for the GMAIL server
      $mail->Username = "ventas@jjingenieros.pe"; // SMTP account username
      $mail->Password = "Sanchez75270586@"; // SMTP account password
      $mail->SetFrom('ventas@jjingenieros.pe', 'COMPROBANTE DE PAGO');
      $mail->AddReplyTo('ventas@jjingenieros.pe', '');
      
      $venta=$this->db->query("SELECT * from venta where venta_idventas=".$id_venta)->row_array();
     // $archivo="public/factura/".$venta["venta_ticket_facturacion"].".pdf";
      $url=$venta["venta_pdf_facturacion"];
      $url1=$venta["venta_xml_facturacion"];

      $mail->AddAddress($correo); 
    //   $mail->AddAttachment( 'certificado.pdf');
       
      
      $mail->Subject='Envio de Comprobante de pago';//en espass cambiar
      $mail->AltBody='Comprobante';
      $mail->Wordwrap=50;//numero de lineas
      $mail->MsgHTML('<html></html>');//formato html para el mensaje
      $mail->addStringAttachment(file_get_contents($url), 'factura.pdf');
      $mail->addStringAttachment(file_get_contents($url1), 'factura.xml');

       
      if($mail->send()){//si se envio el correo,enviamos el correo
          $respuesta="el mensaje ha sido enviado satisfactoriamente";
          $msg['ok'] = true;
         //para borrar el archivo del servidor
      }else{
        $msg['ok'] = false;
        $respuesta="ERROR:".$mail->ErrorInfo."<br>Ocurrio un error, vuelva a intentarlo porfavor.";
        
      } 



//Sanchez75270586@
      
      echo json_encode ($msg);
   

  }




























    

}
?>


