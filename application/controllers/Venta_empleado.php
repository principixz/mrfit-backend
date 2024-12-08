<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
class Venta_empleado extends BaseController {

 public function __construct() {
        parent::__construct();
        
      
    }

  public function index(){

     $data["titulo_descripcion"]="Reporte de Ventas por empleado";
     //$data["categoria"]=$this->Mantenimiento_m->consulta3("select * from categoria_insumo where categoria_insumo_estado=1 and id_sede=".$_COOKIE["id_sede"]);
         $this->vista("Reporte/Venta_empleado",$data);

     
   }

   public function buscar_empleado()
   {
         $inicio=$_POST["inicio"]; 
         $fin=$_POST["fin"]; 
         $data=array();

         $c=0;


           $sql=$this->Mantenimiento_m->consulta3("select sum(venta_monto) as suma,count(venta_idventas) as cantidad from venta where venta_codigomozo=".$_POST["id_empleado"]." and date(venta_pedidofecha)='".$inicio."' and venta_estado=2 " );
          $data["fecha"][$c]=$inicio;
          $data["cantidad"][$c]=(float)$sql[0]["cantidad"];
          $data["suma"][$c]=(float)$sql[0]["suma"];




         while($inicio!=$fin){
             

             $dia2=date_create($inicio);
            date_add($dia2, date_interval_create_from_date_string('1 day'));
            $inicio=date_format($dia2, 'Y-m-d');

               $c++;
              $sql=$this->Mantenimiento_m->consulta3("select sum(venta_monto) as suma,count(venta_idventas) as cantidad from venta where venta_codigomozo=".$_POST["id_empleado"]." and date(venta_pedidofecha)='".$inicio."' and venta_estado=2 " );
          $data["fecha"][$c]=$inicio;
          $data["cantidad"][$c]=(float)$sql[0]["cantidad"];
          $data["suma"][$c]=(float)$sql[0]["suma"];
              
         }
      
      echo  json_encode($data);

   }
  public function traer_personal()
  {
    $data1="";
    if(isset($_GET["search"])){
           $data1=$_GET["search"];
    }
    $data=$this->Mantenimiento_m->consulta3("select * from empleados where empleado_nombre_completo like '%".$data1."%' and estado=1 and empresa_sede=".$_COOKIE["id_sede"]." limit 60");
    $datos=array();
    foreach ($data as $key => $value) {
                $datos["results"][$key]["id"]=$value["empleado_id"];  
                $datos["results"][$key]["text"]=$value["empleado_nombre_completo"];        

    }
    echo json_encode($datos);
  }
}

 ?>