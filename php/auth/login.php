<?php
session_start();
include "../db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $res = $conn->query(
        "SELECT * FROM users WHERE username='$username' AND password='$password'"
    );

    if ($res->num_rows === 1) {
        $_SESSION['user'] = $username;
        header("Location: /assets.php");
        exit;
    } else {
        $error = "Invalid login credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card p-4 shadow" style="width: 350px;">
    <h5 class="mb-3 text-center">Asset Management Login</h5>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php } ?>

    <form method="post">
        <div class="mb-3">
            <label>Username</label>
            <input name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
