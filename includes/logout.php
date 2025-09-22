<?php
// includes/logout.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Perform logout
logout();

// This line should never be reached if logout works correctly
die("Logout redirection failed");
?>