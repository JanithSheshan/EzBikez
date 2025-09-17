<?php
require_once __DIR__ . '../includes/helpers.php';
if (!is_admin()) { header('Location: ' . base_url('public/login.php')); exit; }
global $mysqli;
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM bikes WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    flash_set('success','Bike deleted');
}
header('Location:' . base_url('admin/bikes.php'));
exit;
