
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
//require_once("src/autoload.php");
class C_producto extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Categorias de Platos";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from categoria_producto where categoria_producto_estado=1 and id_sede=".$_COOKIE["id_sede"]);

	  $this->vista("Categoria_producto/index",$data);
	}

	public function nuevo()
	{

	  $data=array();
      $data["titulo_descripcion"]="Nuevo Categoria de Platos";
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Categoria_producto/nuevo",$data);
	}

	public function guardar(){    
		if($_FILES['imagen']['name']!=""){
			$dir_subida = "public/categoriap/";
			$dir_compre= "imagen/comprimido/";
			$url=md5(rand().time()).basename($_FILES['imagen']['name']);
			$fichero_subido = $dir_subida.$url;
			$resubir = $dir_compre.basename($_FILES['imagen']['name']);
			$ancho_nuevo=34; 
			$alto_nuevo=34; 
			if (copy($_FILES['imagen']['tmp_name'], $fichero_subido)){

			}
			else {
				echo "Error al subir la foto es demasiado grande";                 
			} 
			$this->redim ($fichero_subido,$fichero_subido,$ancho_nuevo,$alto_nuevo); 
			$nombre_imagen=$url;
		}else{
			if($this->input->post("id")==""){
				$nombre_imagen="img_504591.png";
			}
			else{
				$dat = $this->Mantenimiento_m->consulta3("select * from producto where producto_id=".$this->input->post("id"));
				$nombre_imagen=$dat[0]["producto_imagen"];
			}
		}

		$data=array(
			"categoria_producto_descripcion"=>$this->input->post("descripcion"),
			"categoria_imagen" => $nombre_imagen,
			"id_sede"=>$_COOKIE["id_sede"]
		);
		if($this->input->post("id")==""){
         $estado=$this->db->insert('categoria_producto', $data);
		}
		else{
                   $this->db->where('categoria_producto_id',$this->input->post("id"));
	            	$estado=$this->db->update('categoria_producto', $data);
		}

     header('Location: '.base_url()."C_producto");
	}

	public function editar($id)
	{
		  $data=array();
      $data["titulo_descripcion"]="Editar Categoria de Producto";
      $data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Categoria_producto/nuevo",$data);

	}

  public function traer_uno()
  {
  	$dat=$this->Mantenimiento_m->consulta3("select * from categoria_producto where categoria_producto_id=".$this->input->post("id"));
		echo json_encode($dat);
  }
   
 
 
	public function eliminar($id)
	{
      $this->Mantenimiento_m->consulta1("update categoria_producto set categoria_producto_estado=0 where categoria_producto_id=".$id);
	} 
	

}
?>