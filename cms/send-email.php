<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP; // Ensure the SMTP class is autoloaded

require 'vendor/phpmailer/phpmailer/src/SMTP.php';

error_log("Send email script entered");
$email_address = getenv("CMS_EMAIL");
$email_password = getenv("CMS_EMAIL_PW");

$app_name = getenv("CMS_APPNAME");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Received POST data: " . print_r($_POST, true));

    $email = $_POST['email'] ?? null;
    $content = $_POST['content'] ?? null;

    if (!$email || !$content) {
        error_log('Email or content not provided');
        echo 'Email or content not provided';
        exit;
    }

    $mail = new PHPMailer();

    // Custom debug output function
    $mail->Debugoutput = function($str, $level) {
        error_log("SMTP Debug Level $level; message: $str");
    };

    // SMTP configuration
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Set the debug level
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $email_address;
    $mail->Password = $email_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Email settings
    $mail->setFrom($email_address, $app_name);
    $mail->addAddress($email);
    $mail->Subject = 'New User Registration Request';
    $mail->isHTML(true);
    $mail->Body = $content;

    error_log("Email sending from: ". $email_address . " to: " . $email);

    if (!$mail->send()) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        error_log('Message sent to ' . $email);
        echo 'Message sent';
    }
} else {
    error_log('Invalid request method');
    echo 'Invalid request method';
}
