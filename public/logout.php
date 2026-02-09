<?php
session_start();
$isAdmin = isset($_SESSION['admin_logged_in']);
session_destroy();

if ($isAdmin) {
    header("Location: login_admin.php");
} else {
    header("Location: login_pemesan.php");
}
exit();
?>