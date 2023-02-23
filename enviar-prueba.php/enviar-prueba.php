<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; 

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';;

$asunto = $_POST["asunto"];
$destinos = $_FILES["destinos"]["tmp_name"];
$mensaje = $_FILES["mensaje"]["tmp_name"];

//Validación de archivos
file_put_contents("logs.txt", "Inicio de validacion del archivo" . PHP_EOL, FILE_APPEND);
if (!isset($_FILES["destinos"]) || $_FILES["destinos"]["error"] != UPLOAD_ERR_OK ||
    $_FILES["destinos"]["type"] != "text/plain" || !is_uploaded_file($destinos)) {
    die("El archivo de destinos no es válido.");
}

if (!isset($_FILES["mensaje"]) || $_FILES["mensaje"]["error"] != UPLOAD_ERR_OK ||
    $_FILES["mensaje"]["type"] != "text/html" || !is_uploaded_file($mensaje)) {
    die("El archivo de mensaje no es válido.");
}

file_put_contents("logs.txt", "Fin  de validacion del archivo" . PHP_EOL, FILE_APPEND);


// Obtener correos electrónicos del archivo

file_put_contents("logs.txt", "Inicio de obtención de correos" . PHP_EOL, FILE_APPEND);

$emails = array();
$file = fopen($destinos, "r");
if ($file) {
    while (($line = fgets($file)) !== false) {
        $email = filter_var(trim($line), FILTER_VALIDATE_EMAIL);
        if ($email !== false) {
            $emails[] = $email;
        }
    }
    fclose($file);
}

file_put_contents("logs.txt", $destinos. PHP_EOL, FILE_APPEND);
file_put_contents("logs.txt", "Fin de obtención de correos" . PHP_EOL, FILE_APPEND);

// Verificar que se obtuvieron correos electrónicos válidos
if (count($emails) === 0) {
    die("El archivo de destinos no contiene correos electrónicos válidos.");
}

// Leer archivo de mensaje
file_put_contents("logs.txt", "Inicio lectura del body .html" . PHP_EOL, FILE_APPEND);
$body = file_get_contents($mensaje);
file_put_contents("logs.txt", "Fin lectura del body .html" . PHP_EOL, FILE_APPEND);
// Crear el cuerpo del mensaje

$body = str_replace("{mensaje}", $body, "asunto: " . $asunto . "<br>mensaje: {mensaje}<br>destinos: " . implode(", ", $emails));


$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'hernanmigliasso98@gmail.com';                     //SMTP username
    $mail->Password   = 'mrsmwbvkueehvpue';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients

    $mail->setFrom('hernanmigliasso98@gmail.com');

    foreach ($emails as $email) {
        $mail->addAddress($email);
    }

    
    

    //Content

    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $asunto;
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    

    $mail->send();
    echo '<script>
    alert("El mensaje se envio correctamente");
    window.history.go(-1);
    </script>';
 }catch (Exception $e) {
    echo "hubo un error al enviar: {$mail->ErrorInfo}";
}
?>