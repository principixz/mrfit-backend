

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "BaseController.php";
//require_once "Automatica.php";

class Compra extends BaseController {

 public function __construct() {
        parent::__construct();
    }

  public function index()
  {
   // echo  $this->input->post("usuario")." ". $this->input->post("clave");
    $data=array();
      $data["titulo_descripcion"]="Lista de Compras";
    $this->vista("Compra/index",$data);
  }

  public function nuevo()
  {
    $data=array();
      $data["titulo_descripcion"]="Nueva Compra";
         
           $data["formapago"]=$this->Mantenimiento_m->consulta3("select * from formapago");
          // $data["tipopago"]=$this->Mantenimiento_m->consulta3("select * from tipo_pago");
            $data["almacen"]=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
            $data["moneda"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado=1");
            //$data["categoria"]=$this->Mantenimiento_m->consulta3("select * from categoria where categoria_estado=1 and id_sede=".$_COOKIE["id_sede"]);
            $data["tipo_documento"]=$this->Mantenimiento_m->consulta3("select * from tipo_documento where tipo_documento.tipodoc_id<> 11 and tipo_documento.tipodoc_id<> 12");
           //$data["credito_fiscal"]=$this->Mantenimiento_m->consulta3("select sede_creditofiscal from sede where sede.id_sede=".$_COOKIE["id_sede"]);
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
    $this->vista("Compra/nuevo",$data);
  }

  public function guardar()
  {
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

  public function eliminar($id)
  {
      $this->Mantenimiento_m->consulta1("update categoria set categoria_estado=0 where categoria_id=".$id);
  } 

  /*public function buscar_proveedor(){
    $data=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and proveedor_razonsocial like '%".$this->input->get("term")."%' limit 20");
        $data1=array();
    foreach ($data as $key => $value) {
      $data1["results"][$key]["id"]=$value["proveedor_id"];
      $data1["results"][$key]["text"]=$value["proveedor_razonsocial"];
    }
    echo json_encode($data1);
  }*/

  function cronograma_prestamo(){
      $monto_prest = $_POST["monto"];
      $cuotas=$_POST["cuotas"];
      $intervalo = $_POST["intervalo"];
      $fecha = $_POST["fecha_compra"];
      $this->load->view('Compra/cronograma',compact('monto_prest','cuotas','intervalo','fecha'));
  }

public function buscar()

  {
       $html="";
       $tipo=0;
      $datos=array();
          $moneda=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_id=".$_POST["tipo"]);
          $data1=$this->Mantenimiento_m->consulta3("select CONCAT('I-',insumo.insumo_id)as insumo_id, insumo.insumo_descripcion,insumo.insumo_precio_compra,  insumo.unidad_medida_id,  insumo.tipo_unidad_medida_id, unidad_medida.unidad_medida_abreviatura from insumo,unidad_medida where insumo.unidad_medida_id=unidad_medida.id_unidad_medida and   insumo_estado=1 and insumo_codigobarras='".$this->input->post("valor")."'  and id_sede=".$_COOKIE["id_sede"]." UNION SELECT CONCAT('P-',producto.producto_id) AS insumo_id, producto.producto_descripcion_ AS insumo_descripcion, producto.producto_preciocompra AS insumo_precio_compra, producto.unidad_medida_id AS unidad_medida_id,producto.tipo_unidad_medida_id AS tipo_unidad_medida_id, unidad_medida.unidad_medida_abreviatura FROM producto, unidad_medida WHERE producto.unidad_medida_id = unidad_medida.id_unidad_medida  AND producto.producto_estado = 1  AND producto_codigobarra = '".$this->input->post("valor")."' AND id_sede =".$_COOKIE["id_sede"]);
     foreach ($data1 as $key => $value) {
      $tipo=1;
      $id=$value["insumo_id"];
      $descripcion=$value["insumo_descripcion"];
      $precio=$value["insumo_precio_compra"];
      $datos["tipo"]=$tipo;
            $datos["id"]=$id;
            $datos["descripcion"]=$descripcion;
            $datos["precio"]=$precio;
             $datos["unidad_medida"]=$value["unidad_medida_id"];
             $datos["tipo_unidad_medida"]=$value["tipo_unidad_medida_id"];
             $datos["abreviatura"]=$value["unidad_medida_abreviatura"];
            echo json_encode($datos);
      exit();
     }

     $data=$this->Mantenimiento_m->consulta3("select CONCAT('I-',insumo.insumo_id) as insumo_id, insumo.insumo_descripcion,insumo.insumo_precio_compra,  insumo.unidad_medida_id,  insumo.tipo_unidad_medida_id, unidad_medida.unidad_medida_abreviatura,insumo.insumo_imagen from insumo,unidad_medida where insumo.unidad_medida_id=unidad_medida.id_unidad_medida and  insumo_estado=1 and (insumo_descripcion LIKE '%".$this->input->post("valor")."%' or insumo_codigobarras LIKE '%".$this->input->post("valor")."%' ) and id_sede=".$_COOKIE["id_sede"]. " UNION SELECT CONCAT('P-',producto.producto_id) AS insumo_id, producto.producto_descripcion_ AS insumo_descripcion, producto.producto_preciocompra AS insumo_precio_compra, producto.unidad_medida_id AS unidad_medida_id,producto.tipo_unidad_medida_id AS tipo_unidad_medida_id, unidad_medida.unidad_medida_abreviatura, producto.producto_imagen as insumo_imagen FROM producto, unidad_medida WHERE producto.unidad_medida_id = unidad_medida.id_unidad_medida  AND producto.producto_estado = 1  AND producto_descripcion_ like '%".$this->input->post("valor")."%' AND id_sede =".$_COOKIE["id_sede"]);


     $html.= '<div class="row">';

      foreach ($data as $key => $value) {
        $url="";
        if($value["insumo_imagen"]!="")
        {
            $url= base_url().'public/img/'.$value['insumo_imagen'];
        }
        else{
             $url= base_url().'public/img/default.jpg';
        }
                        //$a=$value["insumo_id"].",'".$value["insumo_descripcion"]."'".",".$value["tipo_unidad_medida_id"].",".$value["unidad_medida_id"];
    $a="'".$value["insumo_id"]."','".$value["insumo_descripcion"]."',".$value["insumo_precio_compra"].",".$value["unidad_medida_id"].",".$value["tipo_unidad_medida_id"].",'".$value["unidad_medida_abreviatura"]."'";
              $html.=  '<div class="col-md-3">';
             $html.=  '<div class="panel panel-default card-view pa-0">';
             $html.=  '<div class="panel-wrapper collapse in">';
                 $html.=  '<div class="panel-body pa-0">';
                 $html.=  '<article class="col-item">';
                   $html.= '  <div class="photo">';
                 $html.=  '<a href="javascript:void(0);" onclick="agregar_compra('.$a.')"> <img style="width: 125px;height: 125px;" src="'.$url.'" class="img-responsive" alt="Product Image"> </a>
                    </div>
                    <div class="info">
                      <h6 style="font-size: 11px;"">'.$value['insumo_descripcion'].'</h6>
                      <span style="font-size: 10px;"class="head-font block text-warning font-16">'.$moneda[0]['moneda_simbolo'].' '.$value['insumo_precio_compra'].'</span>
                    </div>
                  </article>
                </div>
              </div>
            </div>  
          </div>';  
      }
       $html.= "</div>";
       $datos["tipo"]=2;
       $datos["html"]=$html;
       echo json_encode($datos);
  }

  
/*
public function procesar_compra()
{
  $post=file_get_contents("php://input");  
  $res=json_decode($post, true);
  $compras_producto=array();
  $compras_pago=array();
  $id_caja=0;
  $monto=0;

  parse_str($res["compra_producto"], $compras_producto);
  parse_str($res["compra_pago"], $compras_pago);
//  print_r($compras_pago);
$igv=0;
if(isset($_POST["igv"])){
  $igv=1;
  }

if($compras_pago["forma_pago"]==1){
   $id_caja=1;
  }else{
   $id_caja=2;
  }

$total_parcial=0;
$cargo_igv=0;
$total=0;



foreach ($compras_producto["precio"] as $key => $value) {
  $total+=($value*$compras_producto["cantidad"][$key])-$compras_producto["descuento"][$key];
  }
  $total = round($total,1);
if($igv==0){
  $total_parcial=$total;
  }
else{
  $cargo_igv=$total*0.18;
  $total_parcial=$total-$cargo_igv;
}



$monto=$total;
 




$formapago=$compras_pago["forma_pago"];
$concepto=2;
$descripcion="PAGO DE COMPRA";
$tipomovimiento=2;
$id_tipo_comprobante=$compras_pago["tipo_comprabante"];
$descripcion_comprobante=$compras_pago["serie"].'-'.$compras_pago["correlativo"];
$id_movimiento=0;
$id_movimiento_transporte=0;
$tipo_moneda=$compras_producto["tipo_moneda"];
$cambio=$compras_producto["cambio"];




$cambio_total=1;
    if($tipo_moneda!=1)
    {
      $cambio_total=$cambio;
    }

$monto=$monto*$cambio_total;



if($compras_pago["tipo_pago"]==1){
//forma pago


   $id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,$descripcion_comprobante,$compras_producto["id_proveedor"],null, $igv, null, $tipo_moneda, $cambio);
    if($id_movimiento==0){
      echo 2;exit();
    }else{
      $id_movimiento;
     }

  
}else{
  $this->config->set_item('id_comprobante_caja', null );
}
if(isset($compras_pago["transporte_factura"])){
  $id_movimiento_transporte=$this->generar_movimiento($id_caja,$compras_pago["monto_transporte"],$formapago,53,"Pago de Transporte en una compra",$tipomovimiento,$id_tipo_comprobante,$compras_pago["factura_transporte"], null, null, $igv, null, $tipo_moneda, $cambio);
}
   $data=array(
     "proveedor_id"=>$compras_producto["id_proveedor"],
     "almacen_id"=>$compras_producto["almacen"],
     "moneda_id"=>$tipo_moneda,
     "compra_fechaingresoalmacen"=>date("Y-m-d H:i:s"),
     "tipodoc_id"=>$compras_pago["tipo_comprabante"],
     "compra_fechacomprobante"=>$compras_producto["fecha_compra"],
     "compra_numcomprobante"=>$compras_pago["serie"]."-".$compras_pago["correlativo"],
     "compra_numguiaproveedor"=>$compras_pago["guia_proveedor"],
     "compra_numcfacttransporte"=>$compras_pago["factura_transporte"],
     "compra_numguiatransporte"=>$compras_pago["guia_transporte"],
     "compra_importe"=>$total_parcial,
     "compra_igv"=>$cargo_igv,
     "compra_total"=>$total,
      "compra_idmovimiento"=>$id_movimiento ,
     "id_tipo_pago"=>$compras_pago["tipo_pago"],
     "id_empleado"=>$_COOKIE["usuario_id"],
     "compra_monto_transporte"=>$compras_pago["monto_transporte"],
     "compra_fecha_transporte"=>$compras_pago["fecha_transporte"],
     "compra_movimiento_transporte"=>$id_movimiento_transporte,
     "compra_serie_comprobante"=>$compras_pago["serie"],
     "compra_correlativo_comprobante"=>$compras_pago["correlativo"],
     "compra_monto_cambio"=>$cambio,
     "id_sede"=>$_COOKIE["id_sede"],
     "compra_forma_pago"=>$compras_pago["forma_pago"]
   );
   $this->Mantenimiento_m->insertar("compras",$data);
   $compras=$this->Mantenimiento_m->consulta3("select max(compra_id) as id_compra from compras ");
   $id_compra=$compras[0]["id_compra"];
if($compras_pago["tipo_pago"]==2)
{
   $dato=array(
    "compra_credito_estado"=>1,
    "compra_credito_cuotas"=> $compras_pago["cuotas"]
   );
  $this->db->where('compra_id',$id_compra);
                $estado=$this->db->update('compras', $dato);
    foreach ($compras_pago["nrocuotas"] as $key => $value) {
       $datos=array(
         "cuo_compra"=> $id_compra,
         "cuo_nrocuota"=>$value,
         "cuo_fechavence"=>$compras_pago["fechavence"][$key] ,
         "cuo_montocuota"=>$compras_pago["montocuota"][$key] ,
         "cuo_montopagado"=>0,
         "cambio_dolar"=>$cambio,
         "tipo_moneda"=>$tipo_moneda,
       );
         $this->Mantenimiento_m->insertar("cuotacompra", $datos);
    }
}
   foreach ($compras_producto["id_insumo"] as $key => $value) {
//$this->actualizar_stock($value,$compras_producto["cantidad"][$key],$compras_producto["almacen"],$compras_producto["id_unidad_medida"][$key]);
    $subtotal=0;
    $precio=0;
    $igv_detalle=0;
        $cambio_total=1;
    if($tipo_moneda!=1)
    {
      $cambio_total=$cambio;
    }
    $subtotal=$compras_producto["cantidad"][$key]*$compras_producto["precio"][$key]*$cambio_total;
     if($igv==0){
           $precio=$subtotal;
     }else{
          $igv_detalle=$subtotal*0.18;
          $precio=$subtotal-$igv_detalle;
     }
     $decodificando = explode("-", $value);
     if($decodificando[0]=='I'){
      $datos=array(
         "compra_id"=>$id_compra,
         "producto_id"=>$decodificando[1],
         "um_id"=>$compras_producto["id_unidad_medida"][$key],
         "dc_cantidad"=>$compras_producto["cantidad"][$key],
         "dc_preciounitatio"=>$compras_producto["precio"][$key]*$cambio_total,
         "dc_precio"=>$precio ,
         "dc_igv"=>$igv_detalle,
         "dc_subtotal"=>$subtotal,
         "dc_tipocompra"=>"I",
         "valor_descuento"=>$compras_producto["descuento"][$key]
      );
        $this->Mantenimiento_m->insertar("detalle_compras",$datos);
  $this->actualizar_stock($decodificando[1],$compras_producto["cantidad"][$key],$compras_producto["almacen"],$compras_producto["id_unidad_medida"][$key],$subtotal,$compras_pago["serie"],$compras_pago["correlativo"],$compras_producto["precio"][$key]*$cambio_total,$compras_pago["tipo_comprabante"]);
     }else{
      $datos=array(
         "compra_id"=>$id_compra,
         "insumo_id"=>$decodificando[1],
         "um_id"=>$compras_producto["id_unidad_medida"][$key],
         "dc_cantidad"=>$compras_producto["cantidad"][$key],
         "dc_preciounitatio"=>$compras_producto["precio"][$key]*$cambio_total,
         "dc_precio"=>$precio ,
         "dc_igv"=>$igv_detalle,
         "dc_subtotal"=>$subtotal,
         "dc_tipocompra"=>"P",
         "valor_descuento"=>$compras_producto["descuento"][$key]
      );
        $this->Mantenimiento_m->insertar("detalle_compras",$datos);
$this->actualizar_stock_producto($decodificando[1],$compras_producto["cantidad"][$key],$compras_producto["almacen"],$compras_producto["id_unidad_medida"][$key],$subtotal,$compras_pago["serie"],$compras_pago["correlativo"],$compras_producto["precio"][$key]*$cambio_total,$compras_pago["tipo_comprabante"]);
     }
   }
   if($compras_pago["tipo_comprabante"]!=0){
    $this->relacion_cuentas($id_compra, "11",null,$cambio);  
   }
echo 1;
}

*/

 public function generar_unidad_medida()

 {

  $dat=$this->Mantenimiento_m->consulta3("select * from unidad_medida where id_tipo_unidad_medida=".$this->input->post("id")." and unidad_medida_estado=1");

  echo "<div class='row'><div class='col-md-12'><select class='form-control' id='unidad_medad_seleccionar' name='unidad_medad_seleccionar'>";



  foreach ($dat as $key => $value) {

    echo "<option value='".$value["id_unidad_medida"]."/".$value["unidad_medida_abreviatura"]."'>".$value["unidad_medida_descripcion"]."</option>";

  }

  echo "</select></div></div>";

 }



 public function buscar_tabla()

 {
    $columns = array(  0=>'proveedor', 
                            1=>'proveedor', 
                            2 =>'comprobante',
                            3=> 'monto',
                            4=> 'tipo',
                             5=> 'fecha',
                           6=>'acciones',
                        );
    $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
        $d=$this->input->post('order')[0]["column"];
         $lista=$this->db->query("SELECT * from compras,almacenes,proveedores,tipo_pago where 
compras.proveedor_id=proveedores.proveedor_id and
almacenes.almacen_id=compras.almacen_id and compras.estado=1 and tipo_pago.tipo_pago_id=compras.id_tipo_pago and almacenes.id_sede=".$_COOKIE["id_sede"])->result();
$formar_order="";
if( $d==0)
{
  $formar_order="compras.compra_id";
}
if( $d==1)
{
  $formar_order="proveedores.proveedor_razonsocial";
}
if( $d==2)
{
  $formar_order="compras.compra_numcomprobante";
}
if( $d==3)
{
  $formar_order="compras.compra_total";
}
if( $d==4)
{
  $formar_order="tipo_pago.tipo_pago_descripcion";
}
if( $d==5)
{
  $formar_order="compras.compra_fechaingresoalmacen";
}
if( $d==6)
{
  $formar_order="compras.compra_id";
}
          $totalFiltered = count($lista); 
           $totalData = count($lista); 
            $data;
          if(empty($this->input->post('search')['value']))
        { // cuando no existe buscar
                     $sql="SELECT * from compras,almacenes,proveedores,tipo_pago where 
                      compras.proveedor_id=proveedores.proveedor_id and
                      almacenes.almacen_id=compras.almacen_id and (compras.estado=1 or compras.estado=2) and tipo_pago.tipo_pago_id=compras.id_tipo_pago and almacenes.id_sede=".$_COOKIE["id_sede"]."
                      ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;
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
           $sql="SELECT * from compras,almacenes,proveedores,tipo_pago where 
                      compras.proveedor_id=proveedores.proveedor_id and
                      almacenes.almacen_id=compras.almacen_id and (compras.estado=1 or compras.estado=2) and tipo_pago.tipo_pago_id=compras.id_tipo_pago and (compras.compra_numcomprobante like '%". $search."%' or proveedores.proveedor_razonsocial like '%". $search."%'  ) and almacenes.id_sede=".$_COOKIE["id_sede"]."";
            $dat=$this->db->query($sql);
           $sql="SELECT * from compras,almacenes,proveedores,tipo_pago where 
                      compras.proveedor_id=proveedores.proveedor_id and
                      almacenes.almacen_id=compras.almacen_id and (compras.estado=1 or compras.estado=2) and tipo_pago.tipo_pago_id=compras.id_tipo_pago and (compras.compra_numcomprobante like '%". $search."%' or proveedores.proveedor_razonsocial like '%". $search."%'  ) and almacenes.id_sede=".$_COOKIE["id_sede"]."
                      ORDER BY ".$formar_order." ".$dir." limit ".$start.",".$limit;
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
            $data1 = array();
        if(!empty($data))
        {
            foreach ($data as $post)
            {

                $moneda=$this->Mantenimiento_m->consulta3("select * FROM
                    compras
                    INNER JOIN monedas ON compras.moneda_id = monedas.moneda_id
                    where compra_id=".$post->compra_id);
                                   


              $html1="";
                $nestedData['id'] = $post->compra_id;
                $nestedData['proveedor'] = $post->proveedor_razonsocial;
                $nestedData['comprobante'] = $post->compra_serie_comprobante."-".$post->compra_correlativo_comprobante;
                $nestedData['monto'] = $moneda[0]["moneda_simbolo"]." ".$post->compra_total;
                $nestedData['tipo'] = $post->tipo_pago_descripcion;
                $nestedData['fecha'] = date("d/m/Y", strtotime($post->compra_fechacomprobante));
                if($post->estado==2 || $post->tipodoc_id==0){
                  $nestedData['acciones'] ='<a onclick="detalle_compra('.$post->compra_id.')" class="text-inverse" title="Eliminar" data-toggle="tooltip"><i class="zmdi zmdi-eye zmdi-hc-2x txt-primary"></i></a>';

                }else{
                  $nestedData['acciones'] ='<button onclick="mostrar_detalle_compra('.$post->compra_id.')" type="button" class="btn btn-success btn-xs"><i class="mdi mdi-eye"></i></button> <button onclick="eliminar_precio_equivalencia(19)" type="button" class="btn btn-danger btn-xs"><i class="mdi mdi-delete"></i></button>';
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





 public function ultimo_cambio()

 {

    
/*
    $file = fopen("http://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias","r");



$n=0;

while(!feof($file))  //captura de encabezados

{

  $fila = fgets($file);  //captura de linea

  $sent[$n] = $fila;

  //echo $n." ".$sent[$n]."<br>";

  $n++;

}

fclose($file);

$m = 95;$f=0;



$ultimo_cambio=0;

while($sent[$m] < 32 & $sent[$m+8]>0)

  {

    $ultimo_cambio=$sent[$m+8];

    if($f==3)

    {

      $m=$m+4;

    }

    $m=$m+14;

    $f++;

  }

  

echo $ultimo_cambio;*/
echo "0";



 }


public function  extraer_detalle_compra()
{
   $json=array();
  $detalle=$this->Mantenimiento_m->consulta3("select * from detalle_compras where compra_id=".$_POST["id"]);
  $compra=$this->Mantenimiento_m->consulta3("select * from compras where compra_id=".$_POST["id"]);
//print_r($detalle);

  foreach ($detalle as $key => $value) {
      $json[$key]["precio"]=$value["detalle_compra_precio"];
      $json[$key]["cantidad"]=$value["detalle_compra_cantidad"];
      $json[$key]["descuento"]=$value["detalle_compra_valor_descuento"];
      $json[$key]["tipo_moneda"]=(int)$compra[0]["moneda_id"];
      $json[$key]["cambio_dolar"]=(float)$compra[0]["compra_monto_cambio"];
       $sql="select * from  producto where producto_id=".$value["producto_id"];
      $producto=$this->Mantenimiento_m->consulta2($sql);
      
      $unidad_medida=$this->Mantenimiento_m->consulta2("select * from unidad_medida,detalle_unidad_producto
where detalle_unidad_producto.id_unidad_medida=unidad_medida.id_unidad_medida
and detalle_unidad_producto.detalle_unidad_producto_id=".$value["detalle_compra_detalle_unidad_medida"]);


      $json[$key]["producto"]=$producto->producto_descripcion;
              $json[$key]["unidad_medida"]=$unidad_medida->unidad_medida_descripcion."X".$unidad_medida->detalle_unidad_producto_factor;   
      
      }

      echo json_encode($json);
}

public function anular_compra(){
  $this->db->trans_begin();
  //poner la compra en 0
  $compra=array(
    "compra_importe" => 0,
    "compra_igv" => 0,
    "compra_total" => 0,
    "estado" => 2

  );
  $this->db->where('compra_id',$_REQUEST["id"]);
  $estado=$this->db->update('compras', $compra);

  //preguntar el idmocimiento
  $movimiento=$this->Mantenimiento_m->consulta3("SELECT compras.compra_idmovimiento, compras.compra_serie_comprobante,compras.compra_correlativo_comprobante FROM compras where compras.compra_id=".$_REQUEST["id"]);
  //cambiar comprobante //sacar el id movimiento para
  $comprobante=$this->Mantenimiento_m->consulta3("SELECT plan_contable_comprobante.plan_contable_idcomprobante FROM plan_contable_comprobante where   (plan_contable_comprobante.plan_contable_idregistro= 11 or plan_contable_comprobante.plan_contable_idregistro= 9) and plan_contable_comprobante.plan_tipo_comprobante_id=1 and plan_contable_comprobante.plan_contable_idreferencia = ".$_REQUEST["id"]. " or ( plan_contable_comprobante.plan_contable_idreferencia = ".$movimiento[0]["compra_idmovimiento"]." and plan_contable_comprobante.plan_contable_idregistro = 4)");
  if(count($comprobante)>0){
    foreach ($comprobante as $key => $value) {
      $insumo=array(
        "plan_contable_debe" => 0,
        "plan_contable_haber" => 0
      );
      $this->db->where('plan_contable_idcomprobante',$value["plan_contable_idcomprobante"]);
      $estado=$this->db->update('plan_contable_cuentas', $insumo);
    }
    
  }

  //poner el movimiento en 0
  $compra=array(
    "mov_monto" => 0
  );
  $this->db->where('mov_id',$movimiento[0]["compra_idmovimiento"]);
  $estado=$this->db->update('movimiento', $compra);

  //determinar si es producto o insumo
  $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_compras.insumo_id, detalle_compras.producto_id, detalle_compras.dc_cantidad, detalle_compras.dc_preciounitatio,compras.compra_serie_comprobante,compras.compra_correlativo_comprobante FROM  compras INNER JOIN detalle_compras ON detalle_compras.compra_id = compras.compra_id where compras.compra_id = ".$_REQUEST["id"]);
  $id_almacen=$this->Mantenimiento_m->consulta3("SELECT almacenes.almacen_id FROM sede INNER JOIN almacenes ON almacenes.id_sede = sede.id_sede WHERE sede.sede_estado=1 and almacenes.almacen_estado=1 and sede.id_sede=".$_COOKIE["id_sede"]);
  foreach ($producto_compra as $key => $value) {
    if($value["insumo_id"]==null){
      $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_insumo_almacen.detalle_insumo_almacen_id as producto, detalle_insumo_almacen.detalle_stock, detalle_insumo_almacen.id_insumo FROM insumo INNER JOIN detalle_insumo_almacen ON detalle_insumo_almacen.id_insumo = insumo.insumo_id where detalle_insumo_almacen.id_almacen=".$id_almacen[0]["almacen_id"]." and insumo.insumo_id=".$value["producto_id"]);
      //estock insumo
      $insumo=array(
        "detalle_stock" => $producto_compra[0]["detalle_stock"]-$value["dc_cantidad"]
      );
      $this->db->where('id_insumo',$producto_compra[0]["id_insumo"]);
      $estado=$this->db->update('detalle_insumo_almacen', $insumo);

      $this->movimiento_kardex($producto_compra[0]["producto"],$value["dc_cantidad"],2,"eliminacion de compra",$value["compra_serie_comprobante"],$value["compra_correlativo_comprobante"],$value["dc_preciounitatio"],11);

      
    }else{

      $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_producto_almacen.detalle_producto_almacen_id as producto, producto.producto_id, detalle_producto_almacen.detalle_stock FROM producto INNER JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where detalle_producto_almacen.detalle_almacen=".$id_almacen[0]["almacen_id"]." and producto.producto_id = ".$value["insumo_id"]);
      //estock producto
      $producto=array(
        "detalle_stock" => ($producto_compra[0]["detalle_stock"]-$value["dc_cantidad"])
      );
      $this->db->where('detalle_producto',$producto_compra[0]["producto_id"]);
      $estado=$this->db->update('detalle_producto_almacen', $producto);
      $this->movimiento_kardex_producto($producto_compra[0]["producto"],$value["dc_cantidad"],2,"eliminacion de compra",$value["compra_serie_comprobante"],$value["compra_correlativo_comprobante"],$value["dc_preciounitatio"],11);

    }
    
  }
  echo 1;

  if ($this->db->trans_status() === FALSE){
      $this->db->trans_rollback();      
      return false;    
    }else{      
      $this->db->trans_commit();    
      return true;    
    }
}

public function nota_credito(){
  $this->db->trans_begin();
  //cambiar es estado de la tabla compras a 2 que es nota de credito
  $compra=array(
    "estado" => 2,
    "compra_seri_nota_credito" => $_REQUEST["nota_serie"],
    "compra_correlativo_nota_credito"=> $_REQUEST["nota_correlativo"]
  );
  $this->db->where('compra_id',$_REQUEST["id_nota_credito"]);
  $estado=$this->db->update('compras', $compra);


  //preguntar el idmocimiento
  $movimiento=$this->Mantenimiento_m->consulta3("SELECT compras.compra_idmovimiento, compras.compra_serie_comprobante,compras.compra_correlativo_comprobante FROM compras where compras.compra_id=".$_REQUEST["id_nota_credito"]);
  //cambiar comprobante //sacar el id movimiento para
  $comprobante=$this->Mantenimiento_m->consulta3("SELECT * FROM plan_contable_comprobante where   (plan_contable_comprobante.plan_contable_idregistro= 11 or plan_contable_comprobante.plan_contable_idregistro= 9) and plan_contable_comprobante.plan_tipo_comprobante_id=1 and plan_contable_comprobante.plan_contable_idreferencia = ".$_REQUEST["id_nota_credito"]. " or ( plan_contable_comprobante.plan_contable_idreferencia = ".$movimiento[0]["compra_idmovimiento"]." and plan_contable_comprobante.plan_contable_idregistro = 4)");


//print_r($comprobante); exit();
  if(count($comprobante)>0){
    foreach ($comprobante as $key => $value) {
      $comprobantecuenta = array(
        'plan_contable_idregistro' =>$value["plan_contable_idregistro"],
        'plan_contable_td' => 11,
        'plan_contable_nrodoc' => $value["plan_contable_nrodoc"],
        'plan_contable_serie' =>$value["plan_contable_serie"],
        'plan_contable_correlativo' => $value["plan_contable_correlativo"],
        'plan_contable_fechaemision' => $value["plan_contable_fechaemision"],
        'plan_contable_debe' => $value["plan_contable_haber"],
        'plan_contable_haber' => $value["plan_contable_debe"],
        'plan_contable_glosa' => 'Nota credito Compra por '.$_REQUEST["tipo_pago"],
        'plan_contable_igv_estado' => $value["plan_contable_igv_estado"],
        'plan_contabilidad_id_sede' => $value["plan_contabilidad_id_sede"],
        'plan_contabilidad_estado' => 1,
        'moneda_id' =>  1,
        'plan_contable_detraccion' => 0,
        'plan_contable_retencion' => 0,
        'plan_contable_percepcion' => 0,
        'plan_contable_fechavencimiento' => $value["plan_contable_fechavencimiento"],
        'plan_contable_inciso' => 0,
        'plan_tipo_comprobante_id' => 1,
        'plan_contable_creacioncontabilidad_estado' => 1,
        'plan_contable_idtipocomprobante' => $value["plan_contable_idtipocomprobante"],
        'plan_contable_idreferencia' => $value["plan_contable_idreferencia"],
        'plan_contabilidad_ruc_empresa' => $value["plan_contabilidad_ruc_empresa"],
        'plan_contable_serie_nota' =>$_REQUEST["nota_serie"],
        'plan_contable_correlativa_nota'=>$_REQUEST["nota_correlativo"]
      );
      $this->db->insert('plan_contable_comprobante', $comprobantecuenta);
      $id_comprobante = $this->db->insert_id();

      $cuentas_co=$this->Mantenimiento_m->consulta3("select * from plan_contable_cuentas where plan_contable_cuentas.plan_contable_idcomprobante=".$value["plan_contable_idcomprobante"]);
      if(count($cuentas_co)>0){
        foreach ($cuentas_co as $keye => $valuee) {
        $calculados = array(
              'plan_contable_idcomprobante' => $id_comprobante,
              'plan_contable_idplananalitica' => $valuee["plan_contable_idplananalitica"],
              'plan_contable_auxiliar_id' => $valuee["plan_contable_auxiliar_id"] ,
              'plan_contable_debe' => $valuee["plan_contable_haber"],
              'plan_contable_haber' => $valuee["plan_contable_debe"],
              'plan_contable_monnacional' => $valuee["plan_contable_monnacional"],
              'plan_contable_cuenta_estado' => $valuee["plan_contable_cuenta_estado"],
              'plan_contable_codigo_auxiliar' => $valuee["plan_contable_codigo_auxiliar"],
              'plan_contable_descripcion_auxiliar'=> $valuee["plan_contable_descripcion_auxiliar"],
              'plan_contable_codigo_costo' => $valuee["plan_contable_codigo_costo"],
              'plan_contable_costo_descripcion' => $valuee["plan_contable_costo_descripcion"],
              'plan_contable_idubicacion'=>$valuee["plan_contable_idubicacion"]
            );
            $this->db->insert('plan_contable_cuentas', $calculados);
        
      }
      }
      
      
    }
    
  }


  //generamos un movimiento para la nota de credito
  $id_caj=$this->Mantenimiento_m->consulta3("SELECT sesion_caja.id_sesion_caja FROM sesion_caja INNER JOIN sede_caja ON sesion_caja.id_sede_caja = sede_caja.id_sede_caja where CAST(sesion_caja.ses_fechaapertura AS DATE) ='".date("Y-m-d")."' and sesion_caja.id_empleado=".$_COOKIE["usuario_id"]." and sesion_caja.ses_estado=1");

  $datos_compra=$this->Mantenimiento_m->consulta3("SELECT * from compras WHERE compras.compra_id=".$_REQUEST["id_nota_credito"]);
  $this->generar_movimiento(1,$datos_compra[0]["compra_total"],$_REQUEST["forma_pago"],57,'Nota credito compra',1, 11,$_REQUEST["nota_serie"]."-".$_REQUEST["nota_correlativo"],null,2);

  //determinar si es producto o insumo
  $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_compras.insumo_id, detalle_compras.producto_id, detalle_compras.dc_cantidad, detalle_compras.dc_preciounitatio,compras.compra_serie_comprobante,compras.compra_correlativo_comprobante FROM  compras INNER JOIN detalle_compras ON detalle_compras.compra_id = compras.compra_id where compras.compra_id = ".$_REQUEST["id_nota_credito"]);
  $id_almacen=$this->Mantenimiento_m->consulta3("SELECT almacenes.almacen_id FROM sede INNER JOIN almacenes ON almacenes.id_sede = sede.id_sede WHERE sede.sede_estado=1 and almacenes.almacen_estado=1 and sede.id_sede=".$_COOKIE["id_sede"]);
  foreach ($producto_compra as $key => $value) {
    if($value["insumo_id"]==null){
      $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_insumo_almacen.detalle_insumo_almacen_id as producto, detalle_insumo_almacen.detalle_stock, detalle_insumo_almacen.id_insumo FROM insumo INNER JOIN detalle_insumo_almacen ON detalle_insumo_almacen.id_insumo = insumo.insumo_id where detalle_insumo_almacen.id_almacen=".$id_almacen[0]["almacen_id"]." and insumo.insumo_id=".$value["producto_id"]);
      //estock insumo
      $insumo=array(
        "detalle_stock" => $producto_compra[0]["detalle_stock"]-$value["dc_cantidad"]
      );
      $this->db->where('id_insumo',$producto_compra[0]["id_insumo"]);
      $estado=$this->db->update('detalle_insumo_almacen', $insumo);

      $this->movimiento_kardex($producto_compra[0]["producto"],$value["dc_cantidad"],2,"eliminacion de compra",$value["compra_serie_comprobante"],$value["compra_correlativo_comprobante"],$value["dc_preciounitatio"],11);

      
    }else{

      $producto_compra=$this->Mantenimiento_m->consulta3("SELECT detalle_producto_almacen.detalle_producto_almacen_id as producto, producto.producto_id, detalle_producto_almacen.detalle_stock FROM producto INNER JOIN detalle_producto_almacen ON detalle_producto_almacen.detalle_producto = producto.producto_id where detalle_producto_almacen.detalle_almacen=".$id_almacen[0]["almacen_id"]." and producto.producto_id = ".$value["insumo_id"]);
      //estock producto
      $producto=array(
        "detalle_stock" => ($producto_compra[0]["detalle_stock"]-$value["dc_cantidad"])
      );
      $this->db->where('detalle_producto',$producto_compra[0]["producto_id"]);
      $estado=$this->db->update('detalle_producto_almacen', $producto);
      $this->movimiento_kardex_producto($producto_compra[0]["producto"],$value["dc_cantidad"],2,"eliminacion de compra",$value["compra_serie_comprobante"],$value["compra_correlativo_comprobante"],$value["dc_preciounitatio"],11);

    }
    
  }
  echo 1;
  if ($this->db->trans_status() === FALSE){
      $this->db->trans_rollback();      
      return false;    
    }else{      
      $this->db->trans_commit();    
      return true;    
    }

 
}

public function datos_eliminar_compra(){
  $data=array();
  $data["monto_compra"]=$this->Mantenimiento_m->consulta3("SELECT compras.compra_total FROM compras WHERE compras.compra_id=".$_REQUEST["id"]);
  //$data["tipo_pago"]=$this->Mantenimiento_m->consulta3("SELECT tipo_pago.tipo_pago_id, tipo_pago.tipo_pago_descripcion FROM tipo_pago");
  $data["tipo_pago"]=$this->Mantenimiento_m->consulta3("select * from motivo_nota_credito_descripcion");
  $data["foram_pago"]=$this->Mantenimiento_m->consulta3("SELECT formapago.for_id, formapago.for_descripcion, formapago.for_estado FROM formapago WHERE formapago.for_estado=1");
  echo json_encode($data);
}


public function buscar_producto()
{
$buscar="";
if(isset($_GET["q"])){
  $buscar=$_GET["q"];
}
  $json=array();
  $data=$this->Mantenimiento_m->consulta3("select * from producto where id_sede=".$_COOKIE["id_sede"]." and (producto_descripcion like '%".$buscar."%' or producto_codigobarra like '%".$buscar."%' ) and producto_estado=1 and producto.producto_id_tipoproducto!=3  limit 50");
  foreach ($data as $key => $value) {
    $json["results"][$key]["id"]=$value["producto_id"];
    $json["results"][$key]["text"]=$value["producto_descripcion"];
    //$json["results"][$key]["full_name"]=$value["producto_descripcion"];

  }
  echo json_encode($json);

}

public function mostrar_unidad_medida()
{
  if($_POST["id"]!=""){

          $data=$this->Mantenimiento_m->consulta3("SELECT *
        FROM
        detalle_unidad_producto
        INNER JOIN unidad_medida ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida
        where detalle_unidad_producto_estado=1
        and producto_id=".$_POST["id"]);
        }else
        {

            $data[0]["detalle_unidad_producto_factor"]=1;
            $data[0]["unidad_medida_descripcion"]="Seleccione un producto ";
            $data[0]["detalle_unidad_producto_id"]="";


        }
  echo json_encode($data);exit();
}

public function mostrar_tipo_igv()
{

  $data=$this->Mantenimiento_m->consulta3("select * from tipo_igv where tipo_igv_estado=1");
echo json_encode($data);exit();

}

public function mostrar_igv()
{
  $json=array();
  $data=$this->Mantenimiento_m->consulta3("select * from variable");
  foreach ($data as $key => $value) {
     $json["variable_igv"]=$value["variable_igv"];
     $json["variable_uit"]=$value["variable_uit"];

  }

  echo json_encode($json);

}

public function buscar_proveedor()
  {
    $json=array();
    
      $data=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and (proveedor_razonsocial like '%".$_GET["term"]."%' or proveedor_ruc like '%".$_GET["term"]."%') limit 10 ");
       

    if(count($data)>0){
    foreach ($data as $key => $value) {
      $json[$key]["id"]=$value["proveedor_id"];
      $json[$key]["label"]=$value["proveedor_razonsocial"];
      $json[$key]["value"]=$value["proveedor_razonsocial"];
      $json[$key]["codigo"]=$value["proveedor_ruc"];


    }
    }
   

    echo json_encode($json);
  
}

public function mostrar_forma_pago()
{
  $data=$this->Mantenimiento_m->consulta3("select * from formapago where for_estado=1");
  echo json_encode($data);exit();
}

public function procesar_compra(){
  $post=file_get_contents("php://input");  
  $res=json_decode($post, true);
  $compras_producto=array();
  $compras_pago=array();
  $id_caja=0;
  $monto=0;
  $total = 0;
  parse_str($res["compra"], $compras);
  parse_str($res["pago"], $pago);
// print_r($compras);

// generar pago de la compra
//  exit();
$formapago=$pago["forma_pago"];
$concepto=2;
$descripcion="PAGO DE COMPRA";
$tipomovimiento=2;
$id_tipo_comprobante=$compras["tipo_comprobante"];
$descripcion_comprobante=$compras["serie"].'-'.$compras["correlativo"];
$id_movimiento=0;
$id_movimiento_transporte=0;
$tipo_moneda=$compras["moneda_id"];
$cambio=$compras["cambio"];
$id_cliente = $compras["cliente_id"];
 //guardar compra
if($pago["forma_pago"]==1){
   $id_caja=1;
  }else{
   $id_caja=2;
  }
$igv=0;
if(($compras["igv_total"] > 0)){
  $igv=1;
  }

  foreach ($compras["precio_formulario"] as $key => $value) {
    $total+=($value*$compras["cantidad_formulario"][$key]);
  }
  $total = round($total,1);
  
if($igv==0){
  $total_parcial=$total;
  }
else{
  $cargo_igv=$total*0.18;
  $total_parcial=$total-$cargo_igv;
}

  $monto=$total;



$id_movimiento=1;
$forma_pago=null;
if (isset($pago["forma_pago"])) {
  
$forma_pago=$pago["forma_pago"];
}


$num_cuotas="";
$estado_credito=0;
if($pago["tipo_pago"]==1){
   $id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,$descripcion_comprobante,$id_cliente,null, $igv, null, $tipo_moneda, $cambio);
    if($id_movimiento==0){
      echo 2;exit();
    }else{
      $id_movimiento;
     }  
}

if($pago["tipo_pago"]==2){
$num_cuotas=$pago["cuotas"];
$estado_credito=1;

}

$proveedor=null;
if($compras["cliente_id"]!="")
{



  $proveedor=$compras["cliente_id"];
}




$data_compra=array(
  "proveedor_id"=>$proveedor,
  "almacen_id"=>$compras["almacen_id"],
  "moneda_id"=>$compras["moneda_id"],
  "compra_fechaingresoalmacen"=>date("Y-m-d H:i:s"),
  "tipodoc_id"=>$compras["tipo_comprobante"],
  "compra_fechacomprobante"=>$compras["fecha_compra"],
  "compra_numcomprobante"=>$compras["serie"]."-".$compras["correlativo"],
  "compra_numguiaproveedor"=>"",
  "compra_numcfacttransporte"=>"",
  "compra_numguiatransporte"=>"",
  "compra_importe"=>$compras["subtotal"],
  "compra_igv"=>$compras["igv_total"],
  "compra_total"=>$compras["total"],
  "id_tipo_pago"=>$pago["tipo_pago"],
  "id_empleado"=>$_COOKIE["usuario_id"],
  "compra_credito_estado"=>$estado_credito,
  "compra_credito_cuotas"=>$num_cuotas,
  "compra_monto_transporte"=>"",
  "compra_fecha_transporte"=>"",
  "compra_movimiento_transporte"=>"",
  "compra_serie_comprobante"=>$compras["serie"],
  "compra_correlativo_comprobante"=>$compras["correlativo"],
  "compra_monto_cambio"=>$compras["cambio"],
  "compra_idmovimiento"=>$id_movimiento,
  "id_sede"=>$_COOKIE["id_sede"],
  "compra_forma_pago"=>$forma_pago,





);

$this->Mantenimiento_m->insertar("compras",$data_compra);
// extraeremos el id de compra
$compras_id = $this->db->insert_id();
 //guardar detalle de compra
foreach ($compras["producto_id"] as $key => $value) {
       $data_detalle_compra=array(
        "compra_id"=>$compras_id,
        "producto_id"=>$value,
        "detalle_compra_cantidad"=>$compras["cantidad_formulario"][$key],
        "detalle_compra_preciounitatio"=>$compras["precio_formulario"][$key],
        "detalle_compra_precio"=>((float)$compras["subtotal_formulario"][$key]+(float)$compras["igv_formulario"][$key]),
        "detalle_compra_subtotal"=>$compras["subtotal_formulario"][$key],
        "detalle_compra_igv"=>$compras["igv_formulario"][$key],
        "detalle_compra_valor_descuento"=>0,
        "detalle_compra_detalle_unidad_medida"=>$compras["unidad_medida_formulario"][$key],
        "tipo_igv_id"=>$compras["tipo_igv_formulario"][$key],


      );

       $this->Mantenimiento_m->insertar("detalle_compras",$data_detalle_compra);

     // funcion en el basecontroller
       $this->actualizar_stock($compras["almacen_id"],$value,$compras["unidad_medida_formulario"][$key],$compras["cantidad_formulario"][$key],$compras["precio_formulario"][$key],1,$compras["tipo_comprobante"],$compras["serie"],$compras["correlativo"]);


}



// si el tipo de pago es igual a 2 se generar el cronograma de venta al credito
if($pago["tipo_pago"]==2){
  
     foreach ($pago["nrocuotas"] as $key => $value) {
         $data_cuota_compra=array(
          "cuo_compra"=>$compras_id,
          "cuo_nrocuota"=>$value,
          "cuo_fechavence"=>$pago["fechavence"][$key],
          "cuo_montocuota"=>$pago["montocuota"][$key],
          "tipo_moneda"=>$compras["moneda_id"],
          "cambio_dolar"=>$compras["cambio"],


         );

          $this->Mantenimiento_m->insertar("cuotacompra",$data_cuota_compra);

     }

}

echo 1;

}

}

?>





