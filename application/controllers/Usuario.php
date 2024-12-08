<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";

class Usuario extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	 // echo  $this->input->post("usuario")." ". $this->input->post("clave");
  
     $data=array();
      $data["titulo_descripcion"]="Lista de usuarios";
      $data["tabla"]=$this->Mantenimiento_m->consulta3("select * from perfiles,empleados where empleados.perfil_id=perfiles.perfil_id  and empresa_sede=".$_COOKIE["id_sede"]."  and empleados.estado=1");

      $data["contrato"]=$this->Mantenimiento_m->consulta3("select * from motivo_contrato");
	  $this->vista("Usuario/index",$data);
	}
  public function nuevo(){
   $data["titulo_descripcion"]="nuevo usuario";
   $data["perfil"]=$this->Mantenimiento_m->consulta3("SELECT * from perfiles where perfil_id!=8 and perfil_id!=1 and perfil_id!=14 and estado=1 ");
    $data["fondo_pension"]=$this->Mantenimiento_m->consulta3("SELECT * from fondo_pension where fondo_pension_estado = 1");
    $data["bancos"] = $this->Mantenimiento_m->consulta3("SELECT * FROM bancos where estado = 1");
    $data["fondo_salud"] = $this->Mantenimiento_m->consulta3("SELECT * FROM fondo_salud where fondosalud_estado = 1");

    $data["sede"]=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
    $this->vista("Usuario/nuevo",$data);

  }
  public function editar($id){
   $data["titulo_descripcion"]="Actualizar Usuario";
   $data["id"]=$id;
   $data["perfil"]=$this->Mantenimiento_m->consulta3("SELECT * from perfiles where perfil_id!=8 and perfil_id!=1 and estado=1");
   $data["bancos"] = $this->Mantenimiento_m->consulta3("SELECT * FROM bancos where estado = 1");
   $data["fondo_pension"]=$this->Mantenimiento_m->consulta3("SELECT * from fondo_pension where fondo_pension_estado = 1");
   $data["fondo_salud"] = $this->Mantenimiento_m->consulta3("SELECT * FROM fondo_salud where fondosalud_estado = 1");
     $data["sede"]=$this->Mantenimiento_m->consulta3("select * from sede where id_sede=".$_COOKIE["id_sede"]);
    $this->vista("Usuario/nuevo",$data);
  }

  public function guardar(){ 

   if($this->input->post("id")==""){
         $dato_usuario=array(
              "empleado_nombres"=>$this->input->post("nombre"),
              "empleado_apellidos"=>$this->input->post("apellido"),
              "empleado_dni"=>$this->input->post("dni"),
              "empleado_direccion"=>$this->input->post("direccion"),
              "empleado_email"=>$this->input->post("correo"),
              "empleado_telefono"=>$this->input->post("telefono"),
              "perfil_id"=>$this->input->post("perfil"),
              "empleado_usuario"=>$this->input->post("usuario"),
              "empleado_clave"=>$this->input->post("clave"), 
              "empleado_nombre_completo"=>$this->input->post("nombre")." ".$this->input->post("apellido"),
              "empleado_fecha_nacimiento"=>$this->input->post("fecha"),
              "empresa_sede"=>$_COOKIE["id_sede"],  
              "empleado_sueldoreal"=>$this->input->post("salario_real"),
              "empleado_foto_perfil" => 'defaut.jpg',
              "empleado_sueldoplanilla"=>$this->input->post("salario_planilla"),  
              "empleado_fecha_inicio"=>date("Y-m-d")
            ); 
             $dat=$this->Mantenimiento_m->consulta3("select * from empleados where estado=1 and empleado_usuario='".$_POST["usuario"]."'");
         if(count($dat)==0){
          $estado=$this->db->insert('empleados', $dato_usuario);                 
         }else{
          header('Location: '.base_url()."usuario/nuevo");
          exit();
         }     
    }
    else {



         $dato_usuario=array(
                "empleado_nombres"=>$this->input->post("nombre"),
              "empleado_apellidos"=>$this->input->post("apellido"),
              "empleado_dni"=>$this->input->post("dni"),
              "empleado_direccion"=>$this->input->post("direccion"),
              "empleado_email"=>$this->input->post("correo"),
              "empleado_telefono"=>$this->input->post("telefono"),
              "perfil_id"=>$this->input->post("perfil"),
              "empleado_usuario"=>$this->input->post("usuario"),
              "empleado_clave"=>$this->input->post("clave"),
              "empleado_nombre_completo"=>$this->input->post("nombre")." ".$this->input->post("apellido"),
              "empleado_fecha_nacimiento"=>$this->input->post("fecha"),
              "empresa_sede"=>$_COOKIE["id_sede"],

          

            "empleado_sueldoreal"=>$this->input->post("salario_real"),
            "empleado_sueldoplanilla"=>$this->input->post("salario_planilla")
            );



                   $this->db->where('empleado_id',$this->input->post("id"));
                $estado=$this->db->update('empleados', $dato_usuario);
    }
     header('Location: '.base_url()."usuario");
  }

  public function traer_uno()
  {
      $dat["usuario"]=$this->Mantenimiento_m->consulta3("select * from empleados where empleado_id=".$this->input->post("id"));

    echo json_encode($dat);

  }
  public function eliminar($id)
  {
      $this->Mantenimiento_m->consulta1("update empleados set estado=0 where empleado_id=".$id);
  } 

     public function buscar_usuario() {
      if($_POST["usuario"]!=""){
         $dat=$this->Mantenimiento_m->consulta3("select * from empleados where empleado_usuario='".$_POST["usuario"]."'");
         if(count($dat)==0)
         {
            echo 1;
         }else
         {
          echo 0;
         }

          }
          else{
                   echo 0;
          }
   }

  

}