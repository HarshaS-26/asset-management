<?php
session_start();
include __DIR__ . "/../auth/check.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Asset Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #f4f6f9, #e9eef5);
}
.app-container {
    max-width: 1100px;
}
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold">🖥️ Asset Management</span>
        <div class="text-white">
            <?= htmlspecialchars($_SESSION['user']) ?> |
            <a href="../auth/logout.php" class="text-warning text-decoration-none">Logout</a>
        </div>
    </div>
</nav>

<div class="container app-container mt-4">
