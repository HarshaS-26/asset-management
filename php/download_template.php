<?php
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="asset_import_template.csv"');

$output = fopen('php://output', 'w');

// UTF-8 BOM (IMPORTANT for Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, [
    'Asset Type',
    'Location',
    'Instrument ID',
    'Host Name',
    'Make',
    'Model',
    'Serial Number',
    'Installation Date (YYYY-MM-DD)',
    'Warranty Period',
    'PO Number',
    'Vendor Name',
    'Contact Number',
    'Remarks'
]);

fclose($output);
exit;
