<?php
require 'vendor/autoload.php';
session_start();

// Increase script execution time limit
set_time_limit(15);
$base_url = getenv("CMS_BASE_URL");
$app_name = getenv("CMS_APPNAME");
$admin_email = getenv("CMS_ADMIN_EMAIL");

// Database connection
$dsn = 'sqlite:cms.db';
try {
    error_log("Connecting to database...");
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    exit;
}

// Generate a unique token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Get user details from the form submission
$userName = $_POST['name'];
$userEmail = $_POST['email'];
$token = generateToken();

try {
    // Store the token in the database with the user's email
    $stmt = $conn->prepare('INSERT INTO user_tokens (email, token, created_at) VALUES (:email, :token, datetime("now"))');
    $stmt->execute(['email' => $userEmail, 'token' => $token]);
    error_log("Token stored successfully for $userEmail");
} catch (PDOException $e) {
    error_log("Error storing token: " . $e->getMessage());
    exit;
}

// Create the registration link
$registrationLink = "http://$base_url/cms/send-registration-email.php?token=$token";

// Email content
$emailContent = "<p>User <strong>$userName</strong> with email <strong>$userEmail</strong> has requested to register.</p><p>Click the following link to approve the registration: <a href=\"$registrationLink\">Approve Registration</a></p>";

error_log("Email Content: " . $emailContent);

// Send the email using the send email script
function sendEmail($email, $content) {
    $base_url = getenv("CMS_BASE_URL");
    $url = "http://$base_url/cms/send-email.php"; // Correct URL to send-email.php
    $postData = [
        'email' => $email,
        'content' => $content,
    ];

    error_log("Post Data: " . print_r($postData, true));

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    error_log("Curl initialized");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    error_log("return transfer set");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    error_log("post fields");
    $startTime = microtime(true); // Start time
    error_log("Started timer");
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    error_log("Curl executed");
    $endTime = microtime(true); // End time
    $executionTime = $endTime - $startTime; // Calculate execution time
    error_log('cURL execution time: ' . $executionTime . ' seconds');

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        error_log('cURL error: ' . $error_msg);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    error_log('cURL HTTP response code: ' . $httpCode);

    if ($httpCode !== 200) {
        error_log('cURL response code indicates failure: ' . $httpCode);
        return false;
    }

    curl_close($ch);

    error_log('cURL response: ' . $response);
    return $response;
}

// Send the registration request email
if (sendEmail($admin_email, $emailContent)) {
    error_log('Registration request has been sent.');
} else {
    error_log('Failed to send registration request.');
}

error_log('Script execution completed.');
