<?php
include "db.php";

$id = (int) $_GET['id'];

mysqli_query($conn, "DELETE FROM asset WHERE id = $id");

header("Location: assets.php");
exit();
