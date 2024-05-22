<?php
$dsn = 'sqlite:cms.db';

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$result = $conn->query("SELECT title, content, image, created_at FROM posts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blog Posts</title>
</head>
<body>
    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
        <h2><?php echo htmlspecialchars($row['title']); ?></h2>
        <p><?php echo htmlspecialchars($row['content']); ?></p>
        <?php if ($row['image']): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
        <?php endif; ?>
        <p><em>Posted on <?php echo htmlspecialchars($row['created_at']); ?></em></p>
        <hr>
    <?php endwhile; ?>
</body>
</html>
