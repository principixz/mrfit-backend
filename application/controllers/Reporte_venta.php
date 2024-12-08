
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php";
require_once("src/autoload.php");
require_once("convertirAletra.php");

class Reporte_venta extends BaseController {

 public function __construct() {
        parent::__construct();
       	
      
    }

	public function index()
	{
	  $data["titulo_descripcion"]="Reporte de Ventas";
     $this->vista("Reporte/venta",$data);
   }

   public function venta_dia(){
      $datos=array();
   	   $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
			HOUR(venta_fecha_pago) AS Hora from venta where venta_idsede=".$_COOKIE["id_sede"]." and DATE(venta_fecha_pago) = '".$_POST["fecha_inicio"]."' and venta_estado=2  and venta_fecha_pago is not null  GROUP BY HOUR(venta_fecha_pago)
			ORDER BY HOUR(venta_fecha_pago)");
   	   foreach ($dat as $key => $value) {
   	   	   $datos["extension"][$key]=$value["Hora"]." Horas";
   	   	   $datos["monto"][$key]=(float)$value["monto"];

   	   }
        $datos["cronologia"]="Horas";
   	   echo json_encode($datos);
   }


   public function venta_semana(){
      $datos=array();
   	   $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
			DATE_FORMAT(venta_fecha_pago,'%a') AS nombre_dia,DAY(venta_fecha_pago) from venta where venta_idsede=".$_COOKIE["id_sede"]." and DATE(venta_fecha_pago) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."' and venta_estado=2  and venta_fecha_pago is not null GROUP BY DATE(venta_fecha_pago)
			ORDER BY DATE(venta_fecha_pago)");
   	   foreach ($dat as $key => $value) {
   	   	   $datos["extension"][$key]=$this->nombre_dias_castellano($value["nombre_dia"]);
   	   	   $datos["monto"][$key]=(float)$value["monto"];

   	   }
        $datos["cronologia"]="Dias";
   	   echo json_encode($datos);
   }

     public function venta_mes(){
      $datos=array();
   	   $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
			DATE(venta_fecha_pago) AS nombre_dia,DAY(venta_fecha_pago) from venta where  venta_idsede=".$_COOKIE["id_sede"]." and DATE(venta_fecha_pago) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."' and venta_estado=2 and venta_fecha_pago is not null GROUP BY DATE(venta_fecha_pago)
			ORDER BY DATE(venta_fecha_pago)");
   	   foreach ($dat as $key => $value) {

   	   	   $timestamp = strtotime($value["nombre_dia"]); 
			$new_date = date('d/m/Y', $timestamp);
   	   	   $datos["extension"][$key]=$new_date;
   	   	   $datos["monto"][$key]=(float)$value["monto"];

   	   }
        $datos["cronologia"]="Fecha";
   	   echo json_encode($datos);
   }

   public function venta_anio(){
   	 $datos=array();
   	   $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
			MONTH(venta_fecha_pago) AS nombre_dia,DAY(venta_fecha_pago) from venta where venta_idsede=".$_COOKIE["id_sede"]." and DATE(venta_fecha_pago) BETWEEN '".$_POST["fecha_inicio"]."' and '".$_POST["fecha_fin"]."' and venta_estado=2 and venta_fecha_pago is not null GROUP BY MONTH(venta_fecha_pago)
			ORDER BY MONTH(venta_fecha_pago)");
   	   foreach ($dat as $key => $value) {

   	   	
   	   	   $datos["extension"][$key]=$this->nombre_meses_castellano($value["nombre_dia"]);
   	   	   $datos["monto"][$key]=(float)$value["monto"];

   	   }
        $datos["cronologia"]="Meses";
   	   echo json_encode($datos);
   }





   public function venta_anual(){
     $datos=array();
       $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
      YEAR(venta_fecha_pago) AS nombre_dia,DAY(venta_fecha_pago) from venta where venta_idsede=".$_COOKIE["id_sede"]." and venta_estado=2 and venta_fecha_pago is not null GROUP BY YEAR(venta_fecha_pago)
      ORDER BY YEAR(venta_fecha_pago)");
       foreach ($dat as $key => $value) {

        
           $datos["extension"][$key]=($value["nombre_dia"]);
           $datos["monto"][$key]=(float)$value["monto"];

       }
        $datos["cronologia"]="";
       echo json_encode($datos);
   }


   public function venta_diagrama(){
     $datos=array();
       $dat=$this->Mantenimiento_m->consulta3("select Sum(venta_monto) AS monto,
      YEAR(venta_fecha_pago) AS anio,MONTH(venta_fecha_pago) AS mes from venta where venta_idsede=".$_COOKIE["id_sede"]." and venta_estado=2 and venta_fecha_pago is not null
       GROUP BY YEAR(venta_fecha_pago),MONTH(venta_fecha_pago)
      ORDER BY YEAR(venta_fecha_pago)");
      $datos[0][0]='ID';
       $datos[0][1]='Monto de Venta';
       $datos[0][2]='Fecha';
       $datos[0][3]='Descripcion';
       $datos[0][4]='Monto';


       foreach ($dat as $key => $value) {
        $dat=$value["anio"]."-".$value["mes"]."-1";
                    $timestamp = strtotime($dat); 
        
              $datos[$key+1][0]=$value["anio"].",".$value["mes"];
               $datos[$key+1][1]=(float)$value["monto"];
       $datos[$key+1][2]=(float)$timestamp ;
      
       $datos[$key+1][3]=$value["anio"].",".$this->nombre_meses_castellano($value["mes"])."(".$value["monto"].")";
       $datos[$key+1][4]=(float)$value["monto"];



      

       }
       echo json_encode($datos);
   }

 

 
 

 





   public function nombre_dias_castellano($dat)
   {
        
        switch ($dat) {
        	case 'Sun':
        		return "Domingo";
        		break;
        	
        	case 'Mon':
        	return "Lunes";
        		# code...
        		break;
        	case 'Tue':
        		# code...
        	return "Martes";
        		break;
        	case 'Wed':
        		# code...
        	return "Miercoles";
        		break;
        	case 'Thu':
        		# code...
        	return "Jueves";
        		break;
        	case 'Fri':
        		# code...
        	return "Viernes";
        		break;
        	case 'Sat':
        		# code...
        	return "Sabado";
        		break;

        	default:
        		# code...
        		break;
        }
   }
    public function nombre_meses_castellano($dat)
   {
        
        switch ($dat) {
        	case '1':
        		return "Enero";
        		break;
        	
        	case '2':
        	return "Febrero";
        		# code...
        		break;
        	case '3':
        		# code...
        	return "Marzo";
        		break;
        	case '4':
        		# code...
        	return "Abril";
        		break;
        	case '5':
        		# code...
        	return "Mayo";
        		break;
        	case '6':
        		# code...
        	return "Junio";
        		break;
        	case '7':
        		# code...
        	return "Julio";
        		break;
        	case '8':
        		# code...
        	return "Agosto";
        		break;
        	case '9':
        		# code...
        	return "Setiembre";
        		break;

        	case '10':
        		# code...
        	return "Octubre";
        		break;

        	case '11':
        		# code...
        	return "Noviembre";
        		break;

        	case '12':
        		# code...
        	return "Diciembre";
        		break;



        	default:
        		# code...
        		break;
        }
   }

}