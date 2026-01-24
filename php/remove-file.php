<?php
include "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ---------- VALIDATE INPUT ---------- */
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? '';

if ($id <= 0 || !in_array($type, ['po', 'warranty'])) {
    header("Location: assets.php");
    exit();
}

/* ---------- FETCH FILE INFO ---------- */
$stmt = $conn->prepare("
    SELECT
        document_name,
        document_path,
        warranty_document_name,
        warranty_document_path
    FROM asset
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: assets.php");
    exit();
}

/* ---------- DETERMINE FILE ---------- */
if ($type === 'po') {
    $filePath = $data['document_path'];
    $updateSql = "
        UPDATE asset
        SET document_name = NULL,
            document_path = NULL
        WHERE id = ?
    ";
} else { // warranty
    $filePath = $data['warranty_document_path'];
    $updateSql = "
        UPDATE asset
        SET warranty_document_name = NULL,
            warranty_document_path = NULL
        WHERE id = ?
    ";
}

/* ---------- DELETE FILE FROM DISK ---------- */
if (!empty($filePath) && file_exists($filePath)) {
    unlink($filePath);
}

/* ---------- UPDATE DATABASE ---------- */
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("i", $id);
$updateStmt->execute();
$updateStmt->close();

/* ---------- REDIRECT BACK TO EDIT ---------- */
header("Location: asset-form.php?id=$id");
exit();
