
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class movimiento extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index(){
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Movimientos";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select movimiento.mov_id,movimiento.mov_fecha,movimiento.mov_hora,caja.caja_descripcion,concepto.con_descripcion,tipo_movimiento.tipo_movimiento_descripcion,movimiento.mov_monto,movimiento.mov_descripcion,venta.venta_idventas,compra_id
        FROM
        sede_caja
        INNER JOIN sesion_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja
        INNER JOIN movimiento ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja
        INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
        INNER JOIN tipo_movimiento ON concepto.id_tipo_movimiento = tipo_movimiento.id_tipo_movimiento
        INNER JOIN caja ON sede_caja.id_caja = caja.id_caja
        LEFT JOIN venta ON movimiento.mov_id = venta.venta_idmovimiento 
		LEFT JOIN compras ON movimiento.mov_id = compras.compra_idmovimiento 
        where sede_caja.id_sede=".$_COOKIE["id_sede"]." and movimiento.mov_estado = 1 order by mov_fecha_tiempo DESC");
	  $this->vista("Movimiento/index",$data);
	}

    public function new_movimiento($id){

      
           $sql="select * from concepto where con_estado=1 and id_sede=".$_COOKIE["id_sede"]." and con_id>2 and id_tipo_movimiento=".$id;

  $sql="select * from concepto,tipo_movimiento where concepto.id_tipo_movimiento=tipo_movimiento.id_tipo_movimiento and con_estado_visible=1 and con_estado=1 and (id_sede IS NULL  or id_sede=".$_COOKIE["id_sede"].") and concepto.id_tipo_movimiento=".$id;





      $conceptos = $this->db->query($sql)->result_array();
      $tipo_moneda = $this->Mantenimiento_m->consulta3("SELECT * FROM monedas where monedas.moneda_estado = 1");
      $formapagos = $this->db->query("select * from formapago where for_estado=1")->result_array();
      $caja=$this->Mantenimiento_m->consulta3("select * from caja where caja_estado=1");
      $proveedor=$this->Mantenimiento_m->consulta3("SELECT * FROM proveedores where proveedores.empresa_ruc=". $_COOKIE["ruc_empresa"]);
      
      if($id==1){
       $text=" ingreso";
       $tipo_comprobante=$this->Mantenimiento_m->consulta3("select * FROM
      tipo_documento
      INNER JOIN documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id
      INNER JOIN detalle_doc_sede ON detalle_doc_sede.detalle_id_documento = documento.id_documento
      where detalle_id_sede=".$_COOKIE["id_sede"]." and tipo_documento.tipodoc_id IN(1,2,5)");
      }else{
         $text=" egreso";
         $tipo_comprobante=$this->Mantenimiento_m->consulta3("select * from  tipo_documento WHERE tipo_documento.tipodoc_id != 0");
      }
           $data["titulo_descripcion"]="Nuevo movimiento".$text;
      $data["conceptos"]=$conceptos;
      $data["formapagos"]=$formapagos;
      $data["caja"]=$caja;
      $data["proveedor"]=$proveedor;
      $data["tipo_comprobante"]=$tipo_comprobante;
      $data["moneda"] = $tipo_moneda;
      $data['id']=$id;
      $this->vista('Movimiento/nuevo',$data);
    
  }

 

  public function save_movimiento(){
    $monto_total = $this->input->post("monto");
    if($this->input->post("moneda")==2){
      $monto_total = $this->input->post("monto")*$this->input->post("cambio");
    }

    if ($this->input->post("tipomovi") == 1 && $this->input->post("id_tipo_comprobante") == 0 ) {
      $id = $this->generar_movimiento($this->input->post("caja"),$monto_total,$this->input->post("formapago"),$this->input->post("concepto"),$this->input->post("descripcion"),$this->input->post("tipomovi"),$this->input->post("id_tipo_comprobante"),$this->input->post("descripcion_comprobante"),$this->input->post("id_proveedor"),null,$this->input->post("igv"),9,$this->input->post("moneda"),$this->input->post("cambio"));
    }
    if ($this->input->post("tipomovi") == 1 && $this->input->post("id_tipo_comprobante") != 0 ) {
      if ($this->input->post("formapago") == 1) {
        $id = 4;
      }else{
        $id = 3;
      }
      $id = $this->generar_movimiento($this->input->post("caja"),$monto_total,$this->input->post("formapago"),$this->input->post("concepto"),$this->input->post("descripcion"),$this->input->post("tipomovi"),$this->input->post("id_tipo_comprobante"),$this->input->post("descripcion_comprobante"),$this->input->post("id_proveedor"),null,$this->input->post("igv"),$id,$this->input->post("moneda"),$this->input->post("cambio"));
    }
    if ($this->input->post("tipomovi") == 2) {

      if($this->input->post("anio_periodo")=="" && $this->input->post("mes_periodo")==""){
        $id = $this->generar_movimiento($this->input->post("caja"),$monto_total,$this->input->post("formapago"),$this->input->post("concepto"),$this->input->post("descripcion"),$this->input->post("tipomovi"),$this->input->post("id_tipo_comprobante"),$this->input->post("descripcion_comprobante"),$this->input->post("id_proveedor"),null,$this->input->post("igv"),null,$this->input->post("moneda"),$this->input->post("cambio"));
      }
      else{
        $mes=$this->input->post("mes_periodo");
        if(strlen($mes)==1)
        {
         $mes="0".$mes;
        }
   $id = $this->generar_movimiento($this->input->post("caja"),$monto_total,$this->input->post("formapago"),$this->input->post("concepto"),$this->input->post("descripcion")." DEL PERIODO ".$this->input->post("anio_periodo")."-".$mes,$this->input->post("tipomovi"),$this->input->post("id_tipo_comprobante"),$this->input->post("descripcion_comprobante"),$this->input->post("id_proveedor"),null,$this->input->post("igv"),null,$this->input->post("moneda"),$this->input->post("cambio"),$this->input->post("anio_periodo"),$mes);

      }

      $auxiliar_movimiento=$this->Mantenimiento_m->consulta3("SELECT proveedores.proveedor_razonsocial, proveedores.proveedor_ruc, proveedores.proveedor_id FROM proveedores where proveedores.proveedor_id=".$this->input->post("id_proveedor"));
        $movimiento = array(
        "mov_documento" => $auxiliar_movimiento[0]["proveedor_ruc"],
        "mov_razonsocial" => $auxiliar_movimiento[0]["proveedor_razonsocial"]
      );
        $this->db->where('mov_id',$id);
      $this->db->update('movimiento', $movimiento);
    }
    
      $movimiento = array(
        "mov_documento" => $this->input->post("documento"),
        "mov_razonsocial" => $this->input->post("razon_social")
      );
      $this->db->where('mov_id',$id);
      $this->db->update('movimiento', $movimiento);
      echo $id;
  }


   public function buscar_proveedor(){
    $term="";
if(isset($_GET["term"])){
$term=$_GET["term"];
}

    $data=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and (proveedor_razonsocial like '%".$term."%' or proveedor_ruc like '%".$term."%') ");
        $data1=array();
    foreach ($data as $key => $value) {
      $data1["results"][$key]["id"]=$value["proveedor_id"];
      $data1["results"][$key]["text"]=$value["proveedor_razonsocial"];
    }
  
    echo json_encode($data1);
  }


  public function eliminar($idmovimiento){ 
  	$data=$this->Mantenimiento_m->consulta2("select mov_monto,id_sede_caja,concepto.id_tipo_movimiento from movimiento 
  		INNER JOIN sesion_caja ON movimiento.id_sesion_caja = sesion_caja.id_sesion_caja  
  		INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id
  		where mov_id=".$idmovimiento." AND mov_estado = 1 ");

  	if(isset($data)){
  		$monto_dinero = $data->mov_monto;
	  	$idsedecaja = $data->id_sede_caja;
	  	$tipo_movimiento = $data->id_tipo_movimiento;
	  	if($tipo_movimiento == 1){
	  		$operacion = "sede_caja_monto - ".$monto_dinero;
	  	}else{
	  		$operacion = "sede_caja_monto + ".$monto_dinero;
	  	}
	  	$this->Mantenimiento_m->consulta1("UPDATE sede_caja SET sede_caja_monto = ".$operacion."
	  		WHERE id_sede_caja = ".$idsedecaja);
	  	$this->Mantenimiento_m->consulta1("UPDATE movimiento SET mov_estado = 0
	  		WHERE mov_id = ".$idmovimiento);
  	}
  	
  }

}
?>