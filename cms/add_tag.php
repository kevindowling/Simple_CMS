<?php
if ($argc < 2) {
    die("Usage: php add_tag.php <tag_name>\n");
}

$tag_name = $argv[1];

$dsn = 'sqlite:cms.db';

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
    $stmt->execute([$tag_name]);

    echo "Tag '$tag_name' added successfully.\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
