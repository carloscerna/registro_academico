<?php    
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
//  variables 
$enviado = 'Enviado: ' . date("Y-m-d H:i:s") . "\n";
  $subject = "Este es el asunto del mensaje - ";
  $message = 'Este es el mensaje a enviar.';
//Load Composer's autoloader
  require "$path_root/registro_academico/vendor/autoload.php";
// Cargando la librería de PHPMailer
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
//  
  $mail = new PHPMailer(true);                              // Pasar `true` habilita excepciones
  try {
      //Configuraciones de servidor
      $mail->SMTPDebug = 2;                                 // Habilitar salida de depuración detallada
      $mail->isSMTP();                                      // Configurar el remitente para usar SMTP
      $mail->Host = 'smtp.gmail.com';                  // Especificar servidores SMTP principales y de respaldo
      $mail->SMTPAuth = true;                               // Habilitar autenticación SMTP
      $mail->Username = 'carlos.w.cerna@gmail.com';             // Nombre usuario SMTP
      $mail->Password = 'Sebastian019789132';                           // Contraseña SMTP
      $mail->SMTPSecure = 'ssl';                            // Habilitar encriptación SSL, TLS también es aceptado con el puerto 587
      $mail->Port = 465;                                    // TCP puerto para conectarse
  
      //Destinatarios
      $mail->setFrom('carlos.w.cerna@gmail.com', 'Mailer');          //Este es el correo electrónico desde el que envía su formulario
      $mail->addAddress('carlos.wilfredo77@gmail.com', 'Juan Usuario'); // Agregar una dirección de destinatario
      //$mail->addAddress('contacto@example.com');               // Nombre es opcional
      //$mail->addReplyTo('info@example.com', 'Información');
      //$mail->addCC('cc@example.com');
      //$mail->addBCC('bcc@example.com');
  
      //Archivos adjuntos
      //$mail->addAttachment('/var/tmp/archivo.tar.gz');         // Add attachments
      //$mail->addAttachment('/tmp/imagen.jpg', 'nuevo.jpg');    // Nombre opcional
  
      //Contenido
      $mail->isHTML(true);                                  // Establecer formato de correo electrónico a HTML
      $mail->Subject = 'La línea de asunto va aquí';
      $mail->Body    = 'El texto del cuerpo va aquí';
      //$mail->AltBody = 'Este es el cuerpo en texto plano para clientes de correo no HTML';
  
      $mail->send();
      echo 'El mensaje ha sido enviado';
  } catch (Exception $e) {
      echo 'El mensaje no pudo ser enviado.';
      echo 'Error de correo: ' . $mail->ErrorInfo;
  }
?>