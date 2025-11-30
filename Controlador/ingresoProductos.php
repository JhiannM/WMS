<?php

require_once '../Modelo/registrar.php';
class ConsultarArticulos
{
    private $registrar;

    public function __construct()
    {
        $this->registrar = new Registrar();
    }

    /* Obtener los articulos */
    public function obtenerArticulos()
    {
        $busqueda = isset($_POST['search']) ? $_POST['search'] : '';

        // Llamar al método de la clase Registrar para obtener los artículos
        $articulos = $this->registrar->consultarArticulos($busqueda);

        if ($articulos) {
            echo json_encode([
                'success' => true,
                'data' => $articulos
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se encontraron artículos'
            ]);
        }
    }

    /* Obtener los proyectos */
    public function obtenerProyectos()
    {
        // Llamar al método de la clase Registrar para obtener los proyectos
        $proyectos = $this->registrar->consultarProyectos();

        if ($proyectos) {
            echo json_encode([
                'success' => true,
                'data' => $proyectos
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se encontraron proyectos'
            ]);
        }
    }
}

/* Ejecutador de funciones */
if (isset($_GET['action']) && $_GET['action'] === 'articulos') {
    $controller = new ConsultarArticulos();
    // Puedes pasar $_POST directamente si lo deseas
    $controller->obtenerArticulos($_POST);
} else if (isset($_GET['action']) && $_GET['action'] === 'proyectos') {
    $controller = new ConsultarArticulos();
    $controller->obtenerProyectos();
} else if (isset($_GET['action']) && $_GET['action'] === 'jefes') {
    $controller = new ConsultarArticulos();
    $controller->obtenerJefes();
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Acción no válida'
    ]);
}