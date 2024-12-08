<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Registrar_plato extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Platos";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
producto
INNER JOIN categoria_producto ON categoria_producto.categoria_producto_id = producto.categoria_producto_id

 where producto_estado=1 and producto.producto_id_tipoproducto=3 and producto.id_sede=".$_COOKIE["id_sede"]);
	  $this->vista("Registrar_plato/index",$data);
	}

  public function nuevo()
  { 
  $data=array();
      $data["titulo_descripcion"]="Nuevo Plato";
            $data["moneda"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado=1");
  $this->vista("Registrar_plato/nuevo",$data);

  }


public function actualizar_categoria(){

  $json=$this->Mantenimiento_m->consulta3("select * from categoria_producto where categoria_producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);
  echo json_encode($json);exit();
}


public function guardar_plato()
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
            }else{

            $nombre_imagen="default.jpg";

            }



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
        "categoria_producto_id"=>$_POST["categoria"],
        "producto_stock"=>0,
       
       
        "producto_minimo"=>$_POST["stock_minimo"],
      
        "producto_id_tipoproducto"=>3,
        "id_sede"=>$_COOKIE["id_sede"],
        "unidad_medida_id"=>$_POST["unidad"],
       
        "producto_imagen"=>$nombre_imagen,
           
      );
             
        $this->Mantenimiento_m->insertar("producto",$data);
        $id_producto= $this->db->insert_id();


      }

      else{

        $data=array(
        "producto_codigobarra"=>$_POST["codigo_barra"],
        "producto_codigo_referencia"=>$_POST["codigo_referencia"],
        "producto_descripcion"=>$_POST["descripcion"],
        "producto_precio"=>$_POST["moneda"],
        "moneda_id"=>$_POST["tipo_moneda"],
        "categoria_producto_id"=>$_POST["categoria"], 
       
       
        "producto_minimo"=>$_POST["stock_minimo"],
      
        "producto_id_tipoproducto"=>3,
        "id_sede"=>$_COOKIE["id_sede"],
        "unidad_medida_id"=>$_POST["unidad"],
       
        "producto_imagen"=>$nombre_imagen,
           
      );

        $this->Mantenimiento_m->actualizar("producto",$data,$_POST["producto_id"],"producto_id");
        $id_producto=$_POST["producto_id"];

      }
   echo 1;


}


  public function guardar(){    
    if($_FILES['imagen_categoria']['name']!=""){
      $dir_subida = "public/img/productos/";
      //$dir_compre= "imagen/comprimido/";
      $url=md5(rand().time()).basename($_FILES['imagen_categoria']['name']);
      $fichero_subido = $dir_subida.$url;
     // $resubir = $dir_compre.basename($_FILES['imagen_categoria']['name']);
      $ancho_nuevo=34; 
      $alto_nuevo=34; 
      if (copy($_FILES['imagen_categoria']['tmp_name'], $fichero_subido)){

      }
      else {
        echo "Error al subir la foto es demasiado grande";                 
      } 
      $this->redim ($fichero_subido,$fichero_subido,$ancho_nuevo,$alto_nuevo); 
      $nombre_imagen=$url;
    }else{
      if($this->input->post("id")==""){
        $nombre_imagen="default.jpg";
      }
      else{
        $dat = $this->Mantenimiento_m->consulta3("select * from producto where producto_id=".$this->input->post("id"));
        $nombre_imagen=$dat[0]["producto_imagen"];
      }
    }

    $data=array(
      "categoria_producto_descripcion"=>$this->input->post("nombre_categoria"),
      "categoria_imagen" => $nombre_imagen,
      "id_sede"=>$_COOKIE["id_sede"]
    );
    if($this->input->post("categoria_id")==""){
         $estado=$this->db->insert('categoria_producto', $data);
    }
    else{
                   $this->db->where('categoria_producto_id',$this->input->post("categoria_id"));
                $estado=$this->db->update('categoria_producto', $data);
    }

    echo 1;

    // header('Location: '.base_url()."categoria_producto");
  }

  public function eliminar()
  {
     $data=array(
      'producto_estado' =>0 ,

      );
       $this->db->where('producto_id',$this->input->post("id"));
                $estado=$this->db->update('producto', $data);
    echo 1;
  }

  public function editar($id)
  {
     $data=array();
      $data["titulo_descripcion"]="Lista de Platos";
         $data["moneda"]=$this->Mantenimiento_m->consulta3("select * from monedas where moneda_estado=1");
      $data["id"]=$id;
  $this->vista("Registrar_plato/nuevo",$data);

  }


}


?>