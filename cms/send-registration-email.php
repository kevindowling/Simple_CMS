<?php
require 'vendor/autoload.php';
session_start();

// Database connection
$dsn = 'sqlite:cms.db';
$base_url = getenv("CMS_BASE_URL");
$app_name = getenv("CMS_APPNAME");
try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    exit;
}

if (!isset($_GET['token'])) {
    error_log('Invalid request');
    exit;
}

$token = $_GET['token'];

// Validate the token
$stmt = $pdo->prepare('SELECT * FROM user_tokens WHERE token = :token AND used = 0');
$stmt->execute(['token' => $token]);
$userToken = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userToken) {
    error_log('Invalid or expired token');
    exit;
}

// Mark the token as used
$stmt = $pdo->prepare('UPDATE user_tokens SET used = 1 WHERE token = :token');
$stmt->execute(['token' => $token]);

// Generate a one-time key for the new user registration
$oneTimeKey = bin2hex(random_bytes(16));

// Store the one-time key in the database
$stmt = $pdo->prepare('INSERT INTO one_time_keys (key, created_at) VALUES (:key, datetime("now"))');
$stmt->execute(['key' => $oneTimeKey]);

// Get the new user's email
$email = $userToken['email']; // Use the email from the user_tokens table

// Email content
$registrationLink = "http://$base_url/cms/register.php?token=$oneTimeKey";
$emailContent = "<p>Here is your one-time key for registering with the $app_name CMS:<p>Click the following link to register: <a href=\"$registrationLink\">Register</a></p>";

// Send the email using the send email script
function sendEmail($email, $content) {
    $base_url = getenv("CMS_BASE_URL");
    $url = "http://$base_url/cms/send-email.php"; // Update with the actual path to your send-email script
    $postData = [
        'email' => $email,
        'content' => $content,
    ];

    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
    }
    curl_close($ch);

    if (isset($error_msg)) {
        error_log('Error sending email: ' . $error_msg);
        return false;
    }

    return $response;
}

// Send the registration email
if (sendEmail($email, $emailContent)) {
    error_log('Registration email has been sent to the new user.');
} else {
    error_log('Failed to send the registration email. Please try again later.');
}
