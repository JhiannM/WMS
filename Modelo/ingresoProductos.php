<?php

include '../vendor/autoload.php';
require_once '../public/correos/correos.php';

use Google\Client as Google_Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Sheets as Google_Service_Sheets;
use Google\Service\Sheets\ValueRange as Google_Service_Sheets_ValueRange;
class Registrar {
  
    /* Enviar Datos a la hoja de calculo */
    public function registrarDatos($nombre, $correo, $proyecto, $filas, $archivos) 
    {
        $fecha = date('Y-m-d H:i:s');

        $client = new Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/spreadsheets']);
        $client->setAuthConfig('../cotizaciones-462816-324b5de0d7a7.json');
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = '1YlBwBQEFVwJHwH5ziNzudjQ-8wPCZz7F1FOntJIIoa0'; // ID de tu hoja de cálculo
        $range = 'cotizaciones!A2:L'; // Rango donde se guardarán los datos
        $range2 = 'cotizaciones!A2:A';

        $url_carpeta = $archivos['url_carpeta'] ?? '';
        $url_archivo = $archivos['url_archivo'] ?? '';
                
        /* Crear numero de consecutivo */
        $response2 = $service->spreadsheets_values->get($spreadsheetId, $range2);
        $values2 = $response2->getValues();
        $consecutivo = 1;                           // Valor por defecto si no hay datos
        if (!empty($values2)) {
            $lastRow = end($values2);
            $consecutivo = intval($lastRow[0]) + 1; 
        }         

        $values = []; 
        foreach ($filas as $fila) {

            $otro = $fila['otro'] ?? '';  

            if ($otro == '') {
                $values[] = [
                    $consecutivo, 
                    $fecha, 
                    $nombre, 
                    $correo, 
                    $proyecto, 
                    $fila['articulo'] ?? '', 
                    $fila['cantidad'] ?? '', 
                    $fila['fecha'] ?? '', 
                    $fila['aprueba'] ?? '', 
                    $url_carpeta, 
                    $url_archivo,
                    $fila['observaciones'] ?? ''
                ]; 
                
            }

            if ($otro != '') {
                $values[] = [
                    $consecutivo, 
                    $fecha, 
                    $nombre, 
                    $correo, 
                    $proyecto, 
                    'OTRO('.$fila['otro'].')' ?? '', 
                    $fila['cantidad'] ?? '', 
                    $fila['fecha'] ?? '', 
                    $fila['aprueba'] ?? '', 
                    $url_carpeta, 
                    $url_archivo, 
                    $fila['observaciones'] ?? ''
                ]; 
                
            }
        }

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        try {
            $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            
            if ($result->getUpdates()->getUpdatedCells() > 0) {

                if ($otro != '') {
                    /* Correo de Registro para compras */
                    $mail = new Correos();
                    $mail->cotizacionyArticuloNuevos($otro, $consecutivo);
                }

                /* Correo de confirmación al usuario */
                $mail = new Correos();
                $mail->cotizacionConfirmacion($consecutivo, $correo, $nombre);
                $mail->nuevaCotizacion($consecutivo);
                       
                return $consecutivo;
            } else {
                return false; 
            }

        } catch (Exception $e) {
            echo 'Error al guardar los datos: ' . $e->getMessage();
            return false; // Error al guardar los datos
        }

    }

    /* REcibir el archivo  OK*/
    public function cargarArchivo($archivo, $proyecto) 
    {
        
        $destino     = __DIR__ . '../Archivos/' . basename($archivo['name']);
        $peso        = $archivo['size'];   
        $nombre      = $archivo['name'];
        $tipo        = $archivo['type'];
        $nombre_tipo = $nombre;

        $id_drive = $this->subirDrive($archivo['tmp_name'], $nombre_tipo, $proyecto);
        if ($id_drive) {
            // Aquí puedes guardar la referencia al archivo en la base de datos si es necesario
            return $id_drive;
        } else {

            if (move_uploaded_file($archivo['tmp_name'], $destino)) {
                return $destino;
            }

            return false;
        }
    }   

    /* Cargar archivo al drive OK */
    private function subirDrive($temporal, $nombreArchivo, $proyecto) 
    {

        $client = new Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/spreadsheets']);
        $client->setAuthConfig('../cotizaciones-462816-324b5de0d7a7.json');
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = '1U1zcmWgaPhsJITwHr-9MrgcF7kQHMo07SLEbN0Wgk1Y';  
        $range2 = 'cotizaciones!A2:A';
                
        /* Crear numero de consecutivo */
        $response2 = $service->spreadsheets_values->get($spreadsheetId, $range2);
        $values2 = $response2->getValues();
        $consecutivo = 1;                           // Valor por defecto si no hay datos
        if (!empty($values2)) {
            $lastRow = end($values2);
            $consecutivo = intval($lastRow[0]) + 1; 
        }

        if (!file_exists($temporal)) {
            echo "El archivo temporal no existe: $temporal";
            return false; // El archivo temporal no existe
        }

        $fecha          = date('Y-m-d');
        $parentFolderId = '15LGOY2oDJ5lIGY7gTJcyxqNPbfuOOWo8'; // ID de la carpeta en Google Drive donde se creará la subcarpeta

        $client = new Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->setAuthConfig('../cotizaciones-462816-6cbb8e40c62c.json');
		$client->useApplicationDefaultCredentials();

        try {
            $service   = new Drive($client);

            $query = sprintf(
                "mimeType='application/vnd.google-apps.folder' and name='%s' and '%s' in parents and trashed=false",
                $fecha. '-' . $proyecto. '-CONSECUTIVO #' .$consecutivo,
                $parentFolderId
            );

            $folders = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);
   
            if (count($folders->getFiles()) > 0) {
                // Carpeta ya existe
                $folderId = $folders->getFiles()[0]->getId();

                $file_path = $temporal;
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file_path);

                $file = new DriveFile();
                $file->setName($nombreArchivo);
                $file->setParents([$folderId]); // Usar el ID de la subcarpeta creada
                $file->setDescription('Archivo subido desde PHP - APP Cotizaciones');
                $file->setMimeType($mimeType);

                $result = $service->files->create(
                    $file,
                    array(
                        'data' => file_get_contents($file_path),
                        'mimeType' => $mimeType,
                        'uploadType' => 'multipart',
                        'fields' => 'id'
                    )
                );

                $datosFinales = [];
                $url        = 'https://drive.google.com/drive/folders/' . $folderId;
                $urlArchivo = 'https://drive.google.com/file/d/' . $result->id . '/view?usp=sharing';
                $datosFinales['url_carpeta'] = $url;
                $datosFinales['url_archivo'] = $urlArchivo;

                if ($result) {
                    return $datosFinales;
                } else {
                    return false;
                }

            } else {
                
                // Crear la subcarpeta
                $folderMetadata = new DriveFile([
                    'name' => $fecha. '-' . $proyecto. '-CONSECUTIVO #' .$consecutivo,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$parentFolderId]
                ]);

                $folder = $service->files->create($folderMetadata, [
                    'fields' => 'id'
                ]);

                $folderId = $folder->id;
            }
            
            $folderId  = $folder->id;
            $file_path = $temporal;

            $file = new DriveFile();
            $file->setName($nombreArchivo);

            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file_path);

            $file->setParents([$folderId]); // Usar el ID de la subcarpeta creada
            $file->setDescription('Archivo subido desde PHP - APP Cotizaciones');
            $file->setMimeType($mimeType);

            $result = $service->files->create(
                $file,
                array(
                    'data' => file_get_contents($file_path),
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id'
                )
            );

            $datosFinales = [];

            /* url de la carpeta y del archivo */
            $url        = 'https://drive.google.com/drive/folders/' . $folderId;
            $urlArchivo = 'https://drive.google.com/file/d/' . $result->id . '/view?usp=sharing';
            
            $datosFinales['url_carpeta'] = $url;        // ID del archivo subido
            $datosFinales['url_archivo'] = $urlArchivo; // URL del archivo subido

            if ($result) {
                return $datosFinales; 
            } else {
                return false; 
            }
            
        } catch (Exception $e) {
            echo 'Error al subir el archivo a Google Drive: ' . $e->getMessage();
            return false; 
        }
    }

    /* Consultar proyectos */
    public function consultarProyectos()
    {
        $client = new Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
        $client->setAuthConfig('../cotizaciones-462816-324b5de0d7a7.json');
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = '1YlBwBQEFVwJHwH5ziNzudjQ-8wPCZz7F1FOntJIIoa0'; // ID de tu hoja de cálculo
        $range = 'Proyectos!A2:A'; // Rango donde se encuentran los proyectos
        
        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                return []; 
            } else {
                return $values; 
            }

        } catch (Exception $e) {
            echo 'Error al consultar los proyectos: ' . $e->getMessage();
            return []; 
        }
    }

    /* Consultar jefes que aprueban */
    public function consultarJefeAprueba()
    {
        $client = new Google_Client();
        $client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
        $client->setAuthConfig('../cotizaciones-462816-324b5de0d7a7.json');
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = '1YlBwBQEFVwJHwH5ziNzudjQ-8wPCZz7F1FOntJIIoa0'; // ID de tu hoja de cálculo
        $range = 'Jefes que autorizan!A2:A'; // Rango donde se encuentran los jefes
        
        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                return []; 
            } else {
                return $values; 
            }

        } catch (Exception $e) {
            echo 'Error al consultar los jefes: ' . $e->getMessage();
            return []; 
        }
    }

}
?>