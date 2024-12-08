<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once  (APPPATH.'libraries/vendor/autoload.php');
class HSesion_caja extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index(){
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Sesión Caja"; 
	  $this->vista("HSesion_caja/index",$data);
	}

	public function buscar_tabla(){
		$columns = array( 
		0 =>'mozo',  
		1=> 'tipocaja',
		2=> 'monto',
		3=> 'fecha', 
		4=>'acciones',
		);
		$limit = $this->input->post('length');
		$start = $this->input->post('start');
		$order = $columns[$this->input->post('order')[0]['column']];
		$dir = $this->input->post('order')[0]['dir'];
		if ($dir == 'asc') {
			$dir = 'desc';
		}else{
			$dir = 'asc';
		}
		$d=$this->input->post('order')[0]["column"];
 		$sql1 = "SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
		sesion_caja.ses_montoinicial,
		sesion_caja.id_sesion_caja,
		sesion_caja.ses_fechaapertura,
		IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
		FROM
		sesion_caja
		INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
		INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
		LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
		WHERE sede_caja.id_sede = ".$_COOKIE["id_sede"]." and ses_estado = 0 and sede_caja.id_caja = 1  
		GROUP BY id_sesion_caja
		ORDER BY id_sesion_caja ASC"; 

		$sql2 = "SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
		sesion_caja.ses_montoinicial,
		sesion_caja.id_sesion_caja,
		sesion_caja.ses_fechaapertura,
		IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
		FROM
		sesion_caja
		INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
		INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
		LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
		WHERE sede_caja.id_sede = ".$_COOKIE["id_sede"]." and ses_estado = 0 and sede_caja.id_caja = 2
		GROUP BY id_sesion_caja 
		ORDER BY id_sesion_caja ASC";	
 


		$lista=$this->db->query($sql1)->result();
		$formar_order="";
		if( $d==0){
			$formar_order="id_sesion_caja";
		}
		if( $d==1)
		{
			$formar_order="id_sesion_caja";

		}
		if( $d==2){
			$formar_order="id_sesion_caja";
		}

		if( $d==3){
			$formar_order="id_sesion_caja";

		} 
		$totalFiltered = count($lista); 
		$totalData = count($lista); 




		$data;
		if(empty($this->input->post('search')['value'])){ // cuando no existe buscar 
        		$sql="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
				WHERE sede_caja.id_sede = ".$_COOKIE["id_sede"]." and ses_estado = 0  and sede_caja.id_caja = 1 
				GROUP BY id_sesion_caja
        		ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;

        		$sqlm="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
				WHERE sede_caja.id_sede = ".$_COOKIE["id_sede"]." and ses_estado = 0  and sede_caja.id_caja = 2 
				GROUP BY id_sesion_caja
        		ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit; 

        	$lista_datos=$this->db->query($sql);
        	$lista_datosm=$this->db->query($sqlm);
        	if($lista_datos->num_rows()>0)
        	{
        		$data= $lista_datos->result(); 
        		$datam= $lista_datosm->result(); 
        	}
        	else
        	{
        		$data= null;
        		$datam= null;
        	} 
        }
        else
        {
          // cuando  existe buscar
        	$search = $this->input->post('search')['value']; 
 
        		$sql="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
        		where ses_estado = 0 and sede_caja.id_caja = 1  and (empleados.empleado_nombres like '%". $search."%' or sesion_caja.ses_fechaapertura like '%". $search."%') and sede_caja.id_sede=".$_COOKIE["id_sede"]."
        		GROUP BY id_sesion_caja";
        		$dat=$this->db->query($sql);
        		$sql="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
        		where ses_estado = 0 and sede_caja.id_caja = 1  and (empleados.empleado_nombres like '%". $search."%' or  sesion_caja.ses_fechaapertura like '%". $search."%') and sede_caja.id_sede=".$_COOKIE["id_sede"]."
        		GROUP BY id_sesion_caja
        		ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;


        		$sqlm="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja and movimiento.mov_estado != 0
        		where ses_estado = 0 and sede_caja.id_caja = 2  and (empleados.empleado_nombres like '%". $search."%' or sesion_caja.ses_fechaapertura like '%". $search."%') and sede_caja.id_sede=".$_COOKIE["id_sede"]."
        		GROUP BY id_sesion_caja";
        		$datm=$this->db->query($sql);

        		$sqlm="SELECT CONCAT(empleados.empleado_nombres,' ',empleados.empleado_apellidos) AS nombre,
				sesion_caja.ses_montoinicial,
				sesion_caja.id_sesion_caja,
				sesion_caja.ses_fechaapertura,
				IF(SUM(movimiento.mov_monto) is null,0,SUM(movimiento.mov_monto)) as monto_dia
				FROM
				sesion_caja
				INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
				INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
				LEFT JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_cajaf and movimiento.mov_estado != 0
        		where ses_estado = 0 and sede_caja.id_caja = 2  and (empleados.empleado_nombres like '%". $search."%' or  sesion_caja.ses_fechaapertura like '%". $search."%') and sede_caja.id_sede=".$_COOKIE["id_sede"]."
        		GROUP BY id_sesion_caja
        		ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;
        		echo $sqlm;exit();
        		$lista_datos=$this->db->query($sql);
        		$lista_datosm=$this->db->query($sqlm);
  
        	if($lista_datos->num_rows()>0)
        	{
        		$data= $lista_datos->result(); 
        		$totalFiltered = $dat->num_rows();
        		$datam= $lista_datosm->result(); 
        		$totalFilteredm = $datm->num_rows();
        	}
        	else
        	{
        		$data= null;
        		$datam= null;
        		$totalFiltered = 0;
        	}



        }
        $tabla = "'ventas'";
        $data1 = array();
        $contador = 0; 
        if(!empty($data)){
 

        	foreach ($data as $post){
        		$html1=""; 
        		$cajavirtual = $post->ses_montoinicial+$post->monto_dia;
        		$cajafisica = $datam[$contador]->ses_montoinicial+$datam[$contador]->monto_dia;

        		$nestedData['mozo'] = $post->nombre;
        		$nestedData['tipocaja'] = 'FÍSICA/VIRTUAL';
        		$nestedData['monto'] = $cajavirtual.'/'.$cajafisica;
        		$nestedData['fecha'] =$post->ses_fechaapertura;
        		$nestedData['acciones'] =$post->id_sesion_caja.'/'.$datam[$contador]->id_sesion_caja;

        		
 
					$nestedData['acciones'] ='<td> 

						<a href="'.base_url().'HSesion_caja/cargar_detalle_pagos/1/'.$post->id_sesion_caja.'/'.$datam[$contador]->id_sesion_caja.'" class="text-inverse"  target=”_blank” title="Ver Ventas" data-toggle="tooltip">
	        		<i class="far fa-file-word"></i></i></i>
	        		</a>
	        		<a href="'.base_url().'HSesion_caja/llamarfuncion/1/'.$post->id_sesion_caja.'/'.$datam[$contador]->id_sesion_caja.'" class="text-inverse"  target=”_blank” title="Ver Ventas" data-toggle="tooltip">
	        		<i class="far fa-file"></i></i></i>
	        		</a>
	        		<a href="'.base_url().'HSesion_caja/llamarfuncion/2/'.$post->id_sesion_caja.'/'.$datam[$contador]->id_sesion_caja.'" class="text-inverse" title="Ver Nota de Credito"  target=”_blank” data-toggle="tooltip">
	        		<i class="fas fa-file-pdf"></i></i>
	        		</a>
	        		</td>';
        	 
        		

        		$html="";

        		$data1[] = $nestedData;
        		$contador++;
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

	public function llamarfuncion($identificador,$id_sesion_cajaf,$id_sesion_cajav){
		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa where empresa_ruc=".$_COOKIE["ruc_empresa"]);
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
			WHERE sesion_caja.id_sesion_caja =".$id_sesion_cajaf." or sesion_caja.id_sesion_caja = ".$id_sesion_cajav." ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;
		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE movimiento.mov_estado != 0 and (movimiento.id_sesion_caja = ".$id_sesion_cajaf." OR movimiento.id_sesion_caja = ".$id_sesion_cajav." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE (id_sesion_caja = ".$id_sesion_cajaf."	OR id_sesion_caja = ".$id_sesion_cajav.")");

		$traermovimientos = $this->Mantenimiento_m->consulta3("SELECT movimiento.mov_id,venta.venta_idventas,compras.compra_id,detalle_venta.estado_descuento,
		IF(detalle_venta.estado_descuento != 'DS','AGRUP',IF(detalle_venta.estado_descuento IS NOT NULL,CONCAT( detalle_venta.estado_descuento, '_', producto.producto_id, '_', detalle_venta.precio ),	movimiento.mov_id ) ) AS agrupador,
		IF( movimiento.mov_formapago = 1, 'F', 'V' ) AS formapago,
		IF	( categoria_producto.categoria_producto_id IS NULL, 'BEBIDAS', categoria_producto.categoria_producto_descripcion ) AS categoria,
		CASE WHEN venta.venta_idventas IS NOT NULL THEN	producto.producto_descripcion 
		ELSE movimiento.mov_descripcion END AS producto_descripcion,

		CASE WHEN venta.venta_idventas IS NOT NULL THEN	SUM( detalle_venta.cantidad ) 
		WHEN ( compras.compra_id IS NOT NULL ) THEN	SUM( detalle_compras.detalle_compra_cantidad ) ELSE '1' 
		END AS cantidad,

		CASE WHEN venta.venta_idventas IS NOT NULL THEN	ROUND( AVG( detalle_venta.precio ), 2 ) 
		WHEN ( compras.compra_id IS NOT NULL ) THEN	ROUND( AVG( detalle_compras.detalle_compra_preciounitatio ), 2 ) ELSE movimiento.mov_monto 
		END AS precio,
		CASE WHEN venta.venta_idventas IS NOT NULL THEN ROUND( ( SUM( detalle_venta.cantidad ) * AVG( detalle_venta.precio ) ), 2 ) 
		WHEN ( compras.compra_id IS NOT NULL ) THEN	ROUND( ( SUM( detalle_compras.detalle_compra_cantidad ) * AVG( detalle_compras.detalle_compra_preciounitatio ) * ( - 1 ) ), 2 ) 
		WHEN ( venta.venta_idventas IS NULL AND compras.compra_id IS NULL ) AND concepto.id_tipo_movimiento = 1 THEN movimiento.mov_monto 
		WHEN ( venta.venta_idventas IS NULL AND compras.compra_id IS NULL ) AND concepto.id_tipo_movimiento = 2 THEN ( ( - 1 ) * movimiento.mov_monto ) 
		END AS total,
		concepto.id_tipo_movimiento,
		'U.M' AS unidadmedida,
		CASE WHEN ( venta.venta_idventas IS NOT NULL ) AND ( venta.venta_monto_delivery != 0 ) THEN	'Ingreso Delivery' ELSE concepto.con_descripcion 
		END AS 'concepto',
		CASE WHEN venta.venta_idventas IS NOT NULL THEN 1 
		WHEN ( compras.compra_id IS NOT NULL ) THEN	2 
		WHEN ( venta.venta_idventas IS NULL AND compras.compra_id IS NULL ) AND concepto.id_tipo_movimiento = 1 THEN 3 
		WHEN ( venta.venta_idventas IS NULL AND compras.compra_id IS NULL ) AND concepto.id_tipo_movimiento = 2 THEN 4 
		END AS tipomov 
		FROM
		movimiento
		LEFT JOIN venta ON venta.venta_idmovimiento = movimiento.mov_id
		LEFT JOIN compras ON movimiento.mov_id = compras.compra_idmovimiento
		LEFT JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
		LEFT JOIN detalle_compras ON detalle_compras.compra_id = compras.compra_id
		LEFT JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  
 		LEFT JOIN categoria_producto ON producto.categoria_producto_id = categoria_producto.categoria_producto_id		
 		LEFT JOIN concepto ON movimiento.mov_concepto = concepto.con_id 
			WHERE movimiento.mov_estado != 0 and  (movimiento.id_sesion_caja =".$id_sesion_cajaf." OR movimiento.id_sesion_caja = ".$id_sesion_cajav.") 
			GROUP BY producto.producto_id,tipomov,detalle_venta.estado_descuento,agrupador,detalle_compras.detalle_compra_preciounitatio 
		ORDER BY tipomov,categoria_producto.categoria_producto_id,compras.compra_id,venta.venta_idventas,movimiento.mov_id"); 

		$consulta_delivery = $this->Mantenimiento_m->consulta3("SELECT 'Costo Delivery' as descripcion,venta.venta_monto_delivery,venta.venta_idventas FROM movimiento
			INNER JOIN venta ON venta.venta_idmovimiento = movimiento.mov_id AND venta.venta_monto_delivery > 0 AND movimiento.mov_id = venta.venta_idmovimiento
			WHERE (movimiento.id_sesion_caja =".$id_sesion_cajaf." OR movimiento.id_sesion_caja = ".$id_sesion_cajav.")");
		$ingresos = 0;$egresos=0;$ingresosc = 0;$egresosc = 0;
		foreach($traermovimientos as $value){
			if ($value["tipomov"] == 1) {
				$ingresos = $ingresos + $value["total"];
			}
			if ($value["tipomov"] == 2) {
				$egresos = $egresos + $value["total"];
			}
			if ($value["tipomov"] == 3) {
				$ingresosc = $ingresosc + $value["total"];
			}
			if ($value["tipomov"] == 4) {
				$egresosc = $egresosc + $value["total"];
			}
		}
		$deliverytotal = 0;
		foreach($consulta_delivery as $value){
			$deliverytotal = $deliverytotal + $value["venta_monto_delivery"];
		}

		if ($identificador == 1) {
			$this->traerarqueo($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$consulta_delivery,$ingresos,$egresos,$ingresosc,$egresosc,$deliverytotal,$montocaja);
		}else{
			if ($identificador == 2) { 
				$this->pdfgenerar($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$consulta_delivery,$ingresos,$egresos,$ingresosc,$egresosc,$deliverytotal,$montocaja);
			}else{
				$this->excelgenerar($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$montocaja);
			}
		}


	}
		public function traerarqueo($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$consulta_delivery,$ingresos,$egresos,$ingresosc,$egresosc,$deliverytotal,$montocaja){
		//and ses_estado = 1

		//$ventas = $traerventas;
		//$compras = $traercompras;
		//print_r($montocaja);exit(); 	
		$this->load->view("Sesion_caja/arqueo_cajaa",compact("empresa","sede","sesion_caja","empleado","traermovimientos","consulta_delivery","ingresos","egresos","ingresosc","egresosc","deliverytotal","montocaja"));  

	}

	public function pdfgenerar($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$consulta_delivery,$ingresos,$egresos,$ingresosc,$egresosc,$deliverytotal,$montocaja){ 
		$mpdf = new \Mpdf\Mpdf([
			'mode' => 'utf-8',           
			'setAutoTopMargin' => 'stretch',
			'orientation' => 'L'
		]);   

		$mpdf->WriteHTML($this->load->view('Sesion_caja/pdfarqueo_caja', compact("empresa","sede","sesion_caja","empleado","traermovimientos","consulta_delivery","ingresos","egresos","ingresosc","egresosc","deliverytotal","montocaja"),true));
		$mpdf->Output($pdfFilePath, "I");
	}


		public function cargar_detalle_pagos($identificador,$id_sesion_cajaf,$id_sesion_cajav)
	{


		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa LIMIT 1");
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		/*$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.id_empleado ='".$_COOKIE['usuario_id']."' and sede_caja.id_Sede ='".$_COOKIE['id_sede']."'  ORDER BY sesion_caja.id_sesion_caja DESC ")->result();*/
			$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id 
			WHERE sesion_caja.id_sesion_caja =".$id_sesion_cajaf." or sesion_caja.id_sesion_caja = ".$id_sesion_cajav." ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;


		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja = ".$id_sesion_cajaf." OR movimiento.id_sesion_caja = ".$id_sesion_cajav." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja 
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE  (id_sesion_caja = ".$id_sesion_cajaf."	OR id_sesion_caja = ".$id_sesion_cajav.")
GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento
			 ");










		$sesion_ultimo_fisico=$sesion_caja[1];
		$sesion_ultimo_virtual=$sesion_caja[0];

		$carga_movimiento=[];

		$sql="	select 
				concepto.con_descripcion as 'descripcion',
				SUM(if(tipo_movimiento.id_tipo_movimiento=1,movimiento.mov_monto,0)) as 'ingreso',
				SUM(if(tipo_movimiento.id_tipo_movimiento=2,movimiento.mov_monto,0)) as 'egreso',
				tipo_movimiento.id_tipo_movimiento,
				concepto.con_id

				from movimiento 
				inner join concepto 
				on concepto.con_id=movimiento.mov_concepto
				inner join tipo_movimiento on concepto.id_tipo_movimiento=tipo_movimiento.id_tipo_movimiento
				where movimiento.mov_estado=1 and
				movimiento.id_sesion_caja   IN (?,?)
				GROUP BY concepto.con_id
                 order by concepto.id_tipo_movimiento asc
				";

		$c=0;
		//echo $sesion_ultimo_fisico->id_sesion_caja;exit;
		$concepto_array=$this->db->query($sql,array($id_sesion_cajav,$id_sesion_cajaf))->result_array();		
 
		foreach ($concepto_array as $key => $value) {
			//print_r($value);

			$carga_movimiento["movimiento"][$c]["descripcion"]=$value["descripcion"];
			$carga_movimiento["movimiento"][$c]["ingreso"]=$value["ingreso"];
			$carga_movimiento["movimiento"][$c]["egreso"]=$value["egreso"];
			$carga_movimiento["movimiento"][$c]["tipo"]=1;

			$c++;
			$sql="select 
					CONCAT(movimiento.mov_descripcion,' ',movimiento.tipo_comprobante_descripcion) as 'descripcion',
					SUM(if(tipo_movimiento.id_tipo_movimiento=1,movimiento.mov_monto,0)) as 'ingreso',
					SUM(if(tipo_movimiento.id_tipo_movimiento=2,movimiento.mov_monto,0)) as 'egreso',
					tipo_movimiento.id_tipo_movimiento,
					concepto.con_id

					from movimiento 
					inner join concepto 
					on concepto.con_id=movimiento.mov_concepto
					inner join tipo_movimiento on concepto.id_tipo_movimiento=tipo_movimiento.id_tipo_movimiento
					where movimiento.mov_estado=1
					and concepto.con_id=? and
					movimiento.id_sesion_caja  IN (?,?)
					GROUP BY movimiento.mov_id
                     ORDER by mov_fecha_tiempo desc
					 ";
 
				$movimiento_array=$this->db->query($sql,array($value["con_id"],$id_sesion_cajav,$id_sesion_cajaf))->result_array();	
				foreach ($movimiento_array as $key => $value1) {
					//print_r($value);


			$carga_movimiento["movimiento"][$c]["descripcion"]=$value1["descripcion"];
			$carga_movimiento["movimiento"][$c]["ingreso"]=$value1["ingreso"];
			$carga_movimiento["movimiento"][$c]["egreso"]=$value1["egreso"];
			$carga_movimiento["movimiento"][$c]["tipo"]=2;

			$c++;
				}


		}







		$sql="	select 
				formapago.for_descripcion as 'descripcion',
				SUM(if(tipo_movimiento.id_tipo_movimiento=1,movimiento.mov_monto,0)) as 'ingreso',
				SUM(if(tipo_movimiento.id_tipo_movimiento=2,movimiento.mov_monto,0)) as 'egreso',
				tipo_movimiento.id_tipo_movimiento,
				concepto.con_id,
				formapago.for_descripcion,
 movimiento.mov_formapago
				from movimiento 
				inner join concepto 
				on concepto.con_id=movimiento.mov_concepto
				inner join tipo_movimiento on concepto.id_tipo_movimiento=tipo_movimiento.id_tipo_movimiento
				INNER JOIN formapago on movimiento.mov_formapago=formapago.for_id
				where movimiento.mov_estado=1 and
				movimiento.id_sesion_caja  IN (?,?)
				GROUP BY movimiento.mov_formapago
                 order by movimiento.mov_formapago asc
				";


$movimiento_informacion=array();
$c=0;
	$forma_array=$this->db->query($sql,array($id_sesion_cajav,$id_sesion_cajaf))->result_array();	
foreach ($forma_array as $key => $value) {

		$movimiento_informacion["movimiento"][$c]["descripcion"]=$value["descripcion"];
			$movimiento_informacion["movimiento"][$c]["ingreso"]=$value["ingreso"];
			$movimiento_informacion["movimiento"][$c]["egreso"]=$value["egreso"];
			$movimiento_informacion["movimiento"][$c]["tipo"]=3;

			$c++;

}



















$this->load->view("Sesion_caja/arqueo_caja",compact("movimiento_informacion","empresa","montocaja","sede","sesion_caja","empleado","carga_movimiento"));  

//		echo json_encode($carga_movimiento);exit;







	}
}
