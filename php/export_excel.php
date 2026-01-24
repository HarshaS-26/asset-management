<?php
include "db.php";

/* ---------- SEARCH (SAME AS assets.php) ---------- */
$search = $_GET['search'] ?? '';
$searchSql = "";

if ($search !== '') {
    $safe = mysqli_real_escape_string($conn, $search);
    $searchSql = " AND (
        at.asset_type_name LIKE '%$safe%' OR
        l.location_name LIKE '%$safe%' OR
        a.host_name LIKE '%$safe%' OR
        a.serial_number LIKE '%$safe%' OR
        a.instrument_id LIKE '%$safe%' OR
        a.contact_number LIKE '%$safe%' OR
        a.company_name LIKE '%$safe%'
    )";
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="assets_export.csv"');

echo "\xEF\xBB\xBF"; // IMPORTANT

$output = fopen('php://output', 'w');

function nv($value) {
    return ($value === null || $value === '') ? '' : $value;
}

fputcsv($output, [
    'Sl No',
    'Asset Type',
    'Location',
    'Instrument ID',
    'Host',
    'Make',
    'Model',
    'Serial Number',
    'Install Date',
    'Warranty',
    'PO No',
    'Vendor',
    'Contact',
    'Remarks'
]);

$sql = "
SELECT a.*, at.asset_type_name, l.location_name
FROM asset a
JOIN asset_type at ON a.asset_type_id = at.id
JOIN location l ON a.location_id = l.id
WHERE 1=1 $searchSql
ORDER BY a.id ASC
";

$result = mysqli_query($conn, $sql);

$sl = 1;
while ($row = mysqli_fetch_assoc($result)) {
    
    $installDate = $row['installation_date']
    ? date('d-m-Y', strtotime($row['installation_date']))
    : '';
    
    fputcsv($output, [
        $sl++,
        nv($row['asset_type_name']),
        nv($row['location_name']),
        nv($row['instrument_id']),
        nv($row['host_name']),
        nv($row['make']),
        nv($row['model']),
        nv($row['serial_number']),
        $installDate,
        nv($row['warranty_period']),
        nv($row['po_number']),
        nv($row['company_name']),
        nv($row['contact_number']),
        nv($row['remarks'])
    ]);
}

fclose($output);
exit;
