
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
class Marca_producto extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de marca de producto";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from marca where marca_estado=1");
	  $this->vista("Marca_producto/index",$data);
	}

	public function nuevo()
	{

	  $data=array();
      $data["titulo_descripcion"]="Nueva Marca de producto";
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Marca_producto/nuevo",$data);
	}

	public function guardar()
	{

		$data=array(
			"marca_descripcion"=>$this->input->post("descripcion"),
			
			


		);
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('marca', $data);


     


		}
		else
		{
                   $this->db->where('marca_id',$this->input->post("id"));
	            	$estado=$this->db->update('marca', $data);
		}
     //header('Location: '.base_url()."almacen");
	}

	public function editar($id)
	{
		  $data=array();
      $data["titulo_descripcion"]="Editar Marca";
      $data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Marca_producto/nuevo",$data);

	}

  public function traer_uno()
  {
  	$dat=$this->Mantenimiento_m->consulta3("select * from marca where marca_id=".$this->input->post("id"));
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
      $this->Mantenimiento_m->consulta1("update marca set marca_estado=0 where marca_id=".$id);
	} 


	public function verificar_almacen()
	{
        
        $json=array();

       $sede=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
       if((int)$sede[0]["id_tipo_negocio"]==1)
       {
           $data=$this->Mantenimiento_m->consulta3("select * from almacenes where almacen_estado=1 and id_sede=".$_COOKIE["id_sede"]);
           if(count($data)>=2)
           {
            $json["estado"]=0;
           }else
           {
                 $json["estado"]=1;
           }
       }
       else
       {
             $json["estado"]=0;
       }

    echo json_encode($json);exit();

	}
	

}
?>