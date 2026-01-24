<?php
include "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');

/* ---------- BASIC VALIDATION ---------- */
if (!isset($_POST['id'])) {
    die("Invalid request");
}

$id = (int) $_POST['id'];

/* ---------- DUPLICATE SERIAL CHECK ---------- */
$serial = trim($_POST['serial_number'] ?? '');

if ($serial !== '') {
    $stmt = $conn->prepare(
        "SELECT id FROM asset
         WHERE serial_number = ?
           AND id != ?
           AND asset_type_id != 2"
        );
    $stmt->bind_param("si", $serial, $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        header("Location: asset-form.php?id=$id&error=duplicate");
        exit();
    }
    $stmt->close();
}

/* ---------- DUPLICATE INSTRUMENT ID ---------- */
$instrument_id = trim($_POST['instrument_id'] ?? '');

if ($instrument_id !== '') {
    $instStmt = $conn->prepare(
        "SELECT id FROM asset WHERE instrument_id = ? AND id != ?"
        );
    $instStmt->bind_param("si", $instrument_id, $id);
    $instStmt->execute();
    $instStmt->store_result();
    
    if ($instStmt->num_rows > 0) {
        header("Location: asset-form.php?id=$id&error=instrument_duplicate");
        exit();
    }
    $instStmt->close();
}

/* ---------- READ INPUT ---------- */
$asset_type_id = $_POST['asset_type_id'];
$location_id   = $_POST['location_id'];

$host_name     = trim($_POST['host_name'] ?? '');
$make          = trim($_POST['make'] ?? '');
$model         = trim($_POST['model'] ?? '');
$serial_number = trim($_POST['serial_number'] ?? '');
$remarks       = trim($_POST['remarks'] ?? '');

$installation_date = $_POST['installation_date'] ?: null;
$warranty_period   = trim($_POST['warranty_period'] ?? '');
$company_name      = trim($_POST['company_name'] ?? '');
$contact_number    = trim($_POST['contact_number'] ?? '');
$po_number         = trim($_POST['po_number'] ?? '');

/* ---------- FILE UPLOAD HELPER ---------- */
function uploadFile($file, $dir)
{
    if (empty($file['name'])) {
        return [null, null];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
    
    if (!in_array($mime, $allowed)) {
        header("Location: asset-form.php?id=".$_POST['id']."&error=invalid_file");
        exit();
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        header("Location: asset-form.php?id=".$_POST['id']."&error=file_too_large");
        exit();
    }
    
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $name = time() . "_" . basename($file['name']);
    $path = $dir . $name;
    
    move_uploaded_file($file['tmp_name'], $path);
    
    return [$name, $path];
}

/* ---------- FETCH EXISTING FILES ---------- */
$res = $conn->query("SELECT document_name, document_path,
                            warranty_document_name, warranty_document_path
                     FROM asset WHERE id = $id");
$existing = $res->fetch_assoc();

/* ---------- PO DOCUMENT ---------- */
$document_name = $existing['document_name'];
$document_path = $existing['document_path'];

if (!empty($_POST['remove_document'])) {
    if (!empty($document_path) && file_exists($document_path)) {
        unlink($document_path);
    }
    $document_name = null;
    $document_path = null;
}

if (!empty($_FILES['document']['name'])) {
    [$document_name, $document_path] =
    uploadFile($_FILES['document'], "uploads/assets/");
}

/* ---------- WARRANTY DOCUMENT ---------- */
$warranty_document_name = $existing['warranty_document_name'];
$warranty_document_path = $existing['warranty_document_path'];

if (!empty($_POST['remove_warranty_document'])) {
    if (!empty($warranty_document_path) && file_exists($warranty_document_path)) {
        unlink($warranty_document_path);
    }
    $warranty_document_name = null;
    $warranty_document_path = null;
}

if (!empty($_FILES['warranty_document']['name'])) {
    [$warranty_document_name, $warranty_document_path] =
    uploadFile($_FILES['warranty_document'], "uploads/warranty/");
}

/* ---------- ADD NEW ASSET TYPE ---------- */
if ($asset_type_id === 'new') {
    $stmt = $conn->prepare(
        "INSERT INTO asset_type (asset_type_name) VALUES (?)"
        );
    $stmt->bind_param("s", $_POST['new_asset_type']);
    $stmt->execute();
    $asset_type_id = $stmt->insert_id;
    $stmt->close();
}

/* ---------- ADD NEW LOCATION ---------- */
if ($location_id === 'new') {
    $stmt = $conn->prepare(
        "INSERT INTO location (location_name) VALUES (?)"
        );
    $stmt->bind_param("s", $_POST['new_location']);
    $stmt->execute();
    $location_id = $stmt->insert_id;
    $stmt->close();
}

/* ---------- FINAL UPDATE ---------- */
$sql = "
UPDATE asset SET
    asset_type_id = ?,
    location_id = ?,
    host_name = ?,
    make = ?,
    model = ?,
    serial_number = ?,
    remarks = ?,
    installation_date = ?,
    warranty_period = ?,
    instrument_id = ?,
    company_name = ?,
    contact_number = ?,
    po_number = ?,
    document_name = ?,
    document_path = ?,
    warranty_document_name = ?,
    warranty_document_path = ?
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisssssssssssssssi",
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
    $document_name,
    $document_path,
    $warranty_document_name,
    $warranty_document_path,
    $id
    );

if (!$stmt->execute()) {
    die("UPDATE FAILED: " . $stmt->error);
}

$stmt->close();

/* ---------- REDIRECT ---------- */
header("Location: assets.php");
exit();
