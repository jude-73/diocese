<?php
require 'includes/config.php';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM parishes");
    echo "Success! Found " . $stmt->fetchColumn() . " parishes";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}