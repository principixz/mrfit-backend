
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
class Registrar_clase extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de clase de producto";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from clase where clase_estado=1");
	  $this->vista("Registrar_clase/index",$data);
	}

	public function nuevo()
	{

	  $data=array();
      $data["titulo_descripcion"]="Nueva Clase de producto";
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Registrar_clase/nuevo",$data);
	}

	public function guardar()
	{

		$data=array(
			"clase_descripcion"=>$this->input->post("descripcion"),
			
			


		);
		if($this->input->post("id")=="")
		{
         $estado=$this->db->insert('clase', $data);


     


		}
		else
		{
                   $this->db->where('clase_id',$this->input->post("id"));
	            	$estado=$this->db->update('clase', $data);
		}
     //header('Location: '.base_url()."almacen");
	}

	public function editar($id)
	{
		  $data=array();
      $data["titulo_descripcion"]="Editar Clase Producto";
      $data["id"]=$id;
     // $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from proveedores where proveedor_estado=1 and empresa_ruc=".$_SESSION["ruc_empresa"]);
	  $this->vista("Registrar_clase/nuevo",$data);

	}

  public function traer_uno()
  {
  	$dat=$this->Mantenimiento_m->consulta3("select * from clase where clase_id=".$this->input->post("id"));
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
      $this->Mantenimiento_m->consulta1("update clase set clase_estado=0 where clase_id=".$id);
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