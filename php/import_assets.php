<?php
include "db.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= FILE CHECK ================= */
if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    die("❌ No file uploaded");
}

$handle = fopen($_FILES['excel']['tmp_name'], 'r');
if (!$handle) {
    die("❌ Cannot open file");
}

/* ================= CLEAN FUNCTION ================= */
function clean($v, $conn) {
    if ($v === null) return null;
    
    $v = trim($v);
    
    if ($v === '' || strtolower($v) === 'na' || $v === '—') {
        return null;
    }
    
    $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8,ISO-8859-1,WINDOWS-1252');
    return mysqli_real_escape_string($conn, $v);
}

/* ================= READ HEADER ================= */
$headers = fgetcsv($handle, 0, ",");
if (!$headers) {
    die("❌ Empty CSV file");
}

/* ================= NORMALIZE HEADER ================= */
$normalized = [];
foreach ($headers as $h) {
    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // BOM
    $h = str_replace("\xC2\xA0", ' ', $h);
    $normalized[] = strtolower(trim($h));
}

$map = array_flip($normalized);

/* ================= REQUIRED COLUMNS ================= */
$required = ['asset type', 'location', 'serial number'];

foreach ($required as $col) {
    if (!isset($map[$col])) {
        echo "<pre>";
        print_r($normalized);
        echo "</pre>";
        die("❌ Missing column: <b>$col</b>");
    }
}

/* ================= RESET TABLE ================= */
mysqli_query($conn, "TRUNCATE TABLE asset");

/* ================= COUNTERS ================= */
$inserted = 0;
$failed   = 0;
$failures = [];
$rowNum   = 1;

/* ================= PROCESS ROWS ================= */
while (($row = fgetcsv($handle, 0, ",")) !== false) {
    $rowNum++;
    
    if (count(array_filter($row)) === 0) continue;
    
    $assetTypeName = clean($row[$map['asset type']] ?? null, $conn);
    $locationName  = clean($row[$map['location']] ?? null, $conn);
    $serial        = clean($row[$map['serial number']] ?? null, $conn);
    
    if (!$assetTypeName || !$locationName) {
        $failed++;
        $failures[] = "Row $rowNum → Asset Type / Location missing";
        continue;
    }
    
    $instrumentId = clean($row[$map['instrument id']] ?? null, $conn);
    $hostName     = clean($row[$map['host']] ?? null, $conn);
    $make         = clean($row[$map['make']] ?? null, $conn);
    $model        = clean($row[$map['model']] ?? null, $conn);
    $installDate  = clean($row[$map['install date']] ?? null, $conn);
    $warranty     = clean($row[$map['warranty']] ?? null, $conn);
    $poNumber     = clean($row[$map['po no']] ?? null, $conn);
    $vendor       = clean($row[$map['vendor']] ?? null, $conn);
    $contact      = clean($row[$map['contact']] ?? null, $conn);
    $remarks      = clean($row[$map['remarks']] ?? null, $conn);
    
    /* ================= ASSET TYPE ================= */
    $res = mysqli_query($conn,
        "SELECT id FROM asset_type WHERE asset_type_name='$assetTypeName'");
    $type = mysqli_fetch_assoc($res);
    
    if (!$type) {
        mysqli_query($conn,
            "INSERT INTO asset_type (asset_type_name) VALUES ('$assetTypeName')");
        $assetTypeId = mysqli_insert_id($conn);
    } else {
        $assetTypeId = $type['id'];
    }
    
    /* ================= LOCATION ================= */
    $res = mysqli_query($conn,
        "SELECT id FROM location WHERE location_name='$locationName'");
    $loc = mysqli_fetch_assoc($res);
    
    if (!$loc) {
        mysqli_query($conn,
            "INSERT INTO location (location_name) VALUES ('$locationName')");
        $locationId = mysqli_insert_id($conn);
    } else {
        $locationId = $loc['id'];
    }
    
    /* ================= DATE ================= */
    $installDateSQL = "NULL";
    if ($installDate && ($ts = strtotime($installDate)) !== false) {
        $installDateSQL = "'" . date('Y-m-d', $ts) . "'";
    }
    
    /* ================= INSERT ================= */
    $sql = "
        INSERT INTO asset (
            asset_type_id, location_id, instrument_id, host_name,
            make, model, serial_number, installation_date,
            warranty_period, po_number, company_name,
            contact_number, remarks, created_on
        ) VALUES (
            '$assetTypeId', '$locationId',
            " . ($instrumentId ? "'$instrumentId'" : "NULL") . ",
            " . ($hostName ? "'$hostName'" : "NULL") . ",
            " . ($make ? "'$make'" : "NULL") . ",
            " . ($model ? "'$model'" : "NULL") . ",
            " . ($serial ? "'$serial'" : "NULL") . ",
            $installDateSQL,
            " . ($warranty ? "'$warranty'" : "NULL") . ",
            " . ($poNumber ? "'$poNumber'" : "NULL") . ",
            " . ($vendor ? "'$vendor'" : "NULL") . ",
            " . ($contact ? "'$contact'" : "NULL") . ",
            " . ($remarks ? "'$remarks'" : "NULL") . ",
            NOW()
        )
    ";
            
            if (mysqli_query($conn, $sql)) {
                $inserted++;
            } else {
                $failed++;
                $failures[] = "Row $rowNum → " . mysqli_error($conn);
            }
}

fclose($handle);

/* ================= RESULT ================= */
echo "<h3>Import Completed</h3>";
echo "🟢 Inserted: $inserted<br>";
echo "🔴 Failed: $failed<br>";

if ($failed > 0) {
    echo "<h4>❌ Failed Rows</h4><ul>";
    foreach ($failures as $f) echo "<li>$f</li>";
    echo "</ul>";
}
