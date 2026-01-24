<?php
include "db.php";
include "includes/header.php";

/* ---------- EDIT MODE CHECK ---------- */
$editMode = false;
$data = [];

if (isset($_GET['id'])) {
    $editMode = true;
    $id = (int) $_GET['id'];
    $res = $conn->query("SELECT * FROM asset WHERE id = $id");
    $data = $res->fetch_assoc();
}

/* ---------- DUPLICATE ERROR ---------- */
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'duplicate') {
        echo '<div class="alert alert-warning text-center">⚠️ Serial Number already exists.</div>';
    }
    if ($_GET['error'] === 'instrument_duplicate') {
        echo '<div class="alert alert-warning text-center">⚠️ Instrument ID already exists.</div>';
    }
}

/* ---------- DROPDOWNS ---------- */
$locations = $conn->query("SELECT id, location_name FROM location ORDER BY location_name");
$assets = $conn->query("SELECT id, asset_type_name FROM asset_type ORDER BY asset_type_name");
?>

<div class="card p-4 shadow">
<h5><?= $editMode ? 'Edit Asset' : 'Add Asset' ?></h5>

<form method="post"
      action="<?= $editMode ? 'update.php' : 'save.php' ?>"
      enctype="multipart/form-data">

<?php if ($editMode): ?>
<input type="hidden" name="id" value="<?= $data['id'] ?>">
<?php endif; ?>

<h6 class="mt-3">Asset Details</h6>

<!-- Location / Asset Type / Host -->
<div class="row">
    <div class="col-md-4 mb-3">
        <label>Current Location *</label>
        <select name="location_id" class="form-control"
                onchange="toggleNewLocation(this.value)" required>
            <option value="">-- Select Location --</option>
            <?php while ($row = $locations->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"
                    <?= ($editMode && $row['id'] == $data['location_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['location_name'], ENT_QUOTES) ?>
                </option>
            <?php endwhile; ?>
            <option value="new">➕ Add New Location</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>Asset Type *</label>
        <select name="asset_type_id" class="form-control"
                onchange="toggleNewAssetType(this.value)" required>
            <option value="">-- Select Asset Type --</option>
            <?php while ($row = $assets->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"
                    <?= ($editMode && $row['id'] == $data['asset_type_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['asset_type_name'], ENT_QUOTES) ?>
                </option>
            <?php endwhile; ?>
            <option value="new">➕ Add New Asset Type</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>Host Name</label>
        <input type="text" name="host_name" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['host_name'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
</div>

<!-- New Location / Asset Type -->
<div class="row">
    <div class="col-md-6 mb-3" id="newLocationDiv" style="display:none;">
        <input type="text" name="new_location" class="form-control"
               placeholder="Enter new location">
    </div>

    <div class="col-md-6 mb-3" id="newAssetTypeDiv" style="display:none;">
        <input type="text" name="new_asset_type" class="form-control"
               placeholder="Enter new asset type">
    </div>
</div>

<!-- Make / Model -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Make</label>
        <input type="text" name="make" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['make'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label>Model</label>
        <input type="text" name="model" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['model'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
</div>

<!-- Serial / Instrument -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Serial Number</label>
        <input type="text" name="serial_number" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['serial_number'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label>Instrument ID</label>
        <input type="text" name="instrument_id" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['instrument_id'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
</div>

<!-- Remarks -->
<div class="mb-3">
    <label>Remarks</label>
    <textarea name="remarks" class="form-control"><?= $editMode ? htmlspecialchars($data['remarks'] ?? '', ENT_QUOTES) : '' ?></textarea>
</div>

<hr>
<h6>Purchase & Warranty</h6>

<!-- Dates -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Date of Installation</label>
        <input type="date" name="installation_date" class="form-control"
               value="<?= ($editMode && !empty($data['installation_date']) && $data['installation_date'] !== '0000-00-00') ? $data['installation_date'] : '' ?>">
    </div>

    <div class="col-md-6 mb-3">
        <label>Warranty Period</label>
        <input type="text" name="warranty_period" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['warranty_period'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
</div>

<!-- Vendor -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Vendor</label>
        <input type="text" name="company_name" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['company_name'] ?? '', ENT_QUOTES) : '' ?>">
    </div>

    <div class="col-md-6 mb-3">
        <label>Contact Number</label>
        <input type="text" name="contact_number" maxlength="10" class="form-control"
               value="<?= $editMode ? htmlspecialchars($data['contact_number'] ?? '', ENT_QUOTES) : '' ?>">
    </div>
</div>

<!-- PO + Warranty Upload -->
<div class="row">
    <div class="col-md-6 mb-3"> <label>PO Number</label> <input type="text" name="po_number" class="form-control" 
         value="<?= $editMode && isset($data['po_number']) ? htmlspecialchars($data['po_number'] ?? '',ENT_QUOTES) : '' ?>"> 
    </div>
    <!-- PO -->
    <div class="col-md-6 mb-3">
        <label>PO / Invoice Document</label>

        <?php if ($editMode && !empty($data['document_name'])): ?>
            <div class="mb-2">
                📄 <a href="<?= htmlspecialchars($data['document_path']) ?>" target="_blank">
                    <?= htmlspecialchars($data['document_name'], ENT_QUOTES) ?>
                </a>
                |
                <a href="remove-file.php?id=<?= $data['id'] ?>&type=po"
                   class="text-danger"
                   onclick="return confirm('Remove PO document?')">Remove</a>
            </div>
        <?php endif; ?>

        <input type="file" name="document" id="poFile"
               class="form-control"
               accept=".pdf,.jpg,.jpeg,.png"
               onchange="previewFile(this,'poPreview')">

        <div id="poPreview" style="display:none;" class="mt-2">
            📄 <span></span> |
            <a href="#" onclick="viewSelectedFile('poFile')">View</a> |
            <a href="#" class="text-danger"
               onclick="removeSelectedFile('poFile','poPreview')">Remove</a>
        </div>
    </div>
    </div>
<div class="row">
    <!-- Warranty -->
    <div class="col-md-6 mb-3">
        <label>Warranty Document</label>

        <?php if ($editMode && !empty($data['warranty_document_name'])): ?>
            <div class="mb-2">
                📄 <a href="<?= htmlspecialchars($data['warranty_document_path']) ?>" target="_blank">
                    <?= htmlspecialchars($data['warranty_document_name'], ENT_QUOTES) ?>
                </a>
                |
                <a href="remove-file.php?id=<?= $data['id'] ?>&type=warranty"
                   class="text-danger"
                   onclick="return confirm('Remove warranty document?')">Remove</a>
            </div>
        <?php endif; ?>

        <input type="file" name="warranty_document" id="warrantyFile"
               class="form-control"
               accept=".pdf,.jpg,.jpeg,.png"
               onchange="previewFile(this,'warrantyPreview')">

        <div id="warrantyPreview" style="display:none;" class="mt-2">
            📄 <span></span> |
            <a href="#" onclick="viewSelectedFile('warrantyFile')">View</a> |
            <a href="#" class="text-danger"
               onclick="removeSelectedFile('warrantyFile','warrantyPreview')">Remove</a>
        </div>
    </div>
</div>

<!-- Buttons -->
<div class="d-flex justify-content-between mt-4">
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Save</button>
        <a href="assets.php" class="btn btn-secondary">Cancel</a>
    </div>
    <a href="assets.php" class="btn btn-outline-dark">View List</a>
</div>

</form>
</div>

<script>
function toggleNewLocation(v){document.getElementById('newLocationDiv').style.display=v==='new'?'block':'none'}
function toggleNewAssetType(v){document.getElementById('newAssetTypeDiv').style.display=v==='new'?'block':'none'}

function previewFile(input, previewId){
    if(!input.files.length) return;
    const p=document.getElementById(previewId);
    p.style.display='block';
    p.querySelector('span').textContent=input.files[0].name;
}
function removeSelectedFile(inputId, previewId){
    document.getElementById(inputId).value='';
    document.getElementById(previewId).style.display='none';
}
function viewSelectedFile(inputId){
    const f=document.getElementById(inputId).files[0];
    if(f) window.open(URL.createObjectURL(f),'_blank');
}
</script>

<?php include "includes/footer.php"; ?>
