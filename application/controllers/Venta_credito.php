 

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Venta_credito extends BaseController {

 public function __construct() {
  parent::__construct();


}

public function index()
{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");

 $data=array();
 $data["titulo_descripcion"]="Lista de Credito de Ventas";
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from categoria where categoria_estado=1 and id_sede=".$_COOKIE["id_sede"]);

 $this->vista("Venta_credito/index",$data);
}

public function buscar_tabla(){

  $columns = array( 
    0 =>'Cliente', 
    1 =>'Comprobante',
    2=> 'Monto',

    3=> 'Tipo',
    4=>'Acciones',
  );
  $limit = $this->input->post('length');
  $start = $this->input->post('start');
  $order = $columns[$this->input->post('order')[0]['column']];
  $dir = $this->input->post('order')[0]['dir'];
  $d=$this->input->post('order')[0]["column"];

  $lista=$this->db->query("SELECT clientes.cliente_nombres AS nombre,clientes.cliente_dni,CONCAT('Cliente') AS tipo,
   venta.venta_monto,venta.venta_idventas,CONCAT(venta.venta_num_serie,'-',venta.venta_num_documento) AS venta_numcomprobante,tipo_pago.tipo_pago_descripcion FROM clientes
   INNER JOIN venta ON venta.venta_codigocliente = clientes.cliente_id
   INNER JOIN cuotaventa ON cuotaventa.cuo_venta = venta.venta_idventas
   INNER JOIN tipo_pago ON venta.venta_tipopago = tipo_pago.tipo_pago_id
   WHERE  venta.venta_idsede = ".$_COOKIE["id_sede"]." and venta.venta_credito_estado = 1
   GROUP BY venta.venta_idventas
   ORDER BY venta.venta_monto ASC")->result();
  $formar_order="";
  if( $d==0){
    $formar_order="clientes.cliente_dni";
  }if( $d==1){
    $formar_order="compras.compra_numcomprobante";
  }if( $d==2){
    $formar_order="compras.compra_total";
  }if( $d==3){
    $formar_order="tipo_pago.tipo_pago_descripcion";
  }
  $totalFiltered = count($lista); 
  $totalData = count($lista); 
  $data;
  if(empty($this->input->post('search')['value'])){ 
    $sql="SELECT clientes.cliente_nombres AS nombre,clientes.cliente_dni,CONCAT('Cliente') AS tipo,
    venta.venta_monto,venta.venta_idventas, CONCAT(venta.venta_num_serie,'-',venta.venta_num_documento) AS venta_numcomprobante,tipo_pago.tipo_pago_descripcion FROM clientes
    INNER JOIN venta ON venta.venta_codigocliente = clientes.cliente_id
    INNER JOIN cuotaventa ON cuotaventa.cuo_venta = venta.venta_idventas
    INNER JOIN tipo_pago ON venta.venta_tipopago = tipo_pago.tipo_pago_id
    WHERE venta.venta_idsede = ".$_COOKIE["id_sede"]." and venta.venta_credito_estado = 1
    GROUP BY venta.venta_idventas
    ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;
    $lista_datos=$this->db->query($sql);
    if($lista_datos->num_rows()>0){
      $data= $lista_datos->result(); 
    }else{
     $data= null;
   }
 }
 else{
          // cuando  existe buscar
   $search = $this->input->post('search')['value']; 
   $sql="SELECT * from compras,almacenes,proveedores,tipo_pago where 
   compras.proveedor_id=proveedores.proveedor_id and
   almacenes.almacen_id=compras.almacen_id and  compras.compra_credito_estado=1 and compras.id_tipo_pago=2 and  compras.estado=1 and tipo_pago.tipo_pago_id=compras.id_tipo_pago and (compras.compra_numcomprobante like '%". $search."%' or proveedores.proveedor_razonsocial like '%". $search."%'  ) and almacenes.id_sede=".$_COOKIE["id_sede"]."";
   $dat=$this->db->query($sql);
   $sql="SELECT * from compras,almacenes,proveedores,tipo_pago where 
   compras.proveedor_id=proveedores.proveedor_id and
   almacenes.almacen_id=compras.almacen_id and compras.estado=1 and  compras.compra_credito_estado=1 and compras.id_tipo_pago=2 and tipo_pago.tipo_pago_id=compras.id_tipo_pago and (compras.compra_numcomprobante like '%". $search."%' or proveedores.proveedor_razonsocial like '%". $search."%'  ) and almacenes.id_sede=".$_COOKIE["id_sede"]."
   ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;
   $lista_datos=$this->db->query($sql);

   if($lista_datos->num_rows()>0){
    $data= $lista_datos->result(); 
    $totalFiltered = $dat->num_rows();
  }else{
    $data= null;
    $totalFiltered = 0;
  }



}




$data1 = array();
if(!empty($data))
{
  foreach ($data as $post)
  {
    $html1="";

    $nestedData['proveedor'] = $post->nombre;
    $nestedData['comprobante'] = $post->venta_numcomprobante;
    $nestedData['monto'] = $post->venta_monto;
    $nestedData['tipo'] = $post->tipo_pago_descripcion;
    $nestedData['acciones'] ='<button onclick="ir_pagar('.$post->venta_idventas.')" class="btn btn-success  btn-xs"><i class="fa fa-money"></i> <span>Pagar</span> </button>';


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

public function pago($id)
{
 $data=array();
 $data["titulo_descripcion"]="Cobrar Credito";
 $data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT * from cuotaventa where cuo_venta= ".$id);
 $data["forma_pago"]=$this->Mantenimiento_m->consulta3("select * from  formapago where for_estado=1");
 $data["tipo_comprabante"]=$this->Mantenimiento_m->consulta3("select * from  tipo_comprobante_c where tipo_comprobante_estado=1");
 $data["numcomprobante"] = $this->Mantenimiento_m->consulta2("SELECT venta.ventas_idtipodocumento,CONCAT(venta.venta_num_serie,'-',venta.venta_num_documento) as num_boleta
   FROM venta WHERE venta.venta_idventas = ".$id);
 $data["id_venta"]=$id;
 $this->load->view("Venta_credito/nuevo.php",compact("data"));
}

public function procesar_cobro(){

  $idcaja=0;

  if($_POST["forma_pago"]==1){
   $idcaja=1;
 }
 else{
  $idcaja=2;
}
$movimiento=$this->generar_movimiento($idcaja,$_POST["monto"],$_POST["forma_pago"],1,"PAGO DE VENTA DE CREDITO",1,$_POST["comprobante"],
  $_POST["ncomprobante"]);
if($movimiento!=0)
{


 $cuotas = $this->db->query("select * from cuotaventa where cuo_venta=".$_POST["idventa"]." and cuo_ventaestado=1 order by cuo_ventanrocuota")->result_array();
 $monto = $_POST['monto'];

 foreach ($cuotas as $value) {
  if ($monto == 0) {
    break;
  }else{
   if ((double)$monto>=((double)$value['cuo_ventamontocuota']-(double)$value['cuo_ventamontopagado'])){

     $data = array(
       'amo_cuotaventa' => $value['cuo_ventaid'],
       'amo_movimiento' => $movimiento,
       'amo_monto' => ($value['cuo_ventamontocuota']-$value['cuo_ventamontopagado'])
     );
     $this->db->insert('amortizacionventa', $data);

     $data = array(
       'cuo_ventafechacancelado' => $_POST['fechapago'],
       'cuo_ventamontopagado' => $value['cuo_ventamontocuota'],
       'cuo_ventaestado' => 0
     );
     $this->db->where('cuo_ventaid', $value['cuo_ventaid']);
     $this->db->update('cuotaventa', $data);

     $monto = $monto - ($value['cuo_ventamontocuota']-$value['cuo_ventamontopagado']);
   }else{
     $data = array(
       'amo_cuotaventa' => $value['cuo_ventaid'],
       'amo_movimiento' => $movimiento,
       'amo_monto' => $monto
     );
     $this->db->insert('amortizacionventa', $data);

     $data = array(
       'cuo_ventamontopagado' => ($value['cuo_ventamontopagado'] + $monto)
     );
     $this->db->where('cuo_ventaid', $value['cuo_ventaid']);
     $this->db->update('cuotaventa', $data);

     $monto = 0;
   }
 }
} 

$cancel = $this->db->get_where('cuotaventa', array('cuo_venta' => $_POST["idventa"],'cuo_ventaestado' =>1))->result_array();
if (count($cancel)==0) {
  $data = array(
   'venta_credito_estado' => 0
 );
  $this->db->where('venta_idventas', $_POST["idventa"]);
  $this->db->update('venta', $data);
}


$traer_auxiliar = $this->Mantenimiento_m->consulta2("SELECT clientes.cliente_nombres,clientes.cliente_dni
  FROM venta INNER JOIN clientes ON venta.venta_codigocliente = clientes.cliente_id WHERE venta.venta_idventas = ".$_POST["idventa"]);
$updatemovimiento = array(
  "mov_documento" => $traer_auxiliar->cliente_dni,
  "mov_razonsocial" => $traer_auxiliar->cliente_nombres
);  
$this->db->where('mov_id',$movimiento);
$this->db->update('movimiento', $updatemovimiento);

echo 1;
}
else
{
  echo 0;
}
}

}