<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    error_log('User isnt logged in');
    header("Location: login.php");
    exit;
}else{
    error_log($_SESSION['user_id']);
}

$dsn = 'sqlite:cms.db';
error_log(getenv("CMS_EMAIL"));
try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetching available tags
    $tags = $conn->query("SELECT * FROM tags")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tag_id = $_POST['tag_id'];
    $user_id = $_SESSION['user_id'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, image, tag, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $image, $tag_id, $user_id]);
    } else {
        echo "Failed to upload image.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <form method="POST" action="dashboard.php" enctype="multipart/form-data">
        Title: <input type="text" name="title" required><br>
        Content: <textarea name="content" required></textarea><br>
        Image: <input type="file" name="image" required><br>
        Tag: 
        <select name="tag_id" required>
            <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit">Create Post</button>
    </form>
</body>
</html>
