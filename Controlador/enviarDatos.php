<?php

require_once '../Modelo/registrar.php';

class enviarDatos
{
    private $registrar;

    public function __construct()
    {
        $this->registrar = new Registrar();
    }

    public function guardarCotizacion($data)
    {
        // Recibir datos de texto
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $proyecto = $_POST['proyecto'] ?? '';

        // Recibir y decodificar las filas
        $filas = [];
        if (isset($_POST['filas'])) {
            $filas = json_decode($_POST['filas'], true);
        }

        // Procesar archivos adjuntos
        $archivos = [];
        $rutaGuardada = null; 

        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                /* Guardar el achivo en una variable para enviarlo a drive */
                $nombreArchivo = $file['name'];
                $rutaTemporal = $file['tmp_name'];
                $archivos[] = [
                    'nombre' => $nombreArchivo,
                    'ruta' => $rutaTemporal,
                    'tipo' => $file['type'],
                    'tamaño' => $file['size']
                ];
                
                $rutaGuardada = $this->registrar->cargarArchivo($file, $proyecto);
                          
            }
        }
        
        if ($rutaGuardada === false) {
            // Manejar error al cargar el archivo
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al cargar el archivo: ' . $nombreArchivo
            ]);
            return;
        }     
     
        // Procesar los datos y guardarlos en la base de datos
        $respuesta =  $this->registrar->registrarDatos(
            $nombre,
            $correo,
            $proyecto,
            $filas,
            $rutaGuardada
        );
     
        if($respuesta) {
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'mensaje' => '¡La solicitud de cotización ha sido registrada con éxito!',
                'consecutivo' => $respuesta // Aquí puedes devolver el ID o consecutivo generado
            ]);
        } else {
            // Respuesta de error
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar la Solicitud de cotización'
            ]);
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'guardarCotizacion') {
    $controller = new enviarDatos();
    // Puedes pasar $_POST directamente si lo deseas
    $controller->guardarCotizacion($_POST);
}