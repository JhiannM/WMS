<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Correos {

  public function nuevaCotizacion($consecutivo) 
  {  
    require_once '../../PHPMailer/Exception.php';
    require_once '../../PHPMailer/PHPMailer.php';
    require_once '../../PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);

    try {
      // Configuración del servidor SMTP
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com'; 
      $mail->SMTPAuth   = true;
      $mail->Username   = 'cvega@contactamos.com.co';
      $mail->Password   = 'rztrvmqrhhthfwfp';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;       
      $mail->setFrom('cvega@contactamos.com.co', 'Sistema de Cotizacion - Contactamos'); 
      $mail->addAddress('cvega@contactamos.com.co'); 
      $mail->Subject = 'Nueva cotizacion registrada, consecutivo #' . $consecutivo;

      // Configuración del cuerpo del correo
      $mail->isHTML(true);
      $mail->Body = "
        <div style='font-family: Arial, sans-serif;'>
          <p style='color: #000'> Se ha registrado una nueva cotización con el número de consecutivo <b>$consecutivo</b>.</p>
          <br>
          <b>
            Área de Compras
          </b><br>
          <b>
            CONTACTAMOS OUTSOURCING S.A.S.
          </b>
        </div>
      ";

      $mail->send();
      // Respuesta exitosa
      return ['success' => true, 'message' => 'Correo enviado'];

    } catch (Exception $e) {
      // Manejo de errores
      error_log('Error al enviar el correo: ' . $mail->ErrorInfo);
      return ['success' => false, 'message' => 'Error al enviar el email: ' . $mail->ErrorInfo];
    }
    
  }
  
  public function cotizacionyArticuloNuevos($articulo, $consecutivo) 
  {  

    require_once '../../PHPMailer/Exception.php';
    require_once '../../PHPMailer/PHPMailer.php';
    require_once '../../PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    
    try {

      // Configuración del servidor SMTP
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
      $mail->SMTPAuth   = true;
      $mail->Username   = 'cvega@contactamos.com.co';
      $mail->Password   = 'rztrvmqrhhthfwfp'; 
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587; // Puerto SMTP
      $mail->setFrom('cvega@contactamos.com.co', 'Sistema de Cotizacion - Contactamos'); 
      $mail->addAddress('cvega@contactamos.com.co');            // Correo del destinatario
      $mail->Subject = 'Nueva cotizacion registrada, consecutivo #' . $consecutivo;
      $mail->isHTML(true);
      $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px;'>
          <p style='color: #000'> 
            Se ha registrado una nueva cotización con el número de consecutivo 
            <b>$consecutivo</b>, en el cual se ha solicitado un artículo que 
            no está registrado en el maestro. Por favor, registrarlo para futuras 
            cotizaciones.
          </p>
          <p><b>$articulo</b></p><br>
          <b>
            Área de Compras
          </b><br>
          <b>
            CONTACTAMOS OUTSOURCING S.A.S.
          </b>
        </div>
      ";
      $mail->send();
      // Respuesta exitosa
      return  ['success:' => true, 'message:' => 'Correo enviado' ];
  
    } catch (Exception $e) {
      // Manejo de errores
      return  ['success:' => false, 'message:' => 'Error al enviar el email: ' . $mail->ErrorInfo ];
    }
  }
  
  public function cotizacionConfirmacion($consecutivo, $correo, $nombre) 
  {
    require_once '../../PHPMailer/Exception.php';
    require_once '../../PHPMailer/PHPMailer.php';
    require_once '../../PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    
    try {

      // Configuración del servidor SMTP
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
      $mail->SMTPAuth   = true;
      $mail->Username   = 'cvega@contactamos.com.co';
      $mail->Password   = 'rztrvmqrhhthfwfp'; 
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587; 
      $mail->setFrom('cvega@contactamos.com.co', 'Sistema de Cotizacion - Contactamos'); 
      $mail->addAddress($correo); 
      $mail->Subject = 'Cotizacion registrada, consecutivo #' . $consecutivo;
      $mail->isHTML(true);
      $mail->Body = "
        <div style='font-family: Arial, sans-serif;'>
          <p> 
            Cordial saludo.
          </p>
          <p>
            Estimado(a), $nombre.
          </p><br>
          <p style='color: #000'> Su cotización ha sido guardada exitosamente con el número de consecutivo <b>$consecutivo</b>.</p>
          <p>
            Cualquier inquietud, por favor, contactar al siguiente número: xxxxxxxxxx.
          </p><br>
          <b>
            Área de Compras
          </b><br>
          <b>
            CONTACTAMOS OUTSOURCING S.A.S.
          </b>
        </div>
      ";
      $mail->send();
      // Respuesta exitosa
      return  ['success:' => true, 'message:' => 'Correo enviado' ];
  
    } catch (Exception $e) {
      // Manejo de errores
      return  ['success:' => false, 'message:' => 'Error al enviar el email: ' . $mail->ErrorInfo ];
    }
    
  }

}

