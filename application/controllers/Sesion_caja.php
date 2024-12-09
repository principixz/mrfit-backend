<?php 
require APPPATH . 'libraries/ImplementJwt.php';

require_once  (APPPATH.'libraries/vendor/autoload.php');
require_once "BaseController.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class sesion_caja  extends BaseController {

	public function __construct() {
		parent::__construct();

$this->objOfJwt = new ImplementJwt();
	}

	public function index(){ 
		$data=array();
		if ($_COOKIE["usuario_perfil"] != 5 && $_COOKIE["usuario_perfil"] != 8) {
			$this->consultar_saldo_caja(3);
		}else{
			if ($_COOKIE["usuario_perfil"] == 5) {
				$estadosesioncaja = $this->Mantenimiento_m->consulta3("SELECT sesion_caja.id_empleado FROM sesion_caja,sede_caja WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.ses_estado = 1 AND sede_caja.id_sede = ".$_COOKIE["id_sede"]." GROUP BY sesion_caja.id_empleado");
				if (isset($estadosesioncaja[0]["id_empleado"])) {
					if ($_COOKIE["usuario_id"] == $estadosesioncaja[0]["id_empleado"]) {
						$this->consultar_saldo_caja(1);
					}else{
						$this->consultar_saldo_caja(0);
					}

				}else{
					$this->consultar_saldo_caja(0);
				}
			}else{
				$this->consultar_caja_sedes();
			}			
		}
	}

	public function consultar_saldo_caja($estado){
		$estadosesioncaja = $estado;
		if ($estado == 1 || $estado == 0) {
			$data["titulo_descripcion"]="Sesión de Caja";
			$id_sede=$_COOKIE['id_sede'];
			$fecha_dia =date("Y-m-d");
			$sql="SELECT MAX(sesion_caja.id_sesion_caja) as ult FROM sede_caja,sesion_caja where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and
			sede_caja.id_caja=1 and sede_caja.id_sede=".$id_sede." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"];
			$ulsesionf=$this->Mantenimiento_m->consulta3($sql);
			if (!isset($ulsesionf[0]["ult"])) {
				$array_formapago=array();
				$data["titulo_descripcion"]="Sesión Caja";
				$data["estado_caja"]=2;
				$data["hingresosf"]=0;
				$data["hegresosf"]=0;
				$data["hingresosv"]=0;
				$data["hegresosv"]=0;
				$data["ingresosf"]=0;
				$data["egresosf"]=0;
				$data["ingresosv"]=0;
				$data["egresosv"]=0;
				$data["fecha_dia"]="0000-00-00";
				$data["movif"]=0;
				$data["moviv"]=0;
				$data["saldoinicialv"]=0;
				$data["saldoinicialf"]=0;
				$data["caja1"] = 0;
				$data["caja2"] = 0;
				$data["estadosesioncaja"] = 1;	
				$dat=$this->Mantenimiento_m->consulta3("select * from formapago where for_estado=1");
				foreach ($dat as $key => $value) {
					$array_formapago[$key]["descripcion"]=$value["for_descripcion"];
					$array_formapago[$key]["transacciones"]= 0;
					$array_formapago[$key]["ingreso"]=0.00;
					$array_formapago[$key]["egreso"]=0.00;
					$array_formapago[$key]["total"]=0;
				}
				$data["array_formapago"]=$array_formapago;
			}else{


				$estado_sesionf = $this->db->query("select * from sesion_caja where id_sesion_caja=".$ulsesionf[0]["ult"]." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"])->result_array();
				$fecha = explode(' ', $estado_sesionf[0]["ses_fechaapertura"]);
				$fecha_dia=$fecha[0];
				if ($estado_sesionf[0]["ses_estado"]==0){		
					$estado_caja = 2;					
				}else{
					if($fecha[0]==date('Y-m-d')){
						$estado_caja = 3;
					}else{
						$estadosesion = $this->db->query("select * from sesion_caja where id_sesion_caja=".$ulsesionf[0]['ult']." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"])->result_array();
						$fecha = explode(' ', $estadosesion[0]["ses_fechaapertura"]);
						$estado_caja = 3;
					}
				}
				$estadoactual = $this->Mantenimiento_m->consulta3("SELECT sesion_caja.ses_estado FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja WHERE DATE(sesion_caja.ses_fechaapertura) = '".$fecha[0]."' and sede_caja.id_sede=".$id_sede." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"]." ORDER BY sesion_caja.id_sesion_caja DESC LIMIT 1");
				$estadoactual = $estadoactual[0]["ses_estado"];
				$idsesion = $this->Mantenimiento_m->consulta3("SELECT sesion_caja.id_sesion_caja FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja WHERE sesion_caja.ses_estado =".$estadoactual." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"]." and sede_caja.id_sede=".$id_sede." and DATE(sesion_caja.ses_fechaapertura) = '".$fecha[0]."' ORDER BY sesion_caja.id_sesion_caja DESC LIMIT 2");
				if (isset($idsesion[0]["id_sesion_caja"])) {
					$caja1 =  $idsesion[0]["id_sesion_caja"];
					$caja2 = $idsesion[1]["id_sesion_caja"];
				}else{
					$caja1 =  0;
					$caja2 = 0;
				}
				$ingresosf=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto, movimiento.mov_estado  from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
					concepto.id_tipo_movimiento=1 and sede_caja.id_caja=1  and movimiento.mov_estado = 1 and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				$saldoinicialf = $this->Mantenimiento_m->consulta3("SELECT sesion_caja.ses_montoinicial
					FROM sesion_caja,sede_caja where sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.ses_estado = ".$estadoactual." and sede_caja.id_caja=1  and sede_caja.id_sede =".$id_sede." ORDER BY sesion_caja.ses_fechaapertura DESC LIMIT 1");
				if ($ingresosf[0]["monto"]=="") {
					$ingresosf=0.00;
				}else{
					$ingresosf = $ingresosf[0]["monto"];
				}
				if (!isset($saldoinicialf[0]["ses_montoinicial"])) {
					$saldoinicialf = 0.00;
				}else{
					$saldoinicialf = $saldoinicialf[0]["ses_montoinicial"];
				}

				$egresosf=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
					concepto.id_tipo_movimiento=2 and sede_caja.id_caja=1 and movimiento.mov_estado = 1 and sesion_caja.ses_estado= ".$estadoactual." and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				if ($egresosf[0]["monto"]=="") {
					$egresosf=0.00;
				}else{
					$egresosf = $egresosf[0]["monto"];
				}

				$movimientof=$this->Mantenimiento_m->consulta3("SELECT COUNT(movimiento.mov_id) as contar from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id  and sede_caja.id_caja=1 and movimiento.mov_estado = 1 and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				if ($movimientof[0]["contar"]=="") {
					$movif=0.00;
				}else{
					$movif = $movimientof[0]["contar"];
				}
				$ingresosv=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
					concepto.id_tipo_movimiento=1 and sede_caja.id_caja=2 and movimiento.mov_estado  = 1 and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				$saldoinicialv= $this->Mantenimiento_m->consulta3("SELECT sesion_caja.ses_montoinicial
					FROM sesion_caja,sede_caja where sesion_caja.id_sede_caja = sede_caja.id_sede_caja  and sesion_caja.ses_estado = ".$estadoactual." and  sede_caja.id_caja=2 and sede_caja.id_sede =".$id_sede." ORDER BY sesion_caja.ses_fechaapertura DESC LIMIT 1");

				if ($ingresosv[0]["monto"]=="") {
					$ingresosv=0.00;
				}else{
					$ingresosv = $ingresosv[0]["monto"];
				}

				if (!isset($saldoinicialv[0]["ses_montoinicial"])) {
					$saldoinicialv = 0.00;
				}else{
					$saldoinicialv = $saldoinicialv[0]["ses_montoinicial"];
				}
				$egresosv=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
					concepto.id_tipo_movimiento=2 and sede_caja.id_caja=2 and movimiento.mov_estado  = 1  and sesion_caja.ses_estado= ".$estadoactual." and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				if ($egresosv[0]["monto"]=="") {
					$egresosv=0.00;
				}else{
					$egresosv = $egresosv[0]["monto"];
				} 

				$movimientov=$this->Mantenimiento_m->consulta3("SELECT COUNT(movimiento.mov_id) as contar from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id  and sede_caja.id_caja=2 and movimiento.mov_estado =1 and movimiento.mov_fecha='".$fecha[0]."' and sede_caja.id_sede=".$id_sede);
				if ($movimientov[0]["contar"]=="") {
					$moviv=0.00;
				}else{
					$moviv = $movimientov[0]["contar"];
				}
				$data["estado_caja"]=$estado_caja;
				$data["ingresosf"]=$ingresosf;
				$data["egresosf"]=$egresosf;
				$data["ingresosv"]=$ingresosv;
				$data["egresosv"]=$egresosv;
				$data["fecha_dia"]=$fecha_dia;
				$data["movif"]=$movif;
				$data["moviv"]=$moviv;
				$data["saldoinicialv"]=$saldoinicialv;
				$data["saldoinicialf"]=$saldoinicialf;
				$data["caja1"] = $caja1;
				$data["caja2"] = $caja2;
				$data["estadosesioncaja"] = $estadosesioncaja;
				$array_formapago=array();
				$dat=$this->Mantenimiento_m->consulta3("select * from formapago where for_estado=1");

				if(!isset($estadoactual)) {
					$estadoactual = '""';
				}
				foreach ($dat as $key => $value) {

					$array_formapago[$key]["descripcion"]=$value["for_descripcion"];


					$sql="SELECT COUNT(movimiento.mov_id) as contar from sede_caja,sesion_caja,movimiento,concepto 
					where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and 
					sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id and sesion_caja.ses_estado = 1 and movimiento.mov_formapago=".$value["for_id"]." and movimiento.mov_estado = 1 and movimiento.mov_fecha='".$fecha_dia."' and sede_caja.id_sede=".$id_sede;

					$movimientof=$this->Mantenimiento_m->consulta3($sql);


					$array_formapago[$key]["transacciones"]= $movimientof[0]["contar"];


					$ingresosf=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
						where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and sesion_caja.ses_estado = ".$estadoactual." and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
						sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
						concepto.id_tipo_movimiento=1   and movimiento.mov_estado = 1 and movimiento.mov_fecha='".$fecha_dia."' and movimiento.mov_formapago=".$value["for_id"]);

					if ($ingresosf[0]["monto"]=="") {
						$array_formapago[$key]["ingreso"]=0.00;
					}else{
						$array_formapago[$key]["ingreso"]=$ingresosf[0]["monto"];
					}

					$egreso=$this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto from sede_caja,sesion_caja,movimiento,concepto 
						where sede_caja.id_sede_caja=sesion_caja.id_sede_caja and sesion_caja.ses_estado = ".$estadoactual." and (movimiento.id_sesion_caja = ".$caja1." or movimiento.id_sesion_caja =".$caja2." ) and
						sesion_caja.id_sesion_caja=movimiento.id_sesion_caja and movimiento.mov_concepto=concepto.con_id AND
						concepto.id_tipo_movimiento=2   and movimiento.mov_estado = 1 and movimiento.mov_fecha='".$fecha_dia."' and movimiento.mov_formapago=".$value["for_id"]);

					if ($egreso[0]["monto"]=="") {
						$array_formapago[$key]["egreso"]=0.00;
					}else{
						$array_formapago[$key]["egreso"]=$egreso[0]["monto"];
					}
					$total=$array_formapago[$key]["ingreso"]-$array_formapago[$key]["egreso"];
					$array_formapago[$key]["total"]=$total;
					$data["array_formapago"]=$array_formapago;
				}
			}
		}else{
			$array_formapago=array();
			$data["titulo_descripcion"]="Usted No tiene permisos!!!!!";
			$data["estado_caja"]=0;
			$data["hingresosf"]=0;
			$data["hegresosf"]=0;
			$data["hingresosv"]=0;
			$data["hegresosv"]=0;
			$data["ingresosf"]=0;
			$data["egresosf"]=0;
			$data["ingresosv"]=0;
			$data["egresosv"]=0;
			$data["fecha_dia"]="0000-00-00";
			$data["movif"]=0;
			$data["moviv"]=0;
			$data["saldoinicialv"]=0;
			$data["saldoinicialf"]=0;
			$data["caja1"] = 0;
			$data["caja2"] = 0;
			$data["estadosesioncaja"] = 3;	
			$dat=$this->Mantenimiento_m->consulta3("select * from formapago where for_estado=1");
			foreach ($dat as $key => $value) {
				$array_formapago[$key]["descripcion"]=$value["for_descripcion"];
				$array_formapago[$key]["transacciones"]= 0;
				$array_formapago[$key]["ingreso"]=0.00;
				$array_formapago[$key]["egreso"]=0.00;
				$array_formapago[$key]["total"]=0;
			}
			$data["array_formapago"]=$array_formapago;
		}
		
		$this->vista("Sesion_caja/index",$data);
	}

	public function consultar_caja_sedes(){
		$data["sede"]=$this->Mantenimiento_m->consulta("SELECT * FROM sede,empresa  WHERE sede.empresa_ruc = empresa.empresa_ruc and sede.sede_estado = 1 and empresa.empresa_ruc=".$_COOKIE["ruc_empresa"]);
		$this->vista("Sesion_caja/administrador",$data);
	}
	public function traerfisica(){
		$guardar["dias"] = $_POST;
		$tz = new DateTimeZone('UTC');	
		$datos= $this->Mantenimiento_m->consulta("SELECT Sum(movimiento.mov_monto) AS monto,movimiento.mov_fecha as fecha,
			HOUR(movimiento.mov_hora) AS Hora,YEAR(movimiento.mov_fecha) as Anio,MONTH(movimiento.mov_fecha) as Mes,DAY(movimiento.mov_fecha) as Dia
			FROM  movimiento
			INNER JOIN sesion_caja ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			INNER JOIN tipo_movimiento ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
			WHERE
			sesion_caja.id_sesion_caja = ".$_POST["cajafisica"]." AND
			tipo_movimiento.id_tipo_movimiento = 2
			GROUP BY HOUR(movimiento.mov_hora)
			ORDER BY HOUR(movimiento.mov_hora)");
		$nombre = "Compras del Día";
		$guardar["data"]["compras"]["name"] = $nombre;
		foreach ($datos  as  $key =>  $grafico) {
			$fechahora = $grafico->fecha . ' ' . $grafico->Hora.':00';
			$date = new DateTime($fechahora, $tz);
			$da[0]=(1000 * $date->format('U'));
			$da[1]=(float)$grafico->monto;
			$guardar["data"]["compras"]["data"][$key]= $da;		
		}

		$datos= $this->Mantenimiento_m->consulta("SELECT Sum(movimiento.mov_monto) AS monto, movimiento.mov_fecha as fecha,
			HOUR(movimiento.mov_hora) AS Hora,YEAR(movimiento.mov_fecha) as Anio,MONTH(movimiento.mov_fecha) as Mes,DAY(movimiento.mov_fecha) as Dia
			FROM  movimiento
			INNER JOIN sesion_caja ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			INNER JOIN tipo_movimiento ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
			WHERE
			sesion_caja.id_sesion_caja = ".$_POST["cajafisica"]." AND
			tipo_movimiento.id_tipo_movimiento = 1
			GROUP BY HOUR(movimiento.mov_hora)
			ORDER BY HOUR(movimiento.mov_hora)");
		$nombre = "Ventas del Día";
		$guardar["data"]["ventas"]["name"] = $nombre;
		foreach ($datos  as  $key =>  $grafico) {
			$fechahora = $grafico->fecha . ' ' . $grafico->Hora.':00';
			$date = new DateTime($fechahora, $tz);
			$da[0]=(1000 * $date->format('U'));
			$da[1]=(float)$grafico->monto;
			$guardar["data"]["ventas"]["data"][$key]= $da;		
		}
		echo json_encode($guardar);
	}

	public function traervirtual(){
		$guardar["dias"] = $_POST;
		$tz = new DateTimeZone('UTC');
		$datos= $this->Mantenimiento_m->consulta("SELECT Sum(movimiento.mov_monto) AS monto,movimiento.mov_fecha as fecha,
			HOUR(movimiento.mov_hora) AS Hora,YEAR(movimiento.mov_fecha) as Anio,MONTH(movimiento.mov_fecha) as Mes,DAY(movimiento.mov_fecha) as Dia
			FROM  movimiento
			INNER JOIN sesion_caja ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			INNER JOIN tipo_movimiento ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
			WHERE
			sesion_caja.id_sesion_caja = ".$_POST["cajavirtual"]." AND
			tipo_movimiento.id_tipo_movimiento = 2
			GROUP BY HOUR(movimiento.mov_hora)
			ORDER BY HOUR(movimiento.mov_hora)");
		$nombre = "Compras del Día";
		$guardar["data"]["compras"]["name"] = $nombre;
		foreach ($datos  as  $key =>  $grafico) {
			$fechahora = $grafico->fecha . ' ' . $grafico->Hora.':00';
			$date = new DateTime($fechahora, $tz);
			$da[0]=(1000 * $date->format('U'));
			$da[1]=(float)$grafico->monto;
			$guardar["data"]["compras"]["data"][$key]= $da;		
		}

		$datos= $this->Mantenimiento_m->consulta("SELECT Sum(movimiento.mov_monto) AS monto,movimiento.mov_fecha as fecha,
			HOUR(movimiento.mov_hora) AS Hora,YEAR(movimiento.mov_fecha) as Anio,MONTH(movimiento.mov_fecha) as Mes,DAY(movimiento.mov_fecha) as Dia
			FROM  movimiento
			INNER JOIN sesion_caja ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			INNER JOIN tipo_movimiento ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
			WHERE
			sesion_caja.id_sesion_caja = ".$_POST["cajavirtual"]." AND
			tipo_movimiento.id_tipo_movimiento = 1
			GROUP BY HOUR(movimiento.mov_hora)
			ORDER BY HOUR(movimiento.mov_hora)");
		$nombre = "Ventas del Día";
		$guardar["data"]["ventas"]["name"] = $nombre;
		foreach ($datos  as  $key =>  $grafico) {
			$fechahora = $grafico->fecha . ' ' . $grafico->Hora.':00';
			$date = new DateTime($fechahora, $tz);
			$da[0]=(1000 * $date->format('U'));
			$da[1]=(float)$grafico->monto;
			$guardar["data"]["ventas"]["data"][$key]= $da;		
		}
		echo json_encode($guardar);
	}

	public function apertura_caja(){

		if ($this->input->is_ajax_request()){
		//echo $_POST["inicio_caja"][0];exit();
			$sql="select * from sesion_caja where ses_estado=1 and id_empleado='".$_COOKIE['usuario_id']."'";
			$exist_sesion = $this->db->query($sql)->result_array(); 
			if (count($exist_sesion)==0) {
				$caja = $this->Mantenimiento_m->consulta("SELECT * FROM sede_caja where id_sede = '".$_COOKIE['id_sede']."' ");
				$aux = 0;
				foreach ($caja as $values) {
					$saldo = $values->sede_caja_monto - $_POST["inicio_caja"][($values->id_caja-1)];
					$caja_sede[($values->id_caja-1)] = $values->sede_caja_monto - $_POST["inicio_caja"][($values->id_caja-1)];
					$data = array(
						'sede_caja_monto' => $saldo
					);
					$this->db->where('id_sede_caja',$values->id_sede_caja);
					$this->db->where('id_caja',$values->id_caja);
					$estado=$this->db->update('sede_caja', $data);

//			Sesioncaja::open_sesioncaja();
				}

				$ult =$this->Mantenimiento_m->consulta("SELECT Max(sesion_caja.id_sesion_caja) AS ult,sede_caja.id_caja,sede_caja.sede_caja_monto,sede_caja.id_sede_caja FROM sesion_caja
					INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
					where sede_caja.id_sede =  '".$_COOKIE['id_sede']."' GROUP BY id_caja");
				if(count($ult) != 0){
					$hora = date('H:i');
					foreach ($ult as $values) {

						if( $values->ult ==""){
							$ultimo = 0;

						}else{
							$ultimo = $values->ult;
						}
						$estadosesion = $this->db->query("select * from sesion_caja where id_sesion_caja=".$ultimo)->result_array();
						if (count($estadosesion)==0) {
							$valid_fecha = date('Y-m-d');

						}else{
							$fecha = explode(' ', $estadosesion[0]["ses_fechaapertura"]);
							if ($fecha[0]==date('Y-m-d')) {
								$actual = date('Y-m-d');
								$valid_fecha = date('Y-m-d',strtotime('+0 days', strtotime($actual)));
							}else{
								$valid_fecha = date('Y-m-d');
							}
						}
				//$caja = $this->db->query("SELECT * FROM sede_caja where id_sede_caja = '".$values->id_sede_caja."' ")->result_array();
						$data = array(
							'id_empleado' => $_COOKIE['usuario_id'],
							'id_sede_caja' => $values->id_sede_caja,
							'ses_fechaapertura' => $valid_fecha.' '.$hora,
							'ses_montoapertura' => $caja_sede[($values->id_caja-1)],
							'ses_montoinicial' => $_POST["inicio_caja"][($values->id_caja-1)],
							'ses_montocierre' => 0.00
						);
						$estado=$this->db->insert('sesion_caja', $data);
					}
				}else{
					$valid_fecha = date('Y-m-d');
					$hora = date('H:i');
					$caja = $this->db->query("SELECT sede_caja.id_sede_caja,sede_caja.id_caja FROM sede_caja where id_sede ='".$_COOKIE['id_sede']."'")->result_array();
					foreach ($caja as $sedes) {
						$data = array(
							'id_empleado' => $_COOKIE['usuario_id'],
							'id_sede_caja' => $sedes['id_sede_caja'],
							'ses_fechaapertura' => $valid_fecha.' '.$hora,
							'ses_montoapertura' => $caja_sede[($sedes['id_caja']-1)],
							'ses_montoinicial' => $_POST["inicio_caja"][($sedes['id_caja']-1)],
							'ses_montocierre' => 0.00
						);
						$estado=$this->db->insert('sesion_caja', $data);
					}

				}
			}else{

			}
		}else{
			$this->load->view('Error/404');
		}
	}
// ." and sesion_caja.id_empleado = ".$_COOKIE["usuario_id"]."


	public	function validarcaja(){
		if ($this->input->is_ajax_request()){
			$html = '1';
			$html1 = '';
			$exist_sesion = $this->db->query("SELECT * FROM sesion_caja,sede_caja where sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.ses_estado = 1 and sede_caja.id_Sede ='".$_COOKIE['id_sede']."'")->result();
			if(count($exist_sesion) !=0){		  		
				foreach ($exist_sesion as $values) {
					$fecha = explode(' ', $values->ses_fechaapertura);
					if ($_COOKIE["usuario_perfil"] == 5) {				
						if($fecha[0]>=date('Y-m-d')){
							if ($values->id_empleado == $_COOKIE["usuario_id"]) {
								$html = $html;
								$html = '1';
								exit();
							}else{
								$html1 = '<a href="'.base_url().'control"><button type="button" class="btn btn-danger" >Regresar</button></a>';
								$html = 'Estimado usuario: Usted no abrio caja!';		
							}
						}else{
							if ($values->id_empleado == $_COOKIE["usuario_id"]) {
								$html1 = '<a href="'.base_url().'sesion_caja"><button type="button" class="btn btn-danger" >Ir a caja </button></a>';
								$html = 'Estimado usuario: Aun no cierra caja del día: 	'."$fecha[0]";				
							}else{
								$html1 = '<a href="'.base_url().'control"><button type="button" class="btn btn-danger" >Regresar </button></a>';
								$html = 'Estimado usuario: Aun no se cerro caja, pero usted no tiene permiso para cerrarla ';
							}					

						}		  			
					}else{
						if($fecha[0]>=date('Y-m-d')){
							$html = $html;
						}else{
							$html1 = '<a href="'.base_url().'control"><button type="button" class="btn btn-danger" >Regresar </button></a>';
							$html = 'Estimado usuario: Lo sentimos aún no se aperturo caja, vuelva cuando se aperture! ';						
						}		
					}
				}
			}else{
				if ($_COOKIE["usuario_perfil"] == 5) {
					$html1 = '<a href="'.base_url().'sesion_caja"><button type="button" class="btn btn-danger" >Ir a caja </button></a>';
					$html='Estimado usuario: Aun no se abrio caja del día';
				}else{
					$html1 = '<a href="'.base_url().'control"><button type="button" class="btn btn-danger" >Regresar </button></a>';
					$html = 'Estimado usuario: Lo sentimos aún no se aperturo caja, vuelva cuando se aperture! ';
				}

			}
			$datos["html"] = $html;
			$datos["html1"] = $html1;
			echo json_encode($datos);

		}else{
			$this->load->view('Error/404');
		}
	}

	public	function close_sesioncaja(){
		if ($this->input->is_ajax_request()){
			$caja = $this->Mantenimiento_m->consulta("SELECT * FROM sede_caja where id_sede = '".$_COOKIE['id_sede']."' ");
			foreach ($caja as $values) {
				$data = array(
					'ses_fechacierre' => date('Y-m-d').' '.date('H:i'),
					'ses_montocierre' => $values->sede_caja_monto,
					'ses_estado' => 0
				);
				$this->db->where('ses_estado',1);
				$this->db->where('id_sede_caja',$values->id_sede_caja);
				$estado=$this->db->update('sesion_caja', $data);
				if ($values->id_caja == 1) {
					$saldocaja = $_POST["ingresosf"];
				}else{
					$saldocaja = $_POST["ingresosv"];
				}
				$dato = array(
					'sede_caja_monto' => ($values->sede_caja_monto + $saldocaja)
				);
				$this->db->where('id_caja',$values->id_caja);
				$this->db->where('id_sede_caja',$values->id_sede_caja);
				$estado=$this->db->update('sede_caja', $dato);
			}
		}else{
			$this->load->view('Error/404');
		}
	}

	public function traerarqueo($empresa,$sede,$sesion_caja,$empleado,$traermovimientos,$consulta_delivery,$ingresos,$egresos,$ingresosc,$egresosc,$deliverytotal,$montocaja){ 
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

	public function llamarfuncion($identificador){
		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa LIMIT 1");
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.id_empleado ='".$_COOKIE['usuario_id']."' and sede_caja.id_Sede ='".$_COOKIE['id_sede']."'  ORDER BY sesion_caja.id_sesion_caja DESC ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;
		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja 
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE  (id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja."	OR id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")
GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento
			 ");

		$traermovimientos = $this->Mantenimiento_m->consulta3("SELECT producto.producto_id,movimiento.mov_id,venta.venta_idventas,compras.compra_id,detalle_venta.estado_descuento,
		IF(detalle_venta.estado_descuento != 'DS','AGRUP',IF(detalle_venta.estado_descuento IS NOT NULL,CONCAT( detalle_venta.estado_descuento, '_', producto.producto_id, '_', detalle_venta.precio ),	movimiento.mov_id ) ) AS agrupador,
		IF( movimiento.mov_formapago = 1, 'F', 'V' ) AS formapago,
		IF	( categoria_producto.categoria_producto_id IS NULL, 'SERVICIOS', categoria_producto.categoria_producto_descripcion ) AS categoria,
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
		LEFT JOIN venta ON venta.venta_idventas = movimiento.venta_idventas
		LEFT JOIN compras ON movimiento.mov_id = compras.compra_idmovimiento
		LEFT JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
		LEFT JOIN detalle_compras ON detalle_compras.compra_id = compras.compra_id
		LEFT JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  
 		LEFT JOIN categoria_producto ON producto.categoria_producto_id = categoria_producto.categoria_producto_id		
 		LEFT JOIN concepto ON movimiento.mov_concepto = concepto.con_id 
		WHERE detalle_venta.estado_pedido=1 and  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")   
		GROUP BY producto.producto_id,tipomov,detalle_venta.estado_descuento,agrupador 
		ORDER BY tipomov,categoria_producto.categoria_producto_id,compras.compra_id,venta.venta_idventas,movimiento.mov_id"); 

		$consulta_delivery=$this->Mantenimiento_m->consulta3(" SELECT 'Costo Delivery' as descripcion,detalle_venta.precio as 'venta_monto_delivery',venta.venta_idventas
from movimiento
LEFT JOIN venta ON venta.venta_idventas = movimiento.venta_idventas
inner join detalle_venta on venta.venta_idventas=detalle_venta.id_venta
where detalle_venta.cod_producto_venta=9999 and
venta.venta_estado!='0' and (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")");




		/*$consulta_delivery = $this->Mantenimiento_m->consulta3("SELECT 'Costo Delivery' as descripcion,venta.venta_monto_delivery,venta.venta_idventas FROM movimiento
			INNER JOIN venta ON venta.venta_idmovimiento = movimiento.mov_id AND venta.venta_monto_delivery > 0 AND movimiento.mov_id = venta.venta_idmovimiento and movimiento.mov_estado = 1
			WHERE (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")");*/



$traermovimientos=array_filter($traermovimientos, function($k) {
    return $k["producto_id"] != 9999;
});

//print_r($traermovimientos);
//exit();





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


	public function cargar_detalle_pagos()
	{


		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa LIMIT 1");
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
		$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.id_empleado ='".$_COOKIE['usuario_id']."' and sede_caja.id_Sede ='".$_COOKIE['id_sede']."'  ORDER BY sesion_caja.id_sesion_caja DESC ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;


		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja 
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE  (id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja."	OR id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")
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
		$concepto_array=$this->db->query($sql,array($sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();		
 
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
 
				$movimiento_array=$this->db->query($sql,array($value["con_id"],$sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();	
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
	$forma_array=$this->db->query($sql,array($sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();	
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

	public function cargar_detalle_pagos_nuevo($token)
	{

  $data_token = json_decode($this->consultar_token($token),true);

		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa LIMIT 1");
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$data_token["empresa_sede"]);
		$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.id_empleado ='".$data_token['empleado_id']."' and sede_caja.id_Sede ='".$data_token['empresa_sede']."'  ORDER BY sesion_caja.id_sesion_caja DESC ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;


		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja 
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE  (id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja."	OR id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")
GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento
			 ");










		$sesion_ultimo_fisico=$sesion_caja[1];
		$sesion_ultimo_virtual=$sesion_caja[0];

		$carga_movimiento=[];

		$sql = "SELECT 
             concepto.con_descripcion AS 'descripcion',
             SUM(IF(tipo_movimiento.id_tipo_movimiento=1, movimiento.mov_monto, 0)) AS 'ingreso',
             SUM(IF(tipo_movimiento.id_tipo_movimiento=2, movimiento.mov_monto, 0)) AS 'egreso',
             tipo_movimiento.id_tipo_movimiento,
             concepto.con_id
        FROM movimiento 
        INNER JOIN concepto 
            ON concepto.con_id = movimiento.mov_concepto
        INNER JOIN tipo_movimiento 
            ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
        WHERE movimiento.mov_estado = 1 
          AND movimiento.id_sesion_caja IN (?, ?)
        GROUP BY concepto.con_id, concepto.con_descripcion, tipo_movimiento.id_tipo_movimiento
        ORDER BY tipo_movimiento.id_tipo_movimiento ASC";

		$c=0;
		//echo $sesion_ultimo_fisico->id_sesion_caja;exit;
		$concepto_array=$this->db->query($sql,array($sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();		
 
		foreach ($concepto_array as $key => $value) {
			//print_r($value);

			$carga_movimiento["movimiento"][$c]["descripcion"]=$value["descripcion"];
			$carga_movimiento["movimiento"][$c]["ingreso"]=$value["ingreso"];
			$carga_movimiento["movimiento"][$c]["egreso"]=$value["egreso"];
			$carga_movimiento["movimiento"][$c]["tipo"]=1;

			$c++;
			$sql = "SELECT 
            CONCAT(movimiento.mov_descripcion, ' ', movimiento.tipo_comprobante_descripcion) AS 'descripcion',
            SUM(IF(tipo_movimiento.id_tipo_movimiento = 1, movimiento.mov_monto, 0)) AS 'ingreso',
            SUM(IF(tipo_movimiento.id_tipo_movimiento = 2, movimiento.mov_monto, 0)) AS 'egreso',
            MAX(tipo_movimiento.id_tipo_movimiento) AS id_tipo_movimiento, -- Usar MAX o cualquier función de agregación
            MAX(concepto.con_id) AS con_id -- Usar MAX o cualquier función de agregación
        FROM movimiento 
        INNER JOIN concepto 
            ON concepto.con_id = movimiento.mov_concepto
        INNER JOIN tipo_movimiento 
            ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
        WHERE movimiento.mov_estado = 1
        AND concepto.con_id = ?
        AND movimiento.id_sesion_caja IN (?, ?)
        GROUP BY movimiento.mov_id
        ORDER BY movimiento.mov_fecha_tiempo DESC";
 
				$movimiento_array=$this->db->query($sql,array($value["con_id"],$sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();	
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
		MAX(tipo_movimiento.id_tipo_movimiento) as id_tipo_movimiento, -- Usar MAX o cualquier función de agregación
		MAX(concepto.con_id) as con_id,
		MAX(formapago.for_descripcion) as formapago_descripcion,
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
	$forma_array=$this->db->query($sql,array($sesion_ultimo_fisico->id_sesion_caja,$sesion_ultimo_virtual->id_sesion_caja))->result_array();	
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


		public function llamarfuncion_nuevo($identificador,$token){

			 $data_token = json_decode($this->consultar_token($token),true);
		$empresa=$this->Mantenimiento_m->consulta3("select * from empresa LIMIT 1");
		$sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$data_token["empresa_sede"]);
		$sesion_caja = $this->db->query("SELECT sesion_caja.id_sesion_caja,(sesion_caja.ses_fechaapertura) as fecha_apertura,empleados.empleado_nombres,
			empleados.empleado_apellidos 
			FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			INNER JOIN empleados ON sesion_caja.id_empleado = empleados.empleado_id WHERE sesion_caja.id_sede_caja = sede_caja.id_sede_caja and sesion_caja.id_empleado ='".$data_token['empleado_id']."' and sede_caja.id_Sede ='".$data_token["empresa_sede"]."'  ORDER BY sesion_caja.id_sesion_caja DESC ")->result();
		$empleado = $sesion_caja[0]->empleado_nombres.' '.$sesion_caja[0]->empleado_apellidos;
		$montocaja = $this->Mantenimiento_m->consulta3("SELECT SUM(movimiento.mov_monto) as monto,concepto.id_tipo_movimiento,
			movimiento.mov_formapago,1 as tipo
			FROM movimiento
			INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
			WHERE  movimiento.mov_estado = 1 and (movimiento.id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja." )
			GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento

			UNION 

			SELECT sesion_caja.ses_montoinicial as monto,1 as id_tipo_movimiento,sede_caja.id_caja as mov_formapago,2 as tipo
			FROM sesion_caja 
			INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
			WHERE  (id_sesion_caja = ".$sesion_caja[0]->id_sesion_caja."	OR id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")
GROUP BY id_sesion_caja, mov_formapago,id_tipo_movimiento
			 ");

		$traermovimientos = $this->Mantenimiento_m->consulta3("SELECT producto.producto_id,movimiento.mov_id,venta.venta_idventas,compras.compra_id,detalle_venta.estado_descuento,
		IF(detalle_venta.estado_descuento != 'DS','AGRUP',IF(detalle_venta.estado_descuento IS NOT NULL,CONCAT( detalle_venta.estado_descuento, '_', producto.producto_id, '_', detalle_venta.precio ),	movimiento.mov_id ) ) AS agrupador,
		IF( movimiento.mov_formapago = 1, 'F', 'V' ) AS formapago,
		IF	( categoria_producto.categoria_producto_id IS NULL, 'SERVICIO', categoria_producto.categoria_producto_descripcion ) AS categoria,
		CASE WHEN venta.venta_idventas IS NOT NULL THEN	movimiento.mov_descripcion 
		ELSE movimiento.mov_descripcion END AS producto_descripcion,
		1 AS cantidad,
		CASE WHEN venta.venta_idventas IS NOT NULL THEN	ROUND(AVG(venta.venta_monto), 2)
		WHEN ( compras.compra_id IS NOT NULL ) THEN	ROUND( AVG( detalle_compras.detalle_compra_preciounitatio ), 2 ) ELSE movimiento.mov_monto 
		END AS precio,
		CASE WHEN venta.venta_idventas IS NOT NULL THEN ROUND( ( 1 * AVG( venta.venta_monto ) ), 2 ) 
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
		LEFT JOIN venta ON venta.venta_idventas = movimiento.venta_idventas
		LEFT JOIN compras ON movimiento.mov_id = compras.compra_idmovimiento
		LEFT JOIN detalle_venta ON detalle_venta.id_venta = venta.venta_idventas
		LEFT JOIN detalle_compras ON detalle_compras.compra_id = compras.compra_id
		LEFT JOIN producto ON detalle_venta.cod_producto_venta = producto.producto_id  
 		LEFT JOIN categoria_producto ON producto.categoria_producto_id = categoria_producto.categoria_producto_id		
 		LEFT JOIN concepto ON movimiento.mov_concepto = concepto.con_id 
		WHERE movimiento.mov_estado = 1 and (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")   
		GROUP BY
		producto.producto_id,
		tipomov,
		detalle_venta.estado_descuento,
		agrupador,
		movimiento.mov_id,
		venta.venta_idventas,
		compras.compra_id,
		movimiento.mov_formapago,
		categoria_producto.categoria_producto_id,
		categoria_producto.categoria_producto_descripcion,
		concepto.id_tipo_movimiento,
		concepto.con_descripcion
		ORDER BY tipomov,categoria_producto.categoria_producto_id,compras.compra_id,venta.venta_idventas,movimiento.mov_id"); 

		$consulta_delivery=$this->Mantenimiento_m->consulta3(" SELECT 'Costo Delivery' as descripcion,detalle_venta.precio as 'venta_monto_delivery',venta.venta_idventas
from movimiento
LEFT JOIN venta ON venta.venta_idventas = movimiento.venta_idventas
inner join detalle_venta on venta.venta_idventas=detalle_venta.id_venta
where detalle_venta.cod_producto_venta=9999 and
venta.venta_estado!='0' and (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")");




		/*$consulta_delivery = $this->Mantenimiento_m->consulta3("SELECT 'Costo Delivery' as descripcion,venta.venta_monto_delivery,venta.venta_idventas FROM movimiento
			INNER JOIN venta ON venta.venta_idmovimiento = movimiento.mov_id AND venta.venta_monto_delivery > 0 AND movimiento.mov_id = venta.venta_idmovimiento and movimiento.mov_estado = 1
			WHERE (movimiento.id_sesion_caja =".$sesion_caja[0]->id_sesion_caja." OR movimiento.id_sesion_caja = ".$sesion_caja[1]->id_sesion_caja.")");*/



$traermovimientos=array_filter($traermovimientos, function($k) {
    return $k["producto_id"] != 9999;
});

//print_r($traermovimientos);
//exit();





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

	public function consultar_token($token){
 //   echo $token;

    //$header  =  $this->input->request_headers('authorization');
   // $token="";

   /*if(isset($header['Authorization']))
   {
  $token= $header['Authorization'];
   }else{


      $token= $header['authorization'];
   }*/


    //echo $tokenparametro;exit();

    try {

      ///echo $tokenparametro."";exit();

      $jwtData = $this->objOfJwt->DecodeToken($token);



      return json_encode($jwtData);

    } catch (Exception $e) {

      http_response_code('401');

      echo json_encode(array("status" => false,"message" => $e->getMessage()));exit();

    }

  }


}
?>