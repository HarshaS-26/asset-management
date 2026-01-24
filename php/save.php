<?php
include "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ---------- TIME ---------- */
date_default_timezone_set('Asia/Kolkata');
$now = date('Y-m-d H:i:s');

/* ---------- READ POST VALUES ---------- */
$asset_type_id     = $_POST['asset_type_id'];
$location_id       = $_POST['location_id'];

$host_name         = trim($_POST['host_name'] ?? '') ?: null;
$make              = trim($_POST['make'] ?? '') ?: null;
$model             = trim($_POST['model'] ?? '') ?: null;
$serial_number     = trim($_POST['serial_number'] ?? '');
$remarks           = trim($_POST['remarks'] ?? '') ?: null;

$installation_date = $_POST['installation_date'] ?: null;
$warranty_period   = $_POST['warranty_period'] ?: null;
$instrument_id     = $_POST['instrument_id'] ?: null;
$company_name      = $_POST['company_name'] ?: null;
$contact_number    = $_POST['contact_number'] ?: null;
$po_number         = $_POST['po_number'] ?: null;

/* ---------- DUPLICATE SERIAL CHECK ---------- */
if ($serial_number !== '') {
    $dupStmt = $conn->prepare(
        "SELECT id FROM asset
         WHERE serial_number = ?
           AND asset_type_id != 2"
        );
    $dupStmt->bind_param("s", $serial_number);
    $dupStmt->execute();
    $dupResult = $dupStmt->get_result();
    
    if ($dupResult->num_rows > 0) {
        header("Location: asset-form.php?error=duplicate");
        exit();
    }
    $dupStmt->close();
}

/* ---------- DUPLICATE INSTRUMENT ID ---------- */
if (!empty($instrument_id)) {
    $instStmt = $conn->prepare(
        "SELECT id FROM asset WHERE instrument_id = ?"
        );
    $instStmt->bind_param("s", $instrument_id);
    $instStmt->execute();
    $instResult = $instStmt->get_result();
    
    if ($instResult->num_rows > 0) {
        header("Location: asset-form.php?error=instrument_duplicate");
        exit();
    }
    $instStmt->close();
}

/* ---------- FILE VALIDATION FUNCTION ---------- */
function validateAndUpload($file, $uploadDir)
{
    if (empty($file['name'])) {
        return [null, null];
    }
    
    /* MIME CHECK */
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];
    
    if (!in_array($mime, $allowedTypes)) {
        header("Location: asset-form.php?error=invalid_file");
        exit();
    }
    
    if ($file['size'] > (5 * 1024 * 1024)) {
        header("Location: asset-form.php?error=file_too_large");
        exit();
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    
    move_uploaded_file($file['tmp_name'], $filePath);
    
    return [$fileName, $filePath];
}

/* ---------- UPLOAD PO DOCUMENT ---------- */
[$documentName, $documentPath] =
validateAndUpload($_FILES['document'] ?? [], "uploads/assets/");

/* ---------- UPLOAD WARRANTY DOCUMENT ---------- */
[$warrantyDocumentName, $warrantyDocumentPath] =
validateAndUpload($_FILES['warranty_document'] ?? [], "uploads/warranty/");

/* ---------- ADD NEW ASSET TYPE ---------- */
if ($asset_type_id === 'new') {
    $newAssetType = trim($_POST['new_asset_type']);
    $stmt = $conn->prepare(
        "INSERT INTO asset_type (asset_type_name) VALUES (?)"
        );
    $stmt->bind_param("s", $newAssetType);
    $stmt->execute();
    $asset_type_id = $stmt->insert_id;
    $stmt->close();
}

/* ---------- ADD NEW LOCATION ---------- */
if ($location_id === 'new') {
    $newLocation = trim($_POST['new_location']);
    $stmt = $conn->prepare(
        "INSERT INTO location (location_name) VALUES (?)"
        );
    $stmt->bind_param("s", $newLocation);
    $stmt->execute();
    $location_id = $stmt->insert_id;
    $stmt->close();
}

/* ---------- FINAL INSERT ---------- */
$insertStmt = $conn->prepare("
    INSERT INTO asset (
        asset_type_id,
        location_id,
        host_name,
        make,
        model,
        serial_number,
        remarks,
        installation_date,
        warranty_period,
        instrument_id,
        company_name,
        contact_number,
        po_number,
        document_name,
        document_path,
        warranty_document_name,
        warranty_document_path,
        created_on
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insertStmt->bind_param(
    "iissssssssssssssss",
    $asset_type_id,
    $location_id,
    $host_name,
    $make,
    $model,
    $serial_number,
    $remarks,
    $installation_date,
    $warranty_period,
    $instrument_id,
    $company_name,
    $contact_number,
    $po_number,
    $documentName,
    $documentPath,
    $warrantyDocumentName,
    $warrantyDocumentPath,
    $now
    );

if (!$insertStmt->execute()) {
    die("Insert failed: " . $insertStmt->error);
}

$insertStmt->close();

/* ---------- REDIRECT ---------- */
header("Location: assets.php");
exit();
