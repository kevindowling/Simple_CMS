<?php
$dsn = 'sqlite:cms.db';

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the token from the form
        $token = $_POST['token'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Check if the token is valid
        $stmt = $conn->prepare('SELECT * FROM one_time_keys WHERE key = :key AND used = 0');
        $stmt->execute(['key' => $token]);
        $tokenData = $stmt->fetch();

        if ($tokenData) {
            // Insert the new user
            $stmt = $conn->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
            $stmt->execute(['username' => $username, 'password' => $password]);

            // Mark the token as used
            $stmt = $conn->prepare('UPDATE one_time_keys SET used = 1 WHERE key = :key');
            $stmt->execute(['key' => $token]);

            error_log("Registration successful for user: $username");
            echo "Registration successful!";
        } else {
            error_log("Invalid or expired token used: $token");
            echo "Invalid or expired token.";
        }
    } else {
        // Get the token from the URL
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            error_log('No token provided in URL');
            die('No token provided.');
        }
        $token = $_GET['token'];
    }
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form action="register.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
