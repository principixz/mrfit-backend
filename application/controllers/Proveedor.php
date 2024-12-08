
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
class proveedor extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de Proveedores";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1");
	  $this->vista("Proveedor/index",$data);
	}

	public function nuevo()
	{

	  $data=array();
      $data["titulo_descripcion"]="Nuevo Proveedor";
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Proveedor/nuevo",$data);
	}

	public function guardar()
	{
    $pregunta=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_ruc=".$this->input->post("ruc")."  and proveedor_estado=1");
		$data=array(
			"proveedor_razonsocial"=>$this->input->post("razon_social"),
			"proveedor_direccion"=>$this->input->post("direccion"),
			"proveedor_ruc"=>$this->input->post("ruc"),
			"proveedor_ciudad"=>$this->input->post("ciudad"),
			"proveedor_telefono"=>$this->input->post("telefono"),
			"proveedor_nombrecomercial"=>$this->input->post("nombre_comercial"),
			"proveedor_email"=>$this->input->post("correo"),
			"proveedor_website"=>$this->input->post("pagina"),
			"proveedor_contacto"=>$this->input->post("contacto"),
		
			"proveedor_estado_habido"=>$this->input->post("habido")


		);
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('proveedores', $data);
		}
		else
		{
                   $this->db->where('proveedor_id',$this->input->post("id"));
	            	$estado=$this->db->update('proveedores', $data);
		}
     header('Location: '.base_url()."proveedor");
	}

public function guardar_ajax()
	{

		$data=array(
			"proveedor_razonsocial"=>$this->input->post("razon_social"),
			"proveedor_direccion"=>$this->input->post("direccion"),
			"proveedor_ruc"=>$this->input->post("ruc"),
			"proveedor_ciudad"=>$this->input->post("ciudad"),
			"proveedor_telefono"=>$this->input->post("telefono"),
			"proveedor_nombrecomercial"=>$this->input->post("nombre_comercial"),
			"proveedor_email"=>$this->input->post("correo"),
			"proveedor_website"=>$this->input->post("pagina"),
			"proveedor_contacto"=>$this->input->post("contacto"),
		


		);
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('proveedores', $data);
		}
		else
		{
                   $this->db->where('proveedor_id',$this->input->post("id"));
	            	$estado=$this->db->update('proveedores', $data);
		}
		$datos=array("id"=>1);
 echo json_encode($datos);
	}
	public function editar($id)
	{
		  $data=array();
      $data["titulo_descripcion"]="Editar Proveedor";
      $data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Proveedor/nuevo",$data);

	}

  public function traer_uno()
  {
  	$dat=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_id=".$this->input->post("id"));
		echo json_encode($dat);
  }
   
 public function ver_ruc()
 {

  
  $cliente = new \Sunat\Sunat();
  $data=   $cliente->search($this->input->post("ruc") );
  echo json_encode($data);


 }
 
	public function eliminar($id)
	{
      $this->Mantenimiento_m->consulta1("update proveedores set proveedor_estado=0 where proveedor_id=".$id);
	} 
	

}
?>