
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php"; 
class Venta_mesa extends BaseController {

	public function __construct() {
		parent::__construct();


	}

	public function index()	{   
		$data=array();
		$data["titulo_descripcion"]="Lista Mesas"; 
		$data["salones"] = $this->Mantenimiento_m->consulta3("SELECT * FROM lugar_mesa where lugar_mesa.lugarmesa_estado = 1 and lugar_mesa.id_sede =".$_COOKIE["id_sede"]);
		$this->vista("VentasMesa/listamesa",$data);
	}

	public function llamarventanalista(){
		$where = '';
		$data["titulo_descripcion"]="Pantalla de Mesas - Lista";
		if ($_COOKIE["id_perfil"] == 5 || $_COOKIE["id_perfil"] == 8 || $_COOKIE["id_perfil"] == 12) {
			if (isset($_POST["idsalon"]) && isset($_POST["idestado"])) {
				$where = "and mesa.mesa_id_lugar = ".$_POST["idsalon"]." and mesa.mesa_disponible = ".$_POST["idestado"];
			}else{
				if (isset($_POST["idsalon"])){
					$where = "and mesa.mesa_id_lugar = ".$_POST["idsalon"];
				}
				if (isset($_POST["idestado"])){
					$where = "and mesa.mesa_disponible = ".$_POST["idestado"];
				}
			}
		}else{
			$where = 'and (mesa.mesa_disponible != 1 or venta.venta_idventas is not  null)';
			$mozo = ' and venta.venta_codigomozo='.$_COOKIE["usuario_id"];
			if (isset($_POST["idsalon"]) && isset($_POST["idestado"])) {
				if ($_POST["idestado"] == 1) {
					$where = "and mesa.mesa_id_lugar = ".$_POST["idsalon"]." and (mesa.mesa_disponible != 1 or venta.venta_idventas is not  null) and venta.venta_codigomozo=".$_COOKIE["usuario_id"];
				}else{
					$where = "and mesa.mesa_id_lugar = ".$_POST["idsalon"]." and mesa.mesa_disponible = ".$_POST["idestado"];
				}
			}else{
				if (!isset($_POST["idsalon"]) && !isset($_POST["idsalon"])){
				}else{
					if (isset($_POST["idsalon"])){
						$where = "and mesa.mesa_id_lugar = ".$_POST["idsalon"];
					}else{
					}
					if (isset($_POST["idestado"])){
						if ($_POST["idestado"] == 1) {
							$where = "and (mesa.mesa_disponible != 1 or venta.venta_idventas is not  null) and venta.venta_codigomozo=".$_COOKIE["usuario_id"];
						}else{
							$where = "and mesa.mesa_disponible = ".$_POST["idestado"];
						}
					}else{
						$where = " and venta.venta_codigomozo=".$_COOKIE["usuario_id"];
					}
				}

			}

		}
		$data=$this->Mantenimiento_m->consulta3("SELECT mesa.mesa_tipo,

			mesa.mesa_id,if(mesa.mesa_tipo<>1,
	CONCAT( 'Mesa ', CONCAT(mesa.mesa_numero,buscar_mesas_hijo(mesa.mesa_id))),


			CONCAT('Llevar',mesa.mesa_numero)) AS nombre_silla,lugar_mesa.lugarmesa_descripcion,mesa.mesa_id AS silla_idsilla,
			venta.venta_pedidofecha,
			mesa.mesa_disponible as silla_estado,


			IF(mesa.mesa_disponible = 0,'Desocupado',IF(mesa.mesa_disponible= 1,'Ocupado',IF(mesa.mesa_disponible = 2,'Reservado','ERROR'))) AS nombre_estado,
					IF(mesa.mesa_disponible = 0,'success',IF(mesa.mesa_disponible = 1,'danger',IF(mesa.mesa_disponible = 2,'warning','ERROR'))) AS nombre_color,
					venta.venta_idventas,venta.venta_codigomozo,venta.venta_monto,mesa.mesa_numero ,
					mesa.mesa_estado_agrupacion
					FROM mesa
					INNER JOIN lugar_mesa ON mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
					LEFT JOIN venta ON venta.venta_codigomesa = mesa.mesa_id AND venta.venta_estado != 0 AND venta.venta_estado != 2 
					where   mesa.mesa_id != 0 and lugar_mesa.id_sede = ".$_COOKIE["id_sede"]."  and mesa.mesa_estado = 1 and (CONCAT('Mesa ',mesa.mesa_numero) LIKE '%".$this->input->post("valor")."%') ".$where."

					and mesa.mesa_estado_agrupacion in (1,0)
					ORDER BY mesa.mesa_tipo asc,mesa.mesa_numero");
		$html="";
		if (count($data) != 0) {
		$html.='<ul>';
			foreach ($data as $key => $value) {
				$mesa_id = $value["mesa_id"]; 
				$idsilla = $value["silla_idsilla"];
				
				$sillaestado = $value["silla_estado"];
				if($value["mesa_estado_agrupacion"]==1)
				{
				$sillaestado=4;
				}
				if (($value["venta_idventas"]) != '') {
					$idventa = $value["venta_idventas"];
					$codigomozo = $value["venta_codigomozo"];
					$nombre = "'".$value["nombre_silla"]."'";
					$envio = 'cargarventa('.$idventa.','.$codigomozo.','.$idsilla.',1,'.$nombre.')';
					$click_derecho='cargar_menu(event,'.$idventa.')';

				}else{
					$idventa = 'null';
					$codigomozo = 'null';
					$nombre = "'".$value["nombre_silla"]."'";
					$envio = 'realizarventa('.$idventa.','.$codigomozo.','.$mesa_id.','.$idsilla.','.$sillaestado.','.$nombre.')';
					$click_derecho='cargar_menu(event,'.$idventa.')';
				}

				$html.= '<li>';
				$html.='<a href="#" oncontextmenu="'.$click_derecho.'"   dep-mesa=""onclick="'.$envio.'" dep="" pos="1" title="'.$value["lugarmesa_descripcion"].'" index="M0038" state="'.substr($value["nombre_estado"], 0,1).'" vta="" mesa="'.$value["nombre_silla"].'" moso="'.$value["venta_codigomozo"].'" namemoso="'.$value["venta_codigomozo"].'" class="itm_mesa">';
				if($value["silla_estado"] == 0 ){ 
					$html.='<img style="background: #43ff7b;" src="'.base_url().'/public/images/mesas/vacio.png">';
					$html.='<div id="pentagon" style=" text-align: center;">
					<h6 class="nombresilla texto_div"  style="margin-top: -42px; font-size:15px;">'.$value["nombre_silla"].'</h6>
					<h6 class="ng-binding texto_div" style="margin-left: 5px; font-size:14px;">Disponible</h6>
             		<h6 class="ng-binding texto_div" style="font-size:12px;margin-top:0px;margin-bottom:0px;padding-top:0px;padding-bottom:0px;">'.$value["lugarmesa_descripcion"].'</h6>
			
					</div>';
					//$html.='<div class="caja1" name="estadoaba[]" name-id="'.$value["silla_estado"].'"  id="estadoaba'.$value["silla_idsilla"].'"><span class="span_total_venta"></span> <span class="ng-binding">Disponible</span>';
				}else{
					if($value["silla_estado"] == 1){
						$html.='<img style="background: #f95858;" src="'.base_url().'/public/images/mesas/lleno.png">';
						$html.='<div id="pentagon" style=" text-align: center;">
						<h6 class="nombresilla texto_div" style="margin-top: -42px; font-size:15px;">'.($value["nombre_silla"]).'</h6>
						<h6 class="ng-binding texto_div" style="margin-left: 1px;font-size:14px">S/ '.$value["venta_monto"].'</h6>
		<h6 class="ng-binding texto_div" style="font-size:12px;margin-top:2px;margin-bottom:0px;padding-top:0px;padding-bottom:0px;">'.$value["lugarmesa_descripcion"].'</h6>

						</div>';
						//$html.='<div class="caja2" name="estadoaba[]" name-id="'.$value["silla_estado"].'"  id="estadoaba'.$value["silla_idsilla"].'"><div style="margin:-10px"><span class="span_total_venta">Total </span> <span class="ng-binding">S/ '.$value["venta_monto"].'</span></div><div style="margin:-10px">
						//<span class="ng-binding fecha_tamano"  id="tiempo_silla'.$value["silla_idsilla"].'" >S/ '.$value["venta_monto"].'</span></div>
						$html.='<script>
						tiem= moment("'.$value["venta_pedidofecha"].'", "YYYY-MM-DDTHH:mm:ss").fromNow();
						$("#tiempo_silla'.$value["silla_idsilla"].'").text(tiem);
						</script>';
					}else{ 
						$html.='<img style="background: #43ff7b;" src="'.base_url().'/public/images/mesas/reservado.png">';
							$html.='<div id="pentagon" style=" text-align: center;">
								<h6 
								class="nombresilla texto_div" 
								style="margin-top: -42px; font-size:15px;">'.$value["nombre_silla"].'
								</h6>
										<h6 class="ng-binding texto_div" style="margin-left: 5px; font-size:14px;">Reservado</h6>
			                    		<h6 class="ng-binding texto_div" style="font-size:12px;margin-top:0px;margin-bottom:0px;padding-top:0px;padding-bottom:0px;">'.$value["lugarmesa_descripcion"].'</h6>
						
								</div>';
						//$html.='<div class="caja1" name="estadoaba[]" name-id="'.$value["silla_estado"].'"  id="estadoaba'.$value["silla_idsilla"].'"><span class="span_total_venta"></span> <span class="ng-binding">Reservado</span></div>';
					} 
				}
				$html.='</a>';
				$html.= '</li>';
				//$html.='</div></div>';	
				$contador = 1;		
			}
			$html.='<ul>';
		}else{
			$html.='<center style="margin-bottom: 20px;">
			<img class="iderror" src="'.base_url().'/public/images/mesas/giphy.gif" width="500" />
			<p style="margin: 0 0 0 0; font-size: 32px; font-weight: 700;">No se encontraron mesas</p>
			</center> ';
			$contador = 0;
		}
				$datos["html"]=$html;
		$datos["cont"] = $contador;
		echo json_encode($datos);
	}
	public function validarmozo(){
		$idubicacion = $_POST["idsilla"];
		$idventa = $_POST["idventa"];
		$idvendedor = $_POST["idvendedor"];
		$data = 0;
		if ($_COOKIE["usuario_perfil"] != 2) {
			$band = 0;
		}else{
			if ($idvendedor != $_COOKIE["usuario_id"]) {
			/*if ($this->estado_configuracion(3) == 1) {
					$data = $this->Mantenimiento_m->consulta3("SELECT empleados.empleado_nombres,empleados.empleado_apellidos
						FROM silla
						INNER JOIN empleados ON empleados.empleado_id = silla.silla_id_empleado
						where silla.silla_idsilla = ".$idubicacion." and silla.silla_estado = 1");
				}else{
					$data = $this->Mantenimiento_m->consulta3("SELECT empleados.empleado_nombres,empleados.empleado_apellidos
						FROM empleados
						INNER JOIN mesa ON empleados.empleado_id = mesa.mesa_empleado
						where mesa.mesa_id = ".$idubicacion." and mesa.mesa_estado = 1");
				}*/

							$data = $this->Mantenimiento_m->consulta3("SELECT empleados.empleado_nombres,empleados.empleado_apellidos
						FROM empleados
						INNER JOIN mesa ON empleados.empleado_id = mesa.mesa_empleado
						where mesa.mesa_id = ".$idubicacion." and mesa.mesa_estado = 1");
				$band = 0;
			}else{
				$band = 0;
			}
		}
		$html["data"] = $data;
		$html["band"] = $band;
		echo json_encode($html);
	}

	 public function reserva()
    {

    	$id_silla=$_POST["idsilla"];
    	$estado=$_POST['opcion'];
    	$sql="update mesa set mesa_disponible=? where mesa_id=?";
    	$this->db->query($sql,array($estado,$id_silla));
      
    }
    public function anularpedido()
    {

    	$id_silla=$_POST["idsilla"];
    	//$estado=$_POST['opcion'];
    	$sql="update mesa set mesa_disponible=0 where mesa_id=?";
    	$this->db->query($sql,array($id_silla));

    }

    public function buscar_mesas_agrupar()
    {

    	$idsilla=$_POST["idsilla"];
    	$sql="select * from mesa where mesa_id=?";
    	$mesa=$this->db->query($sql,array($idsilla))->row_array();

    	$sql="SELECT
				mesa.mesa_tipo,
				mesa.mesa_id,
			IF
				( mesa.mesa_tipo <> 1, CONCAT( 'Mesa ', mesa.mesa_numero ), CONCAT( 'Llevar', mesa.mesa_numero ) ) AS nombre_silla,
				lugar_mesa.lugarmesa_descripcion,
				mesa.mesa_id AS silla_idsilla,
				mesa.mesa_disponible AS silla_estado,
				mesa.mesa_numero 
			FROM mesa
			 INNER JOIN lugar_mesa ON
			 mesa.mesa_id_lugar = lugar_mesa.lugarmesa_id
			WHERE
				mesa.mesa_id not in (0,".$idsilla.")
				AND lugar_mesa.id_sede = 1
				AND mesa.mesa_estado = 1 
				and lugar_mesa.lugarmesa_id=?
				and mesa.mesa_tipo=0
				and mesa.mesa_disponible=0
		
			ORDER BY

				mesa.mesa_numero";
         $response=$this->db->query($sql,array($mesa["mesa_id_lugar"]))->result_array();
		echo json_encode($response);exit();
    	
    }


    public function guardar_mesa_agrupar()
    {
    	$resp=array();
          $idsilla=$_POST["idsilla"];
          if( $idsilla!="")
          {

             if (isset($_POST["mesanueva"])) {
                  $sql="update mesa set mesa_estado_agrupacion=? where mesa_id=?";
                  $this->db->query($sql,array(1,$idsilla));

                  foreach ($_POST["mesanueva"] as $key => $value) {
                  	
                  	    $sql="update mesa set mesa_estado_agrupacion=?,mesa_id_padre=? where mesa_id=?";
                  $this->db->query($sql,array(2,$idsilla,$value));
                  }

                    $resp["estado"]=true;
		           $resp["mensaje"]="Se registro correctamente";
		           echo json_encode($resp);exit();


             }
             else{
             	  $resp["estado"]=false;
           $resp["mensaje"]="No se encuentran sillas seleccionadas";
           echo json_encode($resp);exit();
             }
          }else{

           $resp["estado"]=false;
           $resp["mensaje"]="Numero de silla no valido";
           echo json_encode($resp);exit();
          }
    }

    public function anular_mesaagrupar()
    {
        $resp=array();
          $idsilla=$_POST["idsilla"];
          if( $idsilla!="")
          {

    			$this->anular_agrupacion_mesas($idsilla);
    			  $resp["estado"]=true;
		           $resp["mensaje"]="Se registro correctamente";
		           echo json_encode($resp);exit();
          }else{
 			$resp["estado"]=false;
           		$resp["mensaje"]="Numero de silla no valido";
           		echo json_encode($resp);exit();

    		}
    }




}
?>
