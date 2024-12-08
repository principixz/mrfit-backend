<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "BaseController.php"; 

class Registro_comprobante extends BaseController {

	public function __construct() {
		parent::__construct();            
	}

	public function index(){
		$data=array();
		$data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_documento  where tipodoc_estado = 1 and tipo_documento.tipodoc_id IN(1,2,5)");
		$data["contador"] = count($data["tabla"]);
		$data["correlativos"] = $this->Mantenimiento_m->consulta3("SELECT * FROM documento
			INNER JOIN tipo_documento ON tipo_documento.tipodoc_id = documento.id_tipodocumento
			INNER JOIN empresa ON empresa.empresa_ruc = documento.id_empresa");
		$data["chek"] = $this->Mantenimiento_m->consulta3("SELECT detalle_doc_sede.detalle_id_documento, 'checked' as estado
			FROM documento ,tipo_documento ,empresa ,detalle_doc_sede
			WHERE tipo_documento.tipodoc_id = documento.id_tipodocumento 
			AND empresa.empresa_ruc = documento.id_empresa 
			AND detalle_doc_sede.detalle_id_documento = documento.id_documento AND detalle_doc_sede.detalle_id_sede =".$_COOKIE["id_sede"]);
		$data["titulo_descripcion"]="Configuración";
		$this->vista("Configuracion/index",$data);
	}

	public function traerconfig(){
		$data["configuracion"] = $this->Mantenimiento_m->consulta3("SELECT * FROM mantenimiento_parametros");	
		echo json_encode($data);
	}

	public function traerdetalleparametro($value=''){
		$data = $this->Mantenimiento_m->consulta3("SELECT 
			plan_contable_digito.plan_contable_digito_codigo
			FROM
			detalle_parametro_registro
			INNER JOIN plan_contable_tipoparametro ON detalle_parametro_registro.plan_contable_idtipoparametro = plan_contable_tipoparametro.plan_contable_idtipoparametro
			INNER JOIN plan_contable_digito ON detalle_parametro_registro.plan_contable_digito_codigo = plan_contable_digito.plan_contable_digito_codigo
			INNER JOIN plan_contable_registro ON detalle_parametro_registro.registro_idregistro = plan_contable_registro.registro_idregistro
			WHERE detalle_parametro_registro.registro_idregistro = ".$_POST["idcodigo"]." and detalle_parametro_registro.plan_contable_idtipoparametro = ".$_POST["idparametro"]."
			");
		$enviar["id"][0] = "";
		foreach ($data as $key => $valores) {
			$enviar["id"][$key] = $valores["plan_contable_digito_codigo"];
		}
		echo json_encode($enviar);
	}





	public function actualizarparametro(){ 
		$data = array(
		"mantenimiento_cuentaigv" => $_POST["cigv"],
		"mantenimiento_cuarta" => $_POST["retencioncu"],
		"mantenimiento_debe" => $_POST["debe"],
		"mantenimiento_haber" => $_POST["haber"],
		"mantenimiento_cuentapercerpcion" => $_POST["percepcion"],
		"mantenimiento_cuentarecepcion" => $_POST["cretencion"],
		"mantenimeinto_detraccion" => $_POST["cdetraccion"],
		"mantenimiento_igvmesanterior" => $_POST["igvanterior"],
		"mantenimiento_retencionigvante" => $_POST["retencionante"],
		"mantenimiento_percep_mesante" => $_POST["percepcionante"]
		);
		if ($_POST["idparametro"] == ''){
			$this->db->insert('mantenimiento_parametros', $data);
			$id = $this->db->insert_id();	
		}else{
			$this->db->where('mantenimiento_idparametro',$_POST["idparametro"]);
			$estado=$this->db->update('mantenimiento_parametros', $data);
			$id = $_POST["idparametro"];
		}
		echo json_encode($id);
	}



	public function guardarseco(){
		$ruc_empresa = $this->Mantenimiento_m->consulta2("SELECT empresa_ruc FROM empresa LIMIT 1"); 
		for ($i=0; $i <count($_POST["correlatives"]) ; $i++) {
			$band = 1;
			if ($_POST["correlatives"][$i] !='' && $_POST["series"][$i] != '') {
				$data=array(
					"doc_serie"=>$_POST["series"][$i],
					"doc_correlativo"=>$_POST["correlatives"][$i],
					"id_empresa"=>$ruc_empresa->empresa_ruc,
					"id_tipodocumento"=>$_POST["tipodedocumento"][$i]
				);

					$band = 1;

				if ($band == 1) {
					$estado=$this->db->insert('documento', $data);
				}else{
					$this->db->where('id_documento',$iddocumento);
					$estado=$this->db->update('documento', $data);
				}

			}	
		}
		echo 1;
	}

	public function traercomprobantes(){
		$data=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_documento  where tipodoc_estado = 1 and tipodoc_id !=0");
		$envio["contador"] = count($data);
		$envio["seleccionar"] = '<option value=""> Seleccionar...</option>';
		foreach ($data as $key => $value) {
			$envio["seleccionar"] = $envio["seleccionar"].'<option value="'.$value["tipodoc_id"].'">'.$value["tipodoc_descripcion"].'</option>';
		}
		echo json_encode($envio);

	}
	
	public function configsede(){
		$data=array();
		$data["tabla"]=$this->Mantenimiento_m->consulta3("SELECT * FROM tipo_documento  where tipodoc_estado = 1");
		$data["estado"] = 1;
		$data["correlativos"] = $this->Mantenimiento_m->consulta3("SELECT * FROM documento
			INNER JOIN tipo_documento ON tipo_documento.tipodoc_id = documento.id_tipodocumento
			INNER JOIN empresa ON empresa.empresa_ruc = documento.id_empresa
			where documento.id_empresa =".$_COOKIE["ruc_empresa"]);
		$data["chek"] = $this->Mantenimiento_m->consulta3("SELECT detalle_doc_sede.detalle_id_documento, 'checked' as estado
			FROM documento ,tipo_documento ,empresa ,detalle_doc_sede
			WHERE tipo_documento.tipodoc_id = documento.id_tipodocumento 
			AND empresa.empresa_ruc = documento.id_empresa 
			AND detalle_doc_sede.detalle_id_documento = documento.id_documento AND detalle_doc_sede.detalle_id_sede =".$_COOKIE["id_sede"]);
 
         $data["parametro_configuracion"]=$this->Mantenimiento_m->consulta3("SELECT *
FROM
configuracion_parametro
INNER JOIN detalle_tipo_negocio_configuracion_parametro ON detalle_tipo_negocio_configuracion_parametro.configuracion_parametro_id = configuracion_parametro.configuracion_parametro_id
INNER JOIN tipo_negocio ON detalle_tipo_negocio_configuracion_parametro.id_tipo_negocio = tipo_negocio.id_tipo_negocio
INNER JOIN sede ON sede.id_tipo_negocio = tipo_negocio.id_tipo_negocio
where sede.id_sede=
".$_COOKIE["id_sede"]);

         $data["parametro_actualizados"]=$this->Mantenimiento_m->consulta3("select * from detalle_sede_configuracion_parametro where id_sede=".$_COOKIE["id_sede"]);
         $data["banco"]=$this->Mantenimiento_m->consulta3("SELECT bancos.banco_idbanco,bancos.descripcion FROM bancos where bancos.estado=1");

		$data["titulo_descripcion"]="Configuración";
		$this->load->view("Configuracion/index",compact('data'));
	}






    public function datos_empresa()
    {
    $dat=$this->Mantenimiento_m->consulta3("select * from empresa ");
    echo json_encode($dat);

    }



    public function editar_empresa()
    {
       $data=array(
          "empresa_razon_social"=>$_POST["razon_social"],
          "empresa_direccion"=>$_POST["empresa_direccion"],
          "empresa_telefono"=>$_POST["empresa_telefono"],
          "empresa_correo"=>$_POST["empresa_correo"],
          "empresa_abreviatura"=>$_POST["empresa_abreviatura"],
          "empresa_nombre_comercial"=>$_POST["empresa_nombre_comercial"],
         "empresa_token_facturacion"=>$_POST["empresa_token_facturacion"],
         "empresa_icono"=>$_POST["empresa_icono"],
         "empresa_fondo"=>$_POST["empresa_fondo"],
         "empresa_color"=>$_POST["empresa_color"]


          
        



       );
     				 $this->db->where('empresa_ruc',$_COOKIE["ruc_empresa"]);
					$estado=$this->db->update('empresa', $data);
					echo 1;
    }


    public function subir_firma()
    {
    	    $dir_subida = "public/firma_sunat/";
    	  $ver=explode('.',$_FILES['firma']['name']);
    	    $name =basename($ver[0]);
          $extension=$ver[1];
             $newname = implode('.', [$name.time(), $extension]);
             if (copy($_FILES['firma']['tmp_name'], $dir_subida.$newname)) 
             {
              
              }
         else {
                echo "Error al subir la foto es demasiado grande";                 
           }

            $data=array(
          "empreasa_firma_digital"=> $newname 
            );
             $this->db->where('empresa_ruc',$_COOKIE["ruc_empresa"]);
					$estado=$this->db->update('empresa', $data);
					echo 1;
    }




   public function eliminar_comprobante(){   

    $sql="SELECT * FROM detalle_doc_sede INNER JOIN sede ON detalle_doc_sede.detalle_id_sede = sede.id_sede where detalle_doc_sede.detalle_id_documento=".$_POST["id"]."";
    $data=$this->Mantenimiento_m->consulta3($sql);
    $json=array();
    if(count($data)==0){ 
        $sql="delete from documento where  id_documento=".$_POST["id"];
        //echo  $sql;
       	$this->Mantenimiento_m->consulta1($sql);
       	$json["estado"]=1;
       	$json["mensaje"]="SE ELIMINO CORRECTAMENTE ";
       }else{
     	$json["estado"]=0;
       	$json["mensaje"]="ERROR EL CORRELATIVO ESTA RELACIONADO CON LA TIENDA ".$data[0]["sede_descripcion"];
       }
    	echo json_encode($json);
    	exit();
    }


   public function vincular_desvincular(){   

    $sql="SELECT * FROM detalle_doc_sede INNER JOIN sede ON detalle_doc_sede.detalle_id_sede = sede.id_sede where detalle_doc_sede.detalle_id_documento=".$_POST["id"]."";
    $data=$this->Mantenimiento_m->consulta3($sql); 
    $json=array();
    if(count($data)!=0){ 
        $sql="DELETE FROM detalle_doc_sede where  detalle_id_documento=".$_POST["id"]; 
       	$this->Mantenimiento_m->consulta1($sql);
       	$json["estado"]=1;
       	$json["mensaje"]="SE DESVINCULO LA RELACIÓN ";
       }else{
       	$sql="INSERT INTO detalle_doc_sede(detalle_id_sede,detalle_id_documento) VALUES (".$_COOKIE["id_sede"].",".$_POST["id"].") "; 
       	$this->Mantenimiento_m->consulta1($sql);
     	$json["estado"]=0;
       	$json["mensaje"]="SE RELACIONO CORRECTAMENTE";
       }
    	echo json_encode($json);
    	exit();
    }
    

}
?>


