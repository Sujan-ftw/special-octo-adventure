<?php
// delete.php (MODIFIED - Security Check Added)
session_start();
require_once 'utils.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die("Access Denied: Only staff can delete records.");
}

$conn = connectMouDatabase();

if(!isset($_GET['id'])) {
    die("No ID specified");
}
$id=intval($_GET['id']);

$res=$conn->query("SELECT * FROM mou_files WHERE id=$id");
if($res && $res->num_rows>0){
    $record=$res->fetch_assoc();

    // Delete files
    @unlink($record['filepath']);
    if($record['image']) @unlink($record['image']);

    // Delete record
    $stmt=$conn->prepare("DELETE FROM mou_files WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
    header("Location: upload.php?msg=Record deleted");
    exit;
} else {
    die("Record not found");
}
?>