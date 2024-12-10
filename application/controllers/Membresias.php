<?php

require APPPATH . 'libraries/ImplementJwt.php';
require APPPATH . 'libraries/CustomException.php';

require_once "BaseController.php";

header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Content-Type: application/json; charset=UTF-8');

class Membresias extends BaseController {

    public function __construct(){
        parent::__construct();
        $this->load->model('Servicio_m');
        $this->objOfJwt = new ImplementJwt();
    }

    public function consultar_token(){
        $header  =  $this->input->request_headers('authorization');
        $token="";
        if(isset($header['Authorization'])){
            $token= $header['Authorization'];
        }else{
            $token= $header['authorization'];
        }
        try {
            $jwtData = $this->objOfJwt->DecodeToken($token);
            return json_encode($jwtData);
        } catch (Exception $e) {
            http_response_code('401');
            echo json_encode(array("status" => false,"message" => $e->getMessage()));exit();
        }
    }
    
    public function buscar_cliente(){  
        $id=$this->input->get("tipo_pago");
        $total = $this->input->get("totalpago");
        $tipo_pago=$this->input->get("tipo_pago");
        $sql_insertar="";
        if($tipo_pago==2){
          $sql_insertar=" and cliente_id<>1";
        }
         if ($id == "1" || $id == "4" ) {
           $sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where LENGTH(cliente_dni) =11 and (estado=1   and (cliente_nombres LIKE '%".$this->input->get("search")."%' or cliente_dni LIKE '%".$this->input->get("search")."%' or cliente_celular LIKE '%".$this->input->get("search")."%'  ) ".$sql_insertar.") limit 10";
        }else{
          if($id != "6"){
            if ($id == "2" and $total > 700) {
               $sql="SELECT  cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni from clientes where (estado=1 and clientes.cliente_id!=1 and (LENGTH(cliente_dni) = 8  ) and  (cliente_nombres LIKE '%".$this->input->get("search")."%' or cliente_dni LIKE '%".$this->input->get("search")."%' or cliente_celular LIKE '%".$this->input->get("search")."%' ) ".$sql_insertar.") limit 10";
            }else{
               $sql="SELECT  cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni from clientes where ((estado=1  and (LENGTH(cliente_dni) = 8 ) and (cliente_nombres LIKE '%".$this->input->get("search")."%' or cliente_dni LIKE '%".$this->input->get("search")."%' or cliente_celular LIKE '%".$this->input->get("search")."%' ) ".$sql_insertar.") ) limit 10";
            }
          }else{
            $sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where (estado=1 and (cliente_nombres LIKE '%".$this->input->get("search")."%' or cliente_dni LIKE '%".$this->input->get("search")."%' or cliente_celular LIKE '%".$this->input->get("search")."%' ) ".$sql_insertar.") limit 10";
          }
        }
        
    
            //$sql="SELECT cliente_id,cliente_nombres, (IF(cliente_dni is null,'No cuenta con D.N.I',cliente_dni)) as cliente_dni  from clientes where (estado=1 and (cliente_nombres LIKE '%".$this->input->get("q")."%' or cliente_dni LIKE '%".$this->input->get("q")."%' or cliente_celular LIKE '%".$this->input->get("q")."%' ) ".$sql_insertar.") limit 10";
        $data=$this->Mantenimiento_m->consulta3($sql);
        $data1=array();
        foreach ($data as $key => $value) {
          $sql="select * from direccion where cliente_id=".$value["cliente_id"]." and direccion_estado=1 order by direccion_id desc";
          $direcciones=$this->db->query($sql)->row_array();
        //  $direccion=$this->db->query($sql);
          $data1[$key]["id"]=$value["cliente_id"]."/".$value["cliente_dni"]."/".$value["cliente_nombres"]."/".$direcciones["direccion_id"]."/".$direcciones["direccion_descripcion"];
          $data1[$key]["text"]=$value["cliente_nombres"];
          $data1[$key]["documento"]=$value["cliente_dni"];
        }
        echo json_encode($data1);
    
    }
 
    public function get_membresia(){
        try {
            $data_token = json_decode($this->consultar_token(),true);
            if (!$data_token) {
                throw new CustomException('Error al obtener el token.', 401); // 401 - No autorizado
            }
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata,true); 
            $tipoproducto_id = $request["id"] ?? null;
            
            $data['tipomembresia'] = $this->Servicio_m->get_servicios($tipoproducto_id);
            if ($data['tipomembresia'] === false) {
                throw new CustomException('Error al obtener los servicios de membresía.', 500); // 500 - Error interno del servidor
            }
            echo json_encode($data);
        } catch (CustomException $e) {
            log_message('error', 'Error en get_membresia: ' . $e->getMessage());
            $this->output
            ->set_status_header($e->getHttpCode())
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => $e->getMessage()]));
        } catch (Exception $e) {
            // Manejo general de excepciones
            log_message('error', 'Error en get_membresia: ' . $e->getMessage());
            $this->output
            ->set_status_header(500)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'Ocurrió un problema interno del servidor.']));
        }
    }

    public function get_membresia_buscar(){
        try {
            $data_token = json_decode($this->consultar_token(),true);
            if (!$data_token) {
                throw new CustomException('Error al obtener el token.', 401); // 401 - No autorizado
            }
            $descripcion = $this->input->get('descripcion');
            $this->load->model('Servicio_m');
            $result = $this->Servicio_m->get_membresia_buscar($descripcion);
    
            if ($result !== false && !empty($result)) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontraron membresías con la descripción proporcionada.']);
            }
        } catch (Exception $e) {
            log_message('error', 'Error en get_membresia_buscar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al buscar membresías.']);
        }
    }

    public function post_registrartipomembresia(){  
        try {
            // Obtener y decodificar el token de autenticación
            $data_token = json_decode($this->consultar_token(), true);
            if (!$data_token) {
                throw new CustomException('Error al obtener el token.', 401); // 401 - No autorizado
            }
    
            // Definir los campos que se esperan del frontend y mapearlos al backend
            $field_mapping = [
                'descripcion' => 'tipo_membresia_descripcion',
                'precio_mensual' => 'tipo_membresia_precio_mes',
                'meses' => 'tipo_membresia_duracion',
                'tipo_periodo' => 'tipo_membresia_tipoperiodo',
                'fecha_inicio' => 'tipo_membresia_fechainicio',
                'fecha_fin' => 'tipo_membresia_fechafin',
                'categoria_membresia' => 'tipo_membresia_categoriamembresia',
                'tipo_durabilidad' => 'tipo_membresia_tipopago',
                'cantidad_personas' => 'tipo_membresia_cantidadpersona',
                'estado' => 'tipo_membresia_estado' // Asegúrate de que este campo esté presente en el frontend
            ];
    
            // Definir campos opcionales con sus valores predeterminados
            $optional_fields_with_defaults = [
                'tipo_membresia_fechainicio' => null, // Fecha no existente
                'tipo_membresia_fechafin' => null,
                'tipo_membresia_cantidadpersona' => 0
            ];
    
            // Obtener los datos del POST
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata, true); 
    
            // Mapear los campos del frontend al backend
            $mapped_request = [];
            foreach ($field_mapping as $frontend_field => $backend_field) {
                if (isset($request[$frontend_field])) {
                    $mapped_request[$backend_field] = $request[$frontend_field];
                }
            }
    
            // Convertir fechas al formato 'Y-m-d' si están presentes y no están vacías
            if (isset($mapped_request['tipo_membresia_fechainicio']) && !empty($mapped_request['tipo_membresia_fechainicio'])) {
                $mapped_request['tipo_membresia_fechainicio'] = date('Y-m-d', strtotime($mapped_request['tipo_membresia_fechainicio']));
            }
            if (isset($mapped_request['tipo_membresia_fechafin']) && !empty($mapped_request['tipo_membresia_fechafin'])) {
                $mapped_request['tipo_membresia_fechafin'] = date('Y-m-d', strtotime($mapped_request['tipo_membresia_fechafin']));
            }
    
            // Definir los campos requeridos básicos
            $required_fields = [
                'tipo_membresia_descripcion',
                'tipo_membresia_precio_mes',
                'tipo_membresia_duracion',
                'tipo_membresia_tipoperiodo',
                'tipo_membresia_categoriamembresia',
                'tipo_membresia_tipopago',
                'tipo_membresia_estado'
            ];
    
            // Agregar campos condicionalmente requeridos
            if (isset($mapped_request['tipo_membresia_tipoperiodo']) && $mapped_request['tipo_membresia_tipoperiodo'] === '02') { // Temporal
                $required_fields[] = 'tipo_membresia_fechainicio';
                $required_fields[] = 'tipo_membresia_fechafin';
            }
    
            if (isset($mapped_request['tipo_membresia_categoriamembresia']) && $mapped_request['tipo_membresia_categoriamembresia'] === '02') { // Grupal
                $required_fields[] = 'tipo_membresia_cantidadpersona';
            }
    
            // Validar campos requeridos
            foreach ($required_fields as $field) {
                if (!isset($mapped_request[$field]) || $mapped_request[$field] === null || $mapped_request[$field] === '') {
                    throw new CustomException("El campo {$field} es requerido.", 400);
                }
            }
    
            // Asignar valores predeterminados para campos opcionales si no están presentes
            foreach ($optional_fields_with_defaults as $field => $default) {
                if (!isset($mapped_request[$field]) || $mapped_request[$field] === null || $mapped_request[$field] === '') {
                    // Asignar valor predeterminado solo si el campo no es requerido
                    $mapped_request[$field] = $default;
                }
            }
    
            // Validaciones adicionales
            if (!$this->validar_precio($mapped_request['tipo_membresia_precio_mes'])) {
                throw new CustomException("El precio ingresado debe ser un precio válido en formato decimal (12,2).", 400);
            }
    
            if (!$this->validar_servicio_duracion($mapped_request['tipo_membresia_duracion'])) {
                throw new CustomException("Debes validar que 'duración' sea un dígito correcto.", 400);
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_tipoperiodo'], ['01', '02'])) {
                throw new CustomException("El tipo de Periodo es incorrecto 01: Ilimitado , 02: Temporal", 400);
            } else {
                if ($mapped_request['tipo_membresia_tipoperiodo'] === '02') { // Temporal
                    if (!$this->validar_fechas($mapped_request['tipo_membresia_fechainicio'], $mapped_request['tipo_membresia_fechafin'])) {
                        throw new CustomException("Las fechas deben estar en formato año-mes-día y la fecha fin no debe ser menor que la fecha inicio.", 400);
                    }
                }
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_categoriamembresia'], ['01', '02'])) {
                throw new CustomException("El tipo de Categoria Membresia es incorrecto 01: Individual , 02: Grupal", 400);
            } else {
                if ($mapped_request['tipo_membresia_categoriamembresia'] === '02') { // Grupal
                    if (!is_numeric($mapped_request['tipo_membresia_cantidadpersona']) || $mapped_request['tipo_membresia_cantidadpersona'] < 2) {
                        throw new CustomException("El campo 'cantidad_personas' debe ser un número válido y al menos 2 para membresías grupales.", 400);
                    }
                }
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_tipopago'], ['01', '02', '03', '04'])) {
                throw new CustomException("El Tipo de Durabilidad es incorrecto.", 400);
            }
    
            // Insertar la nueva membresía en la base de datos
            $result = $this->Servicio_m->insertar_servicio($mapped_request);
            if ($result) {
                $this->output
                    ->set_status_header(201) // 201 - Creado
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['message' => 'Servicio insertado correctamente.']));
            } else {
                throw new CustomException("No se pudo insertar el servicio. Error en el servidor.", 500);
            }
    
        } catch (CustomException $e) {
            log_message('error', 'Error en insertar_membresia: ' . $e->getMessage());
            $this->output
                ->set_status_header($e->getHttpCode())
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => $e->getMessage()]));
        } catch (Exception $e) {
            // Manejo general de excepciones
            log_message('error', 'Error en insertar_membresia: ' . $e->getMessage());
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Ocurrió un problema interno del servidor.']));
        }
    }

    public function put_registrartipomembresia(){  
        try {
            $data_token = json_decode($this->consultar_token(), true);
            if (!$data_token) {
                throw new CustomException('Error al obtener el token.', 401); // 401 - No autorizado
            }
    
            // Campos mapeados desde el frontend al backend
            $field_mapping = [
                'id' => 'tipo_membresia_id',
                'descripcion' => 'tipo_membresia_descripcion',
                'precio_mensual' => 'tipo_membresia_precio_mes',
                'meses' => 'tipo_membresia_duracion',
                'tipo_periodo' => 'tipo_membresia_tipoperiodo',
                'fecha_inicio' => 'tipo_membresia_fechainicio',
                'fecha_fin' => 'tipo_membresia_fechafin',
                'categoria_membresia' => 'tipo_membresia_categoriamembresia',
                'tipo_durabilidad' => 'tipo_membresia_tipopago',
                'cantidad_personas' => 'tipo_membresia_cantidadpersona',
                'estadoTrotadora' => 'estadoTrotadora' ,
                'estado' => 'tipo_membresia_estado'
            ];
    
            // Campos opcionales con sus valores predeterminados
            $optional_fields_with_defaults = [
                'tipo_membresia_fechainicio' => null, // Fecha no existente
                'tipo_membresia_fechafin' => null,
                'tipo_membresia_cantidadpersona' => 0
            ];
    
            // Obtener los datos del POST
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata, true); 
    
            // Mapear los campos del frontend a los del backend
            $mapped_request = [];
            foreach ($field_mapping as $frontend_field => $backend_field) {
                if (isset($request[$frontend_field])) {
                    $mapped_request[$backend_field] = $request[$frontend_field];
                }
            }

            // Convertir fechas al formato 'Y-m-d' si están presentes
            if (isset($mapped_request['tipo_membresia_fechainicio']) && !empty($mapped_request['tipo_membresia_fechainicio'])) {
                $mapped_request['tipo_membresia_fechainicio'] = date('Y-m-d', strtotime($mapped_request['tipo_membresia_fechainicio']));
            }
            if (isset($mapped_request['tipo_membresia_fechafin']) && !empty($mapped_request['tipo_membresia_fechafin'])) {
                $mapped_request['tipo_membresia_fechafin'] = date('Y-m-d', strtotime($mapped_request['tipo_membresia_fechafin']));
            }
    
            // Determinar campos requeridos basados en las selecciones
            $required_fields = [
                'tipo_membresia_descripcion',
                'tipo_membresia_precio_mes',
                'tipo_membresia_duracion',
                'tipo_membresia_tipoperiodo',
                'tipo_membresia_categoriamembresia',
                'tipo_membresia_tipopago',
                'tipo_membresia_estado', // Asegúrate de que este campo esté mapeado si es necesario
                'estadoTrotadora'
            ];
    
            // Agregar campos condicionalmente requeridos
            if (isset($mapped_request['tipo_membresia_tipoperiodo']) && $mapped_request['tipo_membresia_tipoperiodo'] === '02') {
                $required_fields[] = 'tipo_membresia_fechainicio';
                $required_fields[] = 'tipo_membresia_fechafin';
            }
    
            if (isset($mapped_request['tipo_membresia_categoriamembresia']) && $mapped_request['tipo_membresia_categoriamembresia'] === '02') {
                $required_fields[] = 'tipo_membresia_cantidadpersona';
            }
            
            // Validar campos requeridos
            foreach ($required_fields as $field) {
                if (!isset($mapped_request[$field]) || $mapped_request[$field] === null || $mapped_request[$field] === '') {
                    print_r($mapped_request[$field] . '<br>');
                    throw new CustomException("El campo {$field} es requerido.", 400);
                }
            }
            // Asignar valores predeterminados para campos opcionales si no están presentes
            foreach ($optional_fields_with_defaults as $field => $default) {
                if (!isset($mapped_request[$field]) || empty($mapped_request[$field])) {
                    // Asignar valor predeterminado solo si el campo no es requerido
                    $mapped_request[$field] = $default;
                }
            }
    
            // Validaciones adicionales
            if (!$this->validar_precio($mapped_request['tipo_membresia_precio_mes'])) {
                throw new CustomException("El precio ingresado debe ser un precio válido en formato decimal (12,2).", 400);
            }
    
            if (!$this->validar_servicio_duracion($mapped_request['tipo_membresia_duracion'])) {
                throw new CustomException("Debes validar que 'duración' sea un dígito correcto.", 400);
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_tipoperiodo'], ['01', '02'])) {
                throw new CustomException("El tipo de Periodo es incorrecto 01: Ilimitado , 02: Temporal", 400);
            } else {
                if ($mapped_request['tipo_membresia_tipoperiodo'] === '02') { // Temporal
                    if (!$this->validar_fechas($mapped_request['tipo_membresia_fechainicio'], $mapped_request['tipo_membresia_fechafin'])) {
                        throw new CustomException("Las fechas deben estar en formato año-mes-día y la fecha fin no debe ser menor que la fecha inicio.", 400);
                    }
                }
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_categoriamembresia'], ['01', '02'])) {
                throw new CustomException("El tipo de Categoria Membresia es incorrecto 01: Individual , 02: Grupal", 400);
            } else {
                if ($mapped_request['tipo_membresia_categoriamembresia'] === '02') { // Grupal
                    if (!is_numeric($mapped_request['tipo_membresia_cantidadpersona']) || $mapped_request['tipo_membresia_cantidadpersona'] < 2) {
                        throw new CustomException("El campo 'cantidad_personas' debe ser un número válido y al menos 2 para membresías grupales.", 400);
                    }
                }
            }
    
            if (!$this->validar_tipo($mapped_request['tipo_membresia_tipopago'], ['01', '02', '03', '04'])) {
                throw new CustomException("El Tipo de Durabilidad es incorrecto.", 400);
            }
    
            // Insertar o actualizar el servicio con los datos completos
            $result = $this->Servicio_m->editar_servicio($mapped_request);
            if ($result) {
                $this->output
                    ->set_status_header(200) // Cambiado a 200 para actualizaciones exitosas
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['message' => 'Servicio actualizado correctamente.']));
            } else {
                throw new CustomException("No se pudo actualizar el servicio. Error en el servidor.", 500);
            }
    
        } catch (CustomException $e) {
            log_message('error', 'Error en editar_membresia: ' . $e->getMessage());
            $this->output
                ->set_status_header($e->getHttpCode())
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => $e->getMessage()]));
        } catch (Exception $e) {
            // Manejo general de excepciones
            log_message('error', 'Error en editar_membresia: ' . $e->getMessage());
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Ocurrió un problema interno del servidor.']));
        }
    }

    public function post_registrarClienteMembresia() {
        try {
            $data_token = json_decode($this->consultar_token(), true);
            if (!$data_token) {
                throw new CustomException('Error al obtener el token.', 401); // 401 - No autorizado
            }
            $monto=0;
            $descripcion_comprobante="";
            $serie="";
            $correlativo="";
            $id_documento="";
            $nuevo_correlativo=0;
            $igv=0;
            $estado_igv="";
            $monto_igv=0;
            // Obtener los datos del POST
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata, true);
            
            // Validar que se hayan recibido los principales grupos de datos
            if (!isset($request['ventadatos']) || !isset($request['tipo_membresia_id']) || !isset($request['detalleventaServicios'])) {
                throw new CustomException("Los campos 'ventadatos', 'tipo_membresia_id' y 'detalleventaServicios' son requeridos.", 400);
            }

            // Validar que 'cliente_id' sea un arreglo y no esté vacío
            if (!is_array($cliente_ids) || empty($cliente_ids)) {
                throw new CustomException("El campo 'cliente_id' dentro de 'detalleventaServicios' debe ser un arreglo de IDs de clientes.", 400);
            }
    
            // Validar que 'ventadatos' contenga los campos requeridos
            $required_ventadatos_fields = [
                'ventas_idtipodocumento', 'venta_tipopago', 'venta_formapago',
                'venta_codigocliente', 'venta_fecha', 'venta_monto',
                'venta_id_cajero', 'tipo_membresia_tipopago', 'venta_number_to_letter'
            ];
            
            // Extraer los datos
            $ventadatos = $request['ventadatos'];
            $tipo_membresia_id = $request['tipo_membresia_id'];
            $detalleventaServicios = $request['detalleventaServicios'];
            $cliente_ids = isset($detalleventaServicios['cliente_id']) ? $detalleventaServicios['cliente_id'] : null;
            if($ventas_pago["forma_pago"]==1){
                $id_caja=1;
            }else{
                $id_caja=2;
            }
            $total=$venta_monto;
            $id_tipo_comprobante=$ventas_pago["tipo_comprabante"];
            if ($id_tipo_comprobante == 1) {
                $td = 'BVE';
            }else{
                if ($id_tipo_comprobante == 2) {
                    $td = 'BFE';
                }else{
                    $td = 'SVE';
                }
            }
            $comprobante_detalle=0;
            if(isset($ventas_pago["comprobante_detalle"])){
                $comprobante_detalle=1;
            }
            $formapago=$ventas_pago["forma_pago"];
            $concepto=1;
            $descripcion="COBRO DE VENTA DE PRODUCTOS";
            $tipomovimiento=1;
            $id_movimiento=null;
            $id_movimiento_transporte=0;
            $monto = $venta_monto;
            if(isset($_POST["igv"])){
                $igv=(float)$_POST["igv"];
                $estado_igv="";
                $monto_igv=0;
                $monto_parcial=0;
                
            }else{
                $igv=0;
                $estado_igv="";
                $monto_igv=0;
                $monto_parcial=0;
                
            }
            if($igv==0){
                $estado_igv="0";
                $monto_igv=0;
                $monto_parcial=$monto;
                $idigv = 8;
            }else{
                $estado_igv="1";
                $sumaigv = (1+$igv);
                $monto_parcial=round(($monto/$sumaigv),2);
                $monto_igv=round(($monto-$monto_parcial),2);
                $idigv = 1;
            }

            if($ventas_pago["tipo_pago"]==1){
                $id_movimiento=$this->generar_movimiento($id_caja,$monto,$formapago,$concepto,$descripcion,$tipomovimiento,$id_tipo_comprobante,$descripcion_comprobante);
                if($id_movimiento==0){
                    echo 2;exit();
                }else{
                    $id_movimiento;
                }
            }

            if($id_tipo_comprobante!=0){
                $dat=$this->Mantenimiento_m->consulta3("SELECT * FROM detalle_doc_sede INNER JOIN documento ON detalle_doc_sede.detalle_id_documento = documento.id_documento
                    INNER JOIN tipo_documento ON documento.id_tipodocumento = tipo_documento.tipodoc_id where detalle_doc_sede.detalle_id_sede=".$_COOKIE["id_sede"]." and documento.id_tipodocumento=".$ventas_pago["tipo_comprabante"]);
                $descripcion_comprobante=$dat[0]["doc_serie"]."-".$dat[0]["doc_correlativo"];
                $serie=$dat[0]["doc_serie"];
                $correlativo=$dat[0]["doc_correlativo"];
                $id_documento=$dat[0]["id_documento"];
                $nuevo_correlativo=(int)$correlativo+1;
                $datos=array(
                    "doc_correlativo"=>$nuevo_correlativo
                );
                $this->Mantenimiento_m->actualizar("documento",$datos,$id_documento,"id_documento");
                $data=array(
                    "tipo_comprobante_descripcion"=>$descripcion_comprobante
                );
                $this->db->where('mov_id',$id_movimiento);
                $estado=$this->db->update('movimiento', $data);
            }

            $fechaactual = date("Y-m-d H:i:s");
            
		    $id_cliente=$ventas_pago["cliente"];

            foreach ($required_ventadatos_fields as $field) {
                if (!isset($ventadatos[$field])) {
                    throw new CustomException("El campo '{$field}' es requerido dentro de 'ventadatos'.", 400);
                }
            }
    
            // Iniciar la transacción
            $this->db->trans_begin();
            
            $ventainsert = array(
				"venta_fechapedido" => time(),
				"venta_pedidofecha" => date("Y-m-d H:i:s"),
				"venta_codigomozo" => $id_mozo,
				"venta_idsede" => $_COOKIE["id_sede"],
				"venta_monto" => $monto,
				"ventas_idtipodocumento"=>$ventas_pago["tipo_comprabante"],
				"venta_tipopago" => $ventas_pago["tipo_pago"],
				"venta_formapago" => $ventas_pago["forma_pago"],
				'venta_num_serie' =>$serie,
				"venta_estado_pagado" => 1,
				"venta_monto_entregado"=>$dinero_entrego,
				'venta_num_documento' => $correlativo,
				"venta_idmoneda"=>1,
				"venta_codigocliente" => $ventas_pago["cliente"],
				"venta_idmovimiento" => $id_movimiento,
				"venta_credito_usuario" => $_COOKIE["usuario_id"],
				"venta_fecha_pago"=>$fechaactual,
				"venta_estadococina"=>$pantallaestado,
				"venta_igv_estado"=>$estado_igv	,
				"venta_igv_monto"=>	$monto_igv	,
				"venta_estado_consumo"=> $comprobante_detalle,
				"venta_monto_sinigv"=>$monto_parcial,
				"venta_estado" => 2,
				"ventaid_tipo_venta" => $idigv,
				$nombre_variable => $id,
						"venta_nombre_descripcion"=>$array_cliente[2],
				"venta_documento_descripcion"=>$array_cliente[1],
				"venta_direccion_descripcion"=>$_POST["direccion_descripcion"],
				"venta_id_cajero"=> $_COOKIE["usuario_id"],
			);
			$this->db->insert('venta', $ventainsert);
			$id_venta = $this->db->insert_id();	
			$tipo_registro = 1;
            // 1. Consultar la tabla tipo_membresia y validar tipo_membresia_id
            $tipo_membresia = $this->Servicio_m->obtener_tipo_membresia_por_id($tipo_membresia_id);
            if (!$tipo_membresia) {
                $this->db->trans_rollback();
                throw new CustomException('El tipo de membresía no existe.', 404);
            }

            // Validar que 'cantidad_periodos' esté presente y sea un entero positivo
            if (!isset($ventadatos['cantidad_periodos']) || !is_numeric($ventadatos['cantidad_periodos']) || $ventadatos['cantidad_periodos'] <= 0) {
                throw new CustomException("El campo 'cantidad_periodos' es requerido y debe ser un número entero positivo.", 400);
            }

            $cantidad_periodos = (int)$ventadatos['cantidad_periodos'];
            // Acceder a tipo_membresia_tipoperiodo
            if (property_exists($tipo_membresia, 'tipo_membresia_tipopago')) {
                $tipo_pagoperiodo = $tipo_membresia->tipo_membresia_tipopago;
                // Ahora puedes utilizar $tipo_pagoperiodo según lo requieras
            } else {
                $this->db->trans_rollback();
                throw new CustomException('El tipo de pago de la membresía no está disponible.', 400);
            }
    
            // Procesar cada cliente_id en 'cliente_id'
            foreach ($cliente_ids as $id_cliente) {
                // Obtener información del cliente
                $cliente = $this->Servicio_m->obtener_cliente_por_id($id_cliente);
                if (!$cliente) {
                    $this->db->trans_rollback();
                    throw new CustomException("El cliente con ID {$id_cliente} no existe.", 404);
                }
     
    
                // Validar y traer datos de la tabla venta_tipomembresia
                $ultima_venta_membresia = $this->Servicio_m->obtener_ultima_venta_membresia_por_dni($id_cliente);
                
                
                // Calcular fecha_inicio y fecha_vencimiento_cliente
                if ($ultima_venta_membresia) {
                    // Cliente con membresía previa
                    $fecha_actual = date('Y-m-d');
                    if ($fecha_actual <= $ultima_venta_membresia->fecha_vencimiento_cliente) {
                        // Membresía vigente, extender desde la fecha de vencimiento actual
                        $fecha_inicio = $ultima_venta_membresia->fecha_vencimiento_cliente;
                    } else {
                        // Membresía expirada, iniciar desde la fecha actual
                        $fecha_inicio = $fecha_actual;
                    }
                } else {
                    // Cliente sin membresía previa
                    $fecha_inicio = date('Y-m-d');
                }
                
                // Calcular fecha de vencimiento según tipo de pago
                $servicio_duracion = $tipo_pagoperiodo;
                $fecha_vencimiento_cliente = $this->calcular_fecha_vencimiento($fecha_inicio, $servicio_duracion, $cantidad_periodos);
                // Preparar datos para la inserción en venta_tipomembresia
                $venta_membresia_data = [
                    'tipo_membresia_id' => $tipo_membresia_id,
                    'cliente_id' => $id_cliente,
                    'id_venta' => $ventadatos['venta_codigocliente'],
                    'servicio_duracion' => $servicio_duracion,
                    'membre_fecha_inicio' => $fecha_inicio,
                    'membresia_fecha_fin' => $fecha_vencimiento_cliente,
                    'estado' => 1
                ];
    
                // Insertar en venta_tipomembresia
                $resultado_insercion = $this->Servicio_m->insertar_venta_membresia($venta_membresia_data);
                if (!$resultado_insercion) {
                    $this->db->trans_rollback();
                    throw new CustomException("No se pudo registrar la venta de membresía para el cliente con ID {$id_cliente}.", 500);
                }
            }
    
            // Comprobar el estado de la transacción
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new CustomException("Error en la transacción al registrar la venta de membresía.", 500);
            } else {
                $this->db->trans_commit();
                // Respuesta de éxito con estándar
                $response = [
                    'success' => true,
                    'data' => [
                        'message' => 'Venta de membresía registrada correctamente para todos los clientes.'
                    ]
                ];
                return $this->output
                    ->set_status_header(201)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
            }
    
        } catch (CustomException $e) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            }
            // Respuesta de error con estándar
            $error_response = [
                'success' => false,
                'error' => [
                    'code' => $e->getHttpCode(),
                    'message' => $e->getMessage()
                ]
            ];
            return $this->output
                ->set_status_header($e->getHttpCode())
                ->set_content_type('application/json')
                ->set_output(json_encode($error_response));
        } catch (Exception $e) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            }
            // Respuesta de error genérico
            $error_response = [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Ocurrió un problema interno del servidor.'
                ]
            ];
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($error_response));
        }
    }

    // Método para validar precios
    private function validar_precio($precio) {
        return preg_match('/^\d{1,12}(\.\d{1,2})?$/', $precio);
    }

    // Método para validar fechas
    private function validar_fechas($fecha_inicio, $fecha_fin) {
        $fecha_inicio_valida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio);
        $fecha_fin_valida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin);

        // Convertir fechas a objetos DateTime para comparación
        $fecha_inicio_obj = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
        $fecha_fin_obj = DateTime::createFromFormat('Y-m-d', $fecha_fin);

        return $fecha_inicio_valida && $fecha_fin_valida && $fecha_fin_obj >= $fecha_inicio_obj;
    }

    // Método para validar tipos
    private function validar_tipo($tipo,$tipos_permitidos) {
        return in_array($tipo, $tipos_permitidos);
    }

    // Método para validar servicio_duracion
    private function validar_servicio_duracion($duracion) {
        return is_numeric($duracion) && $duracion >= 1 && $duracion <= 30; // Debe estar entre 1 y 30
    }

    private function calcular_fecha_vencimiento($fecha_inicio, $servicio_duracion, $cantidad_periodos = 1) {
        $fecha = new DateTime($fecha_inicio);
        switch ($servicio_duracion) {
            case '01': // Diario
                $intervalo = 'P' . (1 * $cantidad_periodos) . 'D'; // Días
                break;
            case '02': // Semanal
                $intervalo = 'P' . (7 * $cantidad_periodos) . 'D'; // Días
                break;
            case '03': // Quincenal
                $intervalo = 'P' . (15 * $cantidad_periodos) . 'D'; // Días
                break;
            case '04': // Mensual
                $intervalo = 'P' . $cantidad_periodos . 'M'; // Meses
                break;
            default:
                throw new CustomException('Tipo de pago inválido.', 400);
        }
        $fecha->add(new DateInterval($intervalo));
        return $fecha->format('Y-m-d');
    }

    public function buscar_producto(){
        try { 
            $data_token = json_decode($this->consultar_token(),true);
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata,true); 
            $sql = " SELECT 1 as producto_encendido,
            999 as producto_id_tipoproducto,
            tipo_membresia_id as producto_id,
            tipo_membresia_descripcion as producto_descripcion,
            tipo_membresia_precio_mes as producto_precio,
            999 as categoria_producto_id,
            999 as stock,
            'default.png' as producto_imagen FROM tipo_membresia WHERE tipo_membresia_estado = 1  OR ( tipo_membresia.tipo_membresia_descripcion LIKE '');";
            $tipo_membresias=$this->db->query($sql)->result_array();
            $response=array();
            if (count($tipo_membresias) != 0) {
                $stock = 999;
                foreach ($tipo_membresias as $key => $value) {
                    $url= 'default.jpg';
                }

                $response[$key]["producto_encendido"]=$value["producto_encendido"];
                $response[$key]["producto_id_tipoproducto"]=$value["producto_id_tipoproducto"];
                 $response[$key]["categoria_producto_id"]=$value["categoria_producto_id"];
                $response[$key]["producto_id"]=$value["producto_id"];
                $response[$key]["producto_descripcion"]=$value["producto_descripcion"];
                $response[$key]["producto_precio"]=$value["producto_precio"];
                $response[$key]["stock"]=(int)$stock;
                $response[$key]["imagen"]=$url;
            }
            $response1["productos"]=$response;
            header('Content-type:application/json;charset=utf-8');
            echo json_encode($response1);exit();
        }catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("status" => false, "message" => "An error occurred: " . $e->getMessage()));
            exit();
        }
    }

    public function gestionar_cliente(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true); // Decodifica el JSON de la solicitud
        $dni = $request['dni'];
        $nueva_fecha_fin = isset($request['fechaFinMembresia']) ? $request['fechaFinMembresia'] : null;
    
        $response = [];
        try {
            // Verificar si el cliente existe
            $cliente = $this->Servicio_m->verificar_cliente_por_dni($dni);
            $cliente_estado_fechavencimiento = $request['habilitarFechaFin'] ? 1 : 0; // Asignar estado según habilitarFechaFin
            $cliente_tipomembresia = $request['tipoMembresia']; // Asignar tipo de membresía
    
            if ($cliente) {
                // Buscar membresías válidas
                $membresias = $this->Servicio_m->buscar_membresias_validas($cliente->cliente_id);
                $ultima_membresia = null;
                if ($membresias) {
                    // Obtener el último registro de membresía
                    $ultima_membresia = end($membresias);
                }
                // Preparar datos para actualizar cliente
                $actualizacion_cliente = [
                    'cliente_nombres' => $request['nombres'],
                    'cliente_direccion' => $request['direccion'],
                    'cliente_telefono' => $request['telefono'],
                    'cliente_email' => $request['correo'],
                    'fechaFinMembresia' => $request['fechaFinMembresia'],
                    'cliente_estado_fechavencimiento' => $cliente_estado_fechavencimiento,
                    'cliente_tipomembresia' => $cliente_tipomembresia
                ];
                // Actualizar cliente y membresía en una transacción
                $resultado = $this->Servicio_m->actualizar_cliente_y_membresia(
                    $cliente->cliente_id,
                    $actualizacion_cliente,
                    $ultima_membresia ? $ultima_membresia->membresia_id : null,
                    $nueva_fecha_fin
                );
                if (!$resultado) {
                    throw new Exception('Error en la actualización de cliente o membresía.');
                }
    
                $response['estado'] = true;
                $response['mensaje'] = 'Cliente y membresía actualizados correctamente.';
            } else {
                // Si no existe el cliente, registrar uno nuevo
                $nuevo_cliente = [
                    'cliente_nombres' => $request['nombres'],
                    'cliente_direccion' => $request['direccion'],
                    'cliente_dni' => $dni,
                    'cliente_telefono' => $request['telefono'],
                    'cliente_email' => $request['correo'],
                    'fechaFinMembresia' => $request['fechaFinMembresia'],
                    'cliente_estado_fechavencimiento' => $cliente_estado_fechavencimiento,
                    'cliente_tipomembresia' => $cliente_tipomembresia
                ];
    
                $cliente_id = $this->Servicio_m->registrar_cliente($nuevo_cliente);
    
                if (!$cliente_id) {
                    throw new Exception('Error al registrar el cliente.');
                }
    
                $response['estado'] = true;
                $response['mensaje'] = 'Cliente registrado exitosamente.';
                $response['cliente_id'] = $cliente_id;
            }
    
            echo json_encode($response);
        } catch (Exception $e) {
            $response['estado'] = false;
            $response['mensaje'] = 'Error interno: ' . $e->getMessage();
            log_message('error', 'Error en gestionar_cliente: ' . $e->getMessage());
            echo json_encode($response);
        }
    }

    public function buscar_cliente_servicios() {
        $search = $this->input->get("search");
        try {
            // Llamar al modelo para buscar clientes
            $clientes = $this->Servicio_m->buscar_clientes($search);

            $data = [];
            foreach ($clientes as $key => $value) {
                $data[$key] = [
                    "id" => $value["cliente_id"] . "/" . $value["cliente_dni"] . "/" . $value["cliente_nombres"] . "/0/" . $value["cliente_direccion"],
                    "text" => $value["cliente_nombres"],
                    "documento" => $value["cliente_dni"],
                    "direccion_id" => 0,
                    "direccion_descripcion" => $value["cliente_direccion"]
                ];
            }

            echo json_encode($data);

        } catch (Exception $e) {
            // Manejo de errores
            log_message('error', 'Error en buscar_cliente: ' . $e->getMessage());
            echo json_encode([
                "estado" => false,
                "mensaje" => "Error al buscar clientes. Por favor, contacte a soporte técnico."
            ]);
        }
    }

    public function registrar_asistencia() {
        try {
            // Obtener datos del POST
            $postdata = file_get_contents("php://input");
            $request = json_decode($postdata, true);
    
            if (!isset($request['dni'])) {
                throw new Exception('El DNI es obligatorio.');
            }
    
            $dni = $request['dni'];
            $fechaActual = date('Y-m-d');
    
            // Buscar cliente por DNI
            $this->db->select('clientes.*, tipo_membresia.tipo_membresia_descripcion, tipo_membresia.estadoTrotadora');
            $this->db->from('clientes');
            $this->db->join('tipo_membresia', 'clientes.tipo_membresia = tipo_membresia.tipo_membresia_id', 'left');
            $this->db->where('clientes.cliente_dni', $dni);
            $cliente = $this->db->get()->row();

    
            if (!$cliente) {
                echo json_encode([
                    'mensaje' => 'Lo sentimos, el DNI proporcionado no está registrado en nuestra base de datos. Por favor, contacte con el personal de recepción para más información.',
                    'estado' => 'error'
                ]);
                return;
            }
    
            // Buscar membresías activas
            $membresias = $this->db
                ->select('*')
                ->from('membresia')
                ->where('cliente_id', $cliente->cliente_id)
                ->where('membresia_fecha_fin >=', $fechaActual)
                ->order_by('membresia_fecha_fin', 'DESC')
                ->get()
                ->result();
    
            $fechaVencimiento = null;
            if (!empty($membresias)) {
                // Tomar la última fecha de vencimiento
                $fechaVencimiento = $membresias[0]->membresia_fecha_fin;
            } else if (isset($cliente->fechaFinMembresia) && $cliente->fechaFinMembresia >= $fechaActual) {
                $fechaVencimiento = $cliente->fechaFinMembresia;
            } else {
                echo json_encode([
                    'mensaje' => 'Estimado usuario, su membresía ha vencido. Por favor, renueve su membresía para continuar disfrutando de nuestros servicios.',
                    'estado' => 'error',
                    'nombre' => $cliente->cliente_nombres
                ]);
                return;
            }
    
            // Agregar mensaje basado en estadoTrotadora
            if ($cliente->estadoTrotadora == 0) {
                $mensajeTrotadora = "Estimado {$cliente->cliente_nombres}, su membresía solo le permite el uso del área de pesas.";
            } else {
                $mensajeTrotadora = "Estimado {$cliente->cliente_nombres}, usted tiene acceso a todos los beneficios de Mr. Fit.";
            }
            // Calcular días restantes para la membresía
            $fechaVencimientoObj = new DateTime($fechaVencimiento);
            $fechaActualObj = new DateTime($fechaActual);
            $diasRestantes = $fechaActualObj->diff($fechaVencimientoObj)->days;
    
            $estadoVencimiento = 0; // Por defecto, más de 20 días
            if ($diasRestantes < 10) {
                $estadoVencimiento = 2;
            } else if ($diasRestantes < 20) {
                $estadoVencimiento = 1;
            }
    
            // Generar mensaje amigable basado en los días restantes
            $mensajeAmigable = "Estimado usuario, ";
            if ($diasRestantes < 15) {
                $mensajeAmigable .= "su membresía está próxima a vencer. Le quedan {$diasRestantes} días. ";
            } else {
                $mensajeAmigable .= "su membresía sigue vigente. ";
            }
            $mensajeAmigable .= "Su fecha de vencimiento es el {$fechaVencimiento}.";
    
            // Validar si el usuario ya ingresó hoy
            $asistencia = $this->db
                ->select('*')
                ->from('asistencia')
                ->where('cliente_id', $cliente->cliente_id)
                ->where('asistencia_fecha', $fechaActual)
                ->get()
                ->row();
    
            if ($asistencia) {
                echo json_encode([
                    'mensaje' => 'Estimado Usuario usted ya ingresó hoy día a las ' . $asistencia->asistencia_fecha_hora,
                    'mensajeVencimiento' => $mensajeAmigable,
                    'mensajeTrotadora' => $mensajeTrotadora,
                    'estado' => 'info',
                    'fechaVencimiento' => $fechaVencimiento,
                    'nombre' => $cliente->cliente_nombres,
                    'diasRestantes' => $diasRestantes,
                    'estadoVencimiento' => $estadoVencimiento
                ]);
                return;
            }
    
            // Registrar la asistencia
            $asistenciaData = [
                'cliente_id' => $cliente->cliente_id,
                'asistencia_fecha_hora' => date('Y-m-d H:i:s'),
                'asistencia_fecha' => $fechaActual,
                'asistencia_estado' => 1
            ];
    
            $this->db->insert('asistencia', $asistenciaData);
    
            echo json_encode([
                'mensaje' => 'Se registró hoy ' . $asistenciaData['asistencia_fecha_hora'] ,
                'mensajeVencimiento' => $mensajeAmigable,
                'mensajeTrotadora' => $mensajeTrotadora,
                'estado' => 'success',
                'nombre' => $cliente->cliente_nombres,
                'fechaVencimiento' => $fechaVencimiento,
                'diasRestantes' => $diasRestantes,
                'estadoVencimiento' => $estadoVencimiento
            ]);
    
        } catch (Exception $e) {
            echo json_encode([
                'mensaje' => 'Error interno: ' . $e->getMessage(),
                'estado' => 'error'
            ]);
        }
    }



}
?>