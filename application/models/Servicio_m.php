<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Servicio_m extends CI_Model{
    public function __construct(){
        parent::__construct();
    }

    public function get_servicios($tipoproducto_id = null) {
        try {
            if (empty($tipoproducto_id)) {
                $this->db->select("
                    tipo_membresia_id as id,
                    tipo_membresia_descripcion AS nombre_servicio,
                    CONCAT('S/. ', FORMAT(tipo_membresia_precio_mes, 2)) AS precio_servicio,
                    CONCAT(
                        CASE 
                            WHEN tipo_membresia_tipoperiodo = '01' THEN 'Ilimitado'
                            WHEN tipo_membresia_tipoperiodo = '02' THEN 'Temporal'
                            ELSE 'Desconocido' -- Opcional
                        END,
                        ' (',
                        CASE 
                            WHEN tipo_membresia_tipoperiodo = '01' THEN 'Sin Fecha Fin'
                            ELSE CONCAT(tipo_membresia_fechainicio, ' / ', tipo_membresia_fechafin)
                        END,
                        ')'
                    ) AS tipo_periodo,
                    CASE 
                        WHEN tipo_membresia_categoriamembresia = '01' THEN 'Individual'
                        WHEN tipo_membresia_categoriamembresia = '02' THEN CONCAT('Grupal (', tipo_membresia_cantidadpersona, ')')
                        ELSE 'Desconocido' -- Opcional
                    END AS categoria_membresia,
                    CASE 
                        WHEN tipo_membresia_tipopago = '01' THEN 
                                CASE 
                                    WHEN tipo_membresia_duracion = 1 THEN '1 día'
                                    ELSE CONCAT(tipo_membresia_duracion, ' días')
                                END
                        WHEN tipo_membresia_tipopago = '02' THEN 
                                CASE 
                                    WHEN tipo_membresia_duracion = 1 THEN '1 Mes'
                                    ELSE CONCAT(tipo_membresia_duracion, ' Meses')
                                END
                        WHEN tipo_membresia_tipopago = '03' THEN 
                                CASE 
                                    WHEN tipo_membresia_duracion = 1 THEN '1 Año'
                                    ELSE CONCAT(tipo_membresia_duracion, ' Años')
                                END
                        ELSE 'Desconocido'
                    END AS tipo_pago,
                    CASE
                        WHEN tipo_membresia_estado = '0' THEN 'Inactivo'
                        WHEN tipo_membresia_estado = '1' THEN 'Activo'
                        ELSE 'Desconocido' -- Opcional
                    END AS estado
                ", false);
            }else{
                $this->db->select("*");
            }

            $this->db->from('tipo_membresia');
            //$this->db->where('tipo_membresia_estado',"1");
            if (!empty($tipoproducto_id)) {
                $this->db->where('tipo_membresia_id', $tipoproducto_id);
            }
            $query = $this->db->get();
            if (!$query) {
                throw new Exception('Error en la consulta de Paquetes.');
            }
            return $query->result();
        }catch (Exception $e) {
            log_message('error', 'Error en get_servicios: ' . $e->getMessage());
            return false;
        }
    }

    public function get_membresia_buscar($descripcion = null) {
        try {
            $this->db->select("
                tipo_membresia_id as id,
                tipo_membresia_descripcion AS nombre_servicio,
                FORMAT(tipo_membresia_precio_mes, 2) AS precio_servicio,
                CONCAT(
                    CASE 
                        WHEN tipo_membresia_tipoperiodo = '01' THEN 'Ilimitado'
                        WHEN tipo_membresia_tipoperiodo = '02' THEN 'Temporal'
                        ELSE 'Desconocido'
                    END,
                    ' (',
                    CASE 
                        WHEN tipo_membresia_tipoperiodo = '01' THEN 'Sin Fecha Fin'
                        ELSE CONCAT(tipo_membresia_fechainicio, ' / ', tipo_membresia_fechafin)
                    END,
                    ')'
                ) AS tipo_periodo,
                CASE 
                    WHEN tipo_membresia_categoriamembresia = '01' THEN 'Individual'
                    WHEN tipo_membresia_categoriamembresia = '02' THEN CONCAT('Grupal (', tipo_membresia_cantidadpersona, ')')
                    ELSE 'Desconocido'
                END AS categoria_membresia,
                CASE 
                    WHEN tipo_membresia_tipopago = '01' THEN 
                            CASE 
                                WHEN tipo_membresia_duracion = 1 THEN '1 día'
                                ELSE CONCAT(tipo_membresia_duracion, ' días')
                            END
                    WHEN tipo_membresia_tipopago = '02' THEN 
                            CASE 
                                WHEN tipo_membresia_duracion = 1 THEN '1 Mes'
                                ELSE CONCAT(tipo_membresia_duracion, ' Meses')
                            END
                    WHEN tipo_membresia_tipopago = '03' THEN 
                            CASE 
                                WHEN tipo_membresia_duracion = 1 THEN '1 Año'
                                ELSE CONCAT(tipo_membresia_duracion, ' Años')
                            END
                    ELSE 'Desconocido'
                END AS tipo_pago,
                CASE
                    WHEN tipo_membresia_estado = '0' THEN 'Inactivo'
                    WHEN tipo_membresia_estado = '1' THEN 'Activo'
                    ELSE 'Desconocido'
                END AS estado
            ", false);
            $this->db->from('tipo_membresia');
    
            // Aplica el filtro por descripción si está definido
            if (!empty($descripcion)) {
                $this->db->like('tipo_membresia_descripcion', $descripcion);
            }
    
            $query = $this->db->get();
    
            if (!$query) {
                throw new Exception('Error en la consulta de búsqueda de membresías.');
            }
    
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'Error en get_membresia_buscar: ' . $e->getMessage());
            return false;
        }
    }

    public function insertar_servicio($data) {
        // Prepara los datos para la inserción
        $servicio_data = [
            'tipo_membresia_descripcion' => $data['tipo_membresia_descripcion'], // Asegúrate de que estos nombres coincidan con los de tu base de datos
            'tipo_membresia_precio_mes' => $data['tipo_membresia_precio_mes'],
            'tipo_membresia_duracion' => $data['tipo_membresia_duracion'],
            'tipo_membresia_tipoperiodo' => $data['tipo_membresia_tipoperiodo'],
            'tipo_membresia_fechainicio' => $data['tipo_membresia_fechainicio'],
            'tipo_membresia_fechafin' => $data['tipo_membresia_fechafin'],
            'tipo_membresia_categoriamembresia' => $data['tipo_membresia_categoriamembresia'],
            'tipo_membresia_tipopago' => $data['tipo_membresia_tipopago'],
            'tipo_membresia_cantidadpersona' => $data['tipo_membresia_cantidadpersona']     
        ];

        // Intenta realizar la inserción en la tabla correspondiente
        try {
            if ($this->db->insert('tipo_membresia', $servicio_data)) {
                return true; // Retorna verdadero si la inserción fue exitosa
            } else {
                // Manejo de error si la inserción falla
                $error = $this->db->error(); // Obtiene el error de la base de datos
                log_message('error', 'Error al insertar tipo membresia: ' . $error['message']);
                return false; // Retorna falso si hubo un error
            }
        } catch (Exception $e) {
            // Captura excepciones y registra el error
            log_message('error', 'Excepción al insertar tipo membresia: ' . $e->getMessage());
            return false; // Retorna falso en caso de excepción
        }
    }

    public function editar_servicio($data) {
        $this->db->where('tipo_membresia_id', $data['tipo_membresia_id']);
        unset($data['tipo_membresia_id']); // Eliminar el ID del array para no actualizarlo

        // Intenta realizar la actualización
        try {
            if ($this->db->update('tipo_membresia', $data)) {
                return true; // Retorna verdadero si la actualización fue exitosa
            } else {
                // Manejo de error si la actualización falla
                $error = $this->db->error(); // Obtiene el error de la base de datos
                log_message('error', 'Error al actualizar producto: ' . $error['message']);
                return false; // Retorna falso si hubo un error
            }
        } catch (Exception $e) {
            // Captura excepciones y registra el error
            log_message('error', 'Excepción al actualizar producto: ' . $e->getMessage());
            return false; // Retorna falso en caso de excepción
        }
    }

    public function obtener_tipo_membresia_por_id($tipo_membresia_id) {
        try {
            $this->db->select('*');
            $this->db->from('tipo_membresia');
            $this->db->where('tipo_membresia_id', $tipo_membresia_id);
            $query = $this->db->get();
    
            if ($query === false) {
                // Manejo de errores en la consulta
                $error = $this->db->error();
                log_message('error', 'Error al obtener tipo de membresía: ' . $error['message']);
                return false;
            }
    
            if ($query->num_rows() > 0) {
                return $query->row(); // Retorna el registro como objeto
            } else {
                return false; // No se encontró el tipo de membresía
            }
        } catch (Exception $e) {
            // Captura de excepciones y registro del error
            log_message('error', 'Excepción al obtener tipo de membresía: ' . $e->getMessage());
            return false;
        }
    }

    public function obtener_cliente_por_id($id_cliente) {
        try {
            $this->db->select('*'); // Asegúrate de que 'numero_dni' está incluido
            $this->db->from('clientes'); // Reemplaza 'clientes' con el nombre real de tu tabla de clientes
            $this->db->where('cliente_id', $id_cliente);
            $query = $this->db->get();
    
            if ($query === false) {
                $error = $this->db->error();
                log_message('error', 'Error al obtener cliente: ' . $error['message']);
                return false;
            }
    
            if ($query->num_rows() > 0) {
                return $query->row();
            } else {
                // Manejar el caso donde no se encuentra el cliente
                return null;
            }
        } catch (Exception $e) {
            log_message('error', 'Excepción al obtener cliente: ' . $e->getMessage());
            return false;
        }
    }

    public function obtener_ultima_venta_membresia_por_dni($id_cliente) {
        try {
            // Verificar que el DNI no esté vacío
            if (empty($dni_cliente)) {
                throw new Exception('El DNI del cliente es requerido.');
            }
    
            $this->db->select('*');
            $this->db->from('membresia');
            $this->db->where('cliente_id', $id_cliente);
            $this->db->order_by('membresia_fecha_fin', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get();
    
            if ($query === false) {
                // Manejo de errores en la consulta
                $error = $this->db->error();
                log_message('error', 'Error al obtener la última venta de membresía por DNI: ' . $error['message']);
                return false;
            }
    
            if ($query->num_rows() > 0) {
                return $query->row(); // Retorna el registro como objeto
            } else {
                // No se encontró ninguna venta de membresía para el DNI proporcionado
                return null;
            }
        } catch (Exception $e) {
            // Captura de excepciones y registro del error
            log_message('error', 'Excepción al obtener la última venta de membresía por DNI: ' . $e->getMessage());
            return false;
        }
    }

    public function insertar_venta_membresia($data) {
        try {
            if ($this->db->insert('venta_tipomembresia', $data)) {
                return true;
            } else {
                $error = $this->db->error();
                log_message('error', 'Error al insertar venta_tipomembresia: ' . $error['message']);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Excepción al insertar venta_tipomembresia: ' . $e->getMessage());
            return false;
        }
    }
    
    public function verificar_cliente_por_dni($dni)
    {
        try {
            $this->db->select('*');
            $this->db->from('clientes');
            $this->db->where('cliente_dni', $dni);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->row(); // Retorna el cliente si existe
            } else {
                return null; // No existe el cliente
            }
        } catch (Exception $e) {
            log_message('error', 'Error en verificar_cliente_por_dni: ' . $e->getMessage());
            return false;
        }
    }
    // Buscar membresías válidas por cliente_id y fecha_vencimiento
    public function buscar_membresias_validas($cliente_id)
    {
        try {
            $this->db->select('*');
            $this->db->from('membresia');
            $this->db->where('cliente_id', $cliente_id);
            $this->db->where('membresia_fecha_fin >=', date('Y-m-d')); // Fecha superior a la actual
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->result(); // Retorna las membresías válidas
            } else {
                return null; // No hay membresías válidas
            }
        } catch (Exception $e) {
            log_message('error', 'Error en buscar_membresias_validas: ' . $e->getMessage());
            return false;
        }
    }
    public function registrar_cliente($data)
    {
        try {
            if ($this->db->insert('clientes', $data)) {
                return $this->db->insert_id(); // Retorna el ID del nuevo cliente
            } else {
                $error = $this->db->error();
                log_message('error', 'Error al registrar cliente: ' . $error['message']);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Excepción al registrar cliente: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizar_membresia($membresia_id, $nueva_fecha_fin){
        try {
            $data = [];
            if (!is_null($nueva_fecha_fin)) {
                $data['membresia_fecha_fin'] = $nueva_fecha_fin;
            }
    
            if (!empty($data)) {
                $this->db->where('membresia_id', $membresia_id);
                if ($this->db->update('membresia', $data)) {
                    return true;
                } else {
                    $error = $this->db->error();
                    log_message('error', 'Error al actualizar membresía: ' . $error['message']);
                    return false;
                }
            }
            return true; // No hubo cambios porque `nueva_fecha_fin` es null
        } catch (Exception $e) {
            log_message('error', 'Excepción al actualizar membresía: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizar_cliente_y_membresia($cliente_id, $actualizacion_cliente, $membresia_id = null, $nueva_fecha_fin = null){
        $this->db->trans_start(); // Inicia la transacción
    
        try {
            // Verificar cliente existente
            $cliente_existente = $this->db->get_where('clientes', ['cliente_id' => $cliente_id])->row();
            if (!$cliente_existente) {
                throw new Exception('El cliente con ID ' . $cliente_id . ' no existe.');
            }
    
            // Actualizar membresía si corresponde
            if (!is_null($membresia_id) && !is_null($nueva_fecha_fin)) {
                $nueva_fecha_fin = date('Y-m-d', strtotime($nueva_fecha_fin)); // Formatear fecha
                $this->db->where('membresia_id', $membresia_id);
                $this->db->update('membresia', ['membresia_fecha_fin' => $nueva_fecha_fin]);
                $error = $this->db->error();
                if ($error['code'] !== 0) { // Un código diferente de 0 indica un error
                    throw new Exception('Error al actualizar la membresía: ' . $error['message']);
                }
            }
    
            // Actualizar cliente
            $this->db->where('cliente_id', $cliente_id);
            $this->db->update('clientes', $actualizacion_cliente);
    
            $error = $this->db->error();
            if ($error['code'] !== 0) { // Un código diferente de 0 indica un error
                throw new Exception('Error al actualizar cliente: ' . $error['message']);
            }
    
            $this->db->trans_complete(); // Finaliza la transacción
    
            if ($this->db->trans_status() === false) {
                throw new Exception('Transacción fallida.');
            }
    
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback(); // Revertir transacción
            log_message('error', 'Error en actualizar_cliente_y_membresia: ' . $e->getMessage());
            return false;
        }
    }

    public function buscar_clientes( $search) {

        // Consulta base con límite de 10
        $sql = "SELECT 
                clientes.cliente_id, 
                clientes.cliente_nombres, 
                tipo_membresia.tipo_membresia_descripcion AS cliente_direccion,
                IF(clientes.cliente_dni IS NULL, 'No cuenta con D.N.I', clientes.cliente_dni) AS cliente_dni 
            FROM clientes
            LEFT JOIN tipo_membresia ON clientes.cliente_tipomembresia = tipo_membresia.tipo_membresia_id
            WHERE clientes.estado = 1 
              AND (clientes.cliente_nombres LIKE ? OR clientes.cliente_dni LIKE ? ) 
            LIMIT 10";

        // Parámetros de búsqueda
        $params = ["%$search%", "%$search%"];

        // Ejecutar la consulta
        try {
            $query = $this->db->query($sql, $params);
            return $query->result_array();
        } catch (Exception $e) {
            log_message('error', 'Error en buscar_clientes: ' . $e->getMessage());
            throw $e; // Relanzar la excepción para manejo en el controlador
        }
    }

    public function traer_asistencias($cliente_id) {
        try {
            $this->db->select("
            DATE_FORMAT(asistencia.asistencia_fecha_hora, '%Y') AS year,
            DATE_FORMAT(asistencia.asistencia_fecha_hora, '%m') AS month,
            DATE_FORMAT(asistencia.asistencia_fecha_hora, '%Y-%m-%d') AS fechaIngreso,
            DATE_FORMAT(asistencia.asistencia_fecha_hora, '%h:%i %p') AS horaIngreso,
            tipo_membresia.tipo_membresia_descripcion AS tipoMembresia
        ", false);
    
            $this->db->from('asistencia');
            $this->db->join('clientes', 'asistencia.cliente_id = clientes.cliente_id', 'inner');
            $this->db->join('tipo_membresia', 'clientes.cliente_tipomembresia = tipo_membresia.tipo_membresia_id', 'inner');
            $this->db->where('asistencia.cliente_id', $cliente_id);
            $this->db->order_by('asistencia.asistencia_fecha', 'DESC');
    
            $query = $this->db->get();
    
            if ($query === false) {
                throw new Exception('Error al obtener asistencias del cliente.');
            }
    
            return $query->result_array();
        } catch (Exception $e) {
            log_message('error', 'Error en traer_asistencias: ' . $e->getMessage());
            return false;
        }
    }
}