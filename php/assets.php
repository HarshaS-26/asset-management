<?php
include "db.php";
include "includes/header.php";

/* ---------- DATA ---------- */
$sql = "
SELECT
    a.*,
    at.asset_type_name,
    l.location_name
FROM asset a
JOIN asset_type at ON a.asset_type_id = at.id
JOIN location l ON a.location_id = l.id
ORDER BY a.id DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Asset List</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
html, body {
    height: 100%;
    margin: 0;
    overflow: hidden; /* no page scroll */
}

.page-wrapper {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.action-bar {
    flex-shrink: 0;
}

.table-container {
    flex: 1;
    overflow: hidden;
}

.dataTables_wrapper {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.dataTables_scroll {
    flex: 1;
}

.dataTables_scrollBody {
    overflow-y: auto !important;
}

th {
    white-space: nowrap;
}
</style>
</head>

<body>

<div class="page-wrapper">
    <div class="container-fluid px-4 mt-3">

        <!-- ACTION BAR -->
        <div class="action-bar d-flex justify-content-between mb-2">
            <div>
                <a href="asset-form.php" class="btn btn-primary">➕ Add New</a>
<a href="export_excel.php" class="btn btn-success ms-2">⬇ Export Excel</a>
<form method="post"
      action="import_assets.php"
      enctype="multipart/form-data"
      class="d-inline">

    <label class="btn btn-outline-primary mb-0">
        ⬆ Import CSV
        <input type="file"
               name="excel"
               accept=".csv"
               hidden
               onchange="this.form.submit()">
    </label>

</form>

            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container bg-white shadow-sm rounded">
            <table id="assetTable"
                   class="table table-bordered table-striped align-middle w-100">
        <thead class="table-dark">
        <tr>
            <th>Sl</th>
            <th>Asset Type</th>
            <th>Location</th>
            <th>Instrument ID</th>
            <th>Host</th>
            <th>Make</th>
            <th>Model</th>
            <th>Serial</th>
            <th>Install Date</th>
            <th>Warranty</th>
            <th>PO No</th>
            <th>Vendor</th>
            <th>Contact</th>
            <th>Remarks</th>
            <th>PO Doc</th>
            <th>Warranty Doc</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $sl = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?= $sl++ ?></td>
                <td><?= htmlspecialchars($row['asset_type_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['location_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['instrument_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['host_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['make'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['model'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['serial_number'] ?? '') ?></td>

                <td class="text-center">
                    <?= $row['installation_date']
                        ? date('d-m-Y', strtotime($row['installation_date']))
                        : '' ?>
                </td>

                <td><?= htmlspecialchars($row['warranty_period'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['po_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['company_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['contact_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>

                <td class="text-center">
                    <?php if (!empty($row['document_path'])) { ?>
                        <a href="<?= htmlspecialchars($row['document_path']) ?>"
                           target="_blank"
                           class="btn btn-sm btn-info">View</a>
                    <?php } else echo ''; ?>
                </td>

                <td class="text-center">
                    <?php if (!empty($row['warranty_document_path'])) { ?>
                        <a href="<?= htmlspecialchars($row['warranty_document_path']) ?>"
                           target="_blank"
                           class="btn btn-sm btn-info">View</a>
                    <?php } else echo ''; ?>
                </td>

                <td class="text-nowrap text-center">
                    <a href="asset-form.php?id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this asset?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>

</div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#assetTable').DataTable({
        scrollY: 'calc(100vh - 270px)', 
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        pageLength: 25,
        lengthMenu: [25, 50, 100],
        fixedHeader: true,
        autoWidth: false,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [14, 15, 16] }
        ]
    });
});

</script>

</body>
</html>

<?php include "includes/footer.php"; ?>
