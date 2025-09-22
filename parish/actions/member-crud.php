<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isParishLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$parish_id = $_SESSION['parish_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle member deletion
if ($action == 'delete' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ? AND parish_id = ?");
    $stmt->bind_param("ii", $id, $parish_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Member deleted successfully';
    } else {
        $_SESSION['error'] = 'Error deleting member';
    }
    
    header("Location: ../members.php");
    exit();
}

// Handle edit action
if ($action == 'edit' && $id > 0) {
    header("Location: ../member_form.php?action=edit&id=".$id);
    exit();
}

// Handle add action
if ($action == 'add') {
    header("Location: ../member_form.php?action=add");
    exit();
}

// Default redirect
header("Location: ../members.php");
exit();
?>