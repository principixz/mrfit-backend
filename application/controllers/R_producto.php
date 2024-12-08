
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class R_producto extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Producto";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
producto
INNER JOIN marca ON producto.marca_id = marca.marca_id
INNER JOIN clase ON producto.clase_id = clase.clase_id
 where producto_estado=1 and producto.producto_id_tipoproducto!=3 and id_sede=".$_COOKIE["id_sede"]);
	  $this->vista("R_producto/index",$data);
	}

	public function nuevo()
	{

	  $data=array();
      $data["titulo_descripcion"]="Nuevo Producto";
      $data["moneda"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado=1");
       $data["almacen"]=$this->Mantenimiento_m->consulta3("select  * from almacenes where id_sede=".$_COOKIE["id_sede"]." and almacen_estado=1");

       //$data["categoria"]=$this->Mantenimiento_m->consulta3("select * from categoria_producto where categoria_producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
     //  $data["insumo"]=$this->Mantenimiento_m->consulta3("select * from insumo where insumo_estado=1 and id_sede=".$_COOKIE["id_sede"]);
	  $this->vista("R_producto/nuevo",$data);
	}
  public function editar($id)
  {

    $data=array();
      $data["titulo_descripcion"]="Nuevo Producto";
      $data["moneda"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado=1");
       $data["almacen"]=$this->Mantenimiento_m->consulta3("select  * from almacenes where id_sede=".$_COOKIE["id_sede"]." and almacen_estado=1");
         $data["id"]=$id;
       //$data["categoria"]=$this->Mantenimiento_m->consulta3("select * from categoria_producto where categoria_producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
     //  $data["insumo"]=$this->Mantenimiento_m->consulta3("select * from insumo where insumo_estado=1 and id_sede=".$_COOKIE["id_sede"]);
    $this->vista("R_producto/nuevo",$data);
  }
  public function actualizar_medida()
  {

    $data=$this->Mantenimiento_m->consulta3("select * from unidad_medida where unidad_medida_estado=1");
    echo json_encode($data);
  }
  public function actualizar_clase()
  {
    $data=$this->Mantenimiento_m->consulta3("select * from clase where clase_estado=1");
    echo json_encode($data);
  }

public function actualizar_marca()
 {
   $data=$this->Mantenimiento_m->consulta3("select * from marca where marca_estado=1");
    echo json_encode($data);
 }
public function actualizar_tipo_producto()
{
$data=$this->Mantenimiento_m->consulta3("select * from tipo_producto where tipoproducto_estado=1 and tipoproducto_id!=3");
    echo json_encode($data);

}
public function guardar_unidad()
{

  $data=array(
    "unidad_medida_descripcion"=>strtoupper($_POST["nombre_unidad"]),
    "descripcion_sunat"=>strtoupper($_POST["sunat_unidad"])
  );

  $this->Mantenimiento_m->insertar("unidad_medida",$data);
  echo 1;
}
public function guardar_clase()
{

  $data=array(
    "clase_descripcion"=>strtoupper($_POST["nombre_clase"])
   
  );

  $this->Mantenimiento_m->insertar("clase",$data);
  echo 1;
}

public function guardar_marca()
{

  $data=array(
    "marca_descripcion"=>strtoupper($_POST["nombre_marca"])
   
  );

  $this->Mantenimiento_m->insertar("marca",$data);
  echo 1;
}


public function guardar_producto()
{

     $nombre_imagen="";
       if($_FILES['imagen']['name']!="")
       {    $dir_subida = "public/img/productos/";
            $nombre=basename($_FILES['imagen']['name']) ;
            $datos_url=explode(".", $nombre);
            $can=sizeof($datos_url);
            $res=$can-1;
            $url=md5(rand().time()).".".$datos_url[$res];
            $fichero_subido = $dir_subida.$url;
            $ancho_nuevo=400; 
            $alto_nuevo=400; 
             if (copy($_FILES['imagen']['tmp_name'], $fichero_subido)){
              }else {
                echo "Error al subir la foto es demasiado grande";                 
              }     
              $this->redim ($fichero_subido,$fichero_subido,$ancho_nuevo,$alto_nuevo); 
              $nombre_imagen=$url;
       }else
       {

            if($_POST["producto_id"]!=""){
               $imagen=$this->Mantenimiento_m->consulta3("select * from producto where  producto_id=".$_POST["producto_id"]);
               $nombre_imagen=$imagen[0]["producto_imagen"];
            }



       }
      $proveedor=null;
     if($_POST["proveedor"]!="")
     {
       $proveedor=$_POST["proveedor"];

     }

$aplicar=null;
       if(isset($_POST["tipo_aplicar"]))
         {
          $aplicar=$_POST["tipo_aplicar"];
         }
          
   
     $id_producto="";
    // echo "data".$_POST["producto_id"];exit();
      if($_POST["producto_id"]=="")
      {

           $data=array(
        "producto_codigobarra"=>$_POST["codigo_barra"],
        "producto_codigo_referencia"=>$_POST["codigo_referencia"],
        "producto_descripcion"=>$_POST["descripcion"],
        "producto_precio"=>$_POST["moneda"],
        "moneda_id"=>$_POST["tipo_moneda"],
        "clase_id"=>$_POST["clase"],
        "marca_id"=>$_POST["marca"],
        "producto_modelo"=>$_POST["modelo"],
        "proveedor_id"=>$proveedor, 
        "producto_minimo"=>$_POST["stock_minimo"],
        "producto_ubicacion"=>$_POST["ubicacion"],
        "producto_id_tipoproducto"=>$_POST["tipo_producto"],
        "id_sede"=>$_COOKIE["id_sede"],
        "unidad_medida_id"=>$_POST["unidad"],
        "producto_kilogramo"=>$_POST["peso"],
        "producto_imagen"=>$nombre_imagen,
            "producto_estado_precio_fijo"=>$_POST["precio_fijo"],
           "producto_tipo_precio"=>$aplicar,
           "categoria_producto_id"=>$_POST["categoria"]
      );

        $this->Mantenimiento_m->insertar("producto",$data);
        $id_producto= $this->db->insert_id();

       
       
          $datos=array(
             "producto_id"=>$id_producto,
             "id_unidad_medida"=>$_POST["unidad"],
             "detalle_unidad_producto_factor"=>1,
             "detalle_unidad_producto_fijo"=>1
          );
         $this->Mantenimiento_m->insertar("detalle_unidad_producto", $datos);
         $id_detalle_unidad_producto=$this->db->insert_id();
       
        $datos_precio_unitario=array(
           "precio_unitario_producto_descripcion"=>"precio 1",
           "precio_unitario_producto_valor"=>$_POST["moneda"],
           "detalle_unidad_producto_id"=>$id_producto,
           "precio_unidad_producto_precio_fijo"=>$_POST["precio_fijo"],
           "precio_unidad_producto_aplicar_fijo"=>$aplicar,
           "precio_unitario_producto_fijo"=>1,
           "precio_unitario_producto_descuento"=>0,
           "detalle_unidad_producto_id"=>$id_detalle_unidad_producto
        );
        $this->Mantenimiento_m->insertar("precio_unitario_producto",$datos_precio_unitario);
       // $detalle_precio_id= $this->db->insert_id();

       $almacen=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
       foreach ($almacen as $key => $value) {
           $data_almacen=array(
                "detalle_almacen"=>$value["almacen_id"],
                "detalle_producto"=>$id_producto
            );
        $this->Mantenimiento_m->insertar("detalle_producto_almacen",$data_almacen);

       }


      }else{

        $data=array(
        "producto_codigobarra"=>$_POST["codigo_barra"],
        "producto_codigo_referencia"=>$_POST["codigo_referencia"],
        "producto_descripcion"=>$_POST["descripcion"],
        "producto_precio"=>$_POST["moneda"],
        "moneda_id"=>$_POST["tipo_moneda"],
        "clase_id"=>$_POST["clase"],
        "marca_id"=>$_POST["marca"],
        "producto_modelo"=>$_POST["modelo"],
        "proveedor_id"=>$proveedor, 
        "producto_minimo"=>$_POST["stock_minimo"],
        "producto_ubicacion"=>$_POST["ubicacion"],
        "producto_id_tipoproducto"=>$_POST["tipo_producto"],
        "id_sede"=>$_COOKIE["id_sede"],
        "producto_imagen"=>$nombre_imagen,
            "producto_estado_precio_fijo"=>$_POST["precio_fijo"],
           "producto_tipo_precio"=>$aplicar,
             "categoria_producto_id"=>$_POST["categoria"]
      );

        $this->Mantenimiento_m->actualizar("producto",$data,$_POST["producto_id"],"producto_id");
        $id_producto=$_POST["producto_id"];

        $detalle=$this->Mantenimiento_m->consulta3("select * from detalle_unidad_producto where producto_id=".$id_producto." and detalle_unidad_producto_fijo=1");
        $id_detalle_unidad_producto=$detalle[0]["detalle_unidad_producto_id"];

        $precio_unitario=$this->Mantenimiento_m->consulta3("select * from precio_unitario_producto where detalle_unidad_producto_id=".$id_detalle_unidad_producto." and precio_unitario_producto_fijo=1");

         $precio_unitario_producto_id=$precio_unitario[0]["precio_unitario_producto_id"];
         
            $datos_precio_unitario=array(
               "precio_unitario_producto_valor"=>$_POST["moneda"],
               "precio_unidad_producto_precio_fijo"=>$_POST["precio_fijo"],
               "precio_unidad_producto_aplicar_fijo"=>$aplicar,
            );
         $this->Mantenimiento_m->actualizar("precio_unitario_producto",$datos_precio_unitario,$precio_unitario_producto_id,"precio_unitario_producto_id");
      }



$json["estado"]=1;
$json["producto_id"]=$id_producto;
    echo json_encode($json);



}


public function mostrar_precio()
{
  $data=$this->Mantenimiento_m->consulta3("SELECT *
FROM
detalle_unidad_producto
INNER JOIN unidad_medida ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida
INNER JOIN precio_unitario_producto ON precio_unitario_producto.detalle_unidad_producto_id = detalle_unidad_producto.detalle_unidad_producto_id
where detalle_unidad_producto.producto_id=".$_POST["producto_id"]." and detalle_unidad_producto_estado=1
and precio_unidad_producto_estado=1");
  echo json_encode($data);exit();

}

public function traer_informacion()
{
  $data=$this->Mantenimiento_m->consulta3("select * from producto where producto_id=".$_POST["producto_id"]);
  echo json_encode($data);exit();
}

public function mostrar_equivalencia()
{

   $data=$this->Mantenimiento_m->consulta3("SELECT *
FROM
detalle_unidad_producto
INNER JOIN unidad_medida ON detalle_unidad_producto.id_unidad_medida = unidad_medida.id_unidad_medida
where detalle_unidad_producto_estado=1 and producto_id=".$_POST["producto_id"]);

   echo json_encode($data);exit();

}

public function guardar_equivalencia()
{
$valorar=$this->Mantenimiento_m->consulta3("select * from detalle_unidad_producto where producto_id=".$_POST["producto_id"]." and id_unidad_medida=".$_POST["unidad_medida"]." and detalle_unidad_producto_fijo=1");

  if(sizeof($valorar)==0){
  $data=array(
   "producto_id"=>$_POST["producto_id"],
   "id_unidad_medida"=>$_POST["unidad_medida"],   
   "detalle_unidad_producto_factor"=>$_POST["factor_unidad"],   

  );
        $this->Mantenimiento_m->insertar("detalle_unidad_producto",$data);
        echo 1;
      }else
      {



        echo 0;
      }

}

public function eliminar_detalle_unidad()
{
   $datos_precio_unitario=array(
               "detalle_unidad_producto_estado"=>0
            );
         $this->Mantenimiento_m->actualizar("detalle_unidad_producto",$datos_precio_unitario,$_POST["id"],"detalle_unidad_producto_id");
}
public function guardar_precio_detalle()
{

  $data=array(
   "detalle_unidad_producto_id"=>$_POST["equivalencia"],
   "precio_unitario_producto_descuento"=>$_POST["descuento_precio"],
   "precio_unitario_producto_utilidad"=>$_POST["utilidad_precio"],
   "precio_unitario_producto_descripcion"=>$_POST["precio_descripcion"],
   "precio_unitario_producto_valor"=>$_POST["valor_precio"]

  );
    

     if($_POST["precio_equivalencia_id"]=="")
     {
        
        $this->Mantenimiento_m->insertar("precio_unitario_producto",$data); 
     }else
     {
     $this->Mantenimiento_m->actualizar("precio_unitario_producto",$data,$_POST["precio_equivalencia_id"],"precio_unitario_producto_id");

     }

}

public function eliminar_detalle_precio()
{

 $datos_precio_unitario=array(
               "precio_unidad_producto_estado"=>0
            );
         $this->Mantenimiento_m->actualizar("precio_unitario_producto",$datos_precio_unitario,$_POST["id"],"precio_unitario_producto_id");


}


public function mostrar_stock()
{

$json=array();
$data=$this->Mantenimiento_m->consulta3("SELECT *
FROM
detalle_producto_almacen
where detalle_producto=".$_POST["producto_id"]);

$json=$data;
echo json_encode($json);exit();


}

}
?>