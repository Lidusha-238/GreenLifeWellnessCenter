<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "wellness_center";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$date = $_POST['date'];
$time = $_POST['time'];

$stmt = $conn->prepare("UPDATE appointments SET date=?, time=? WHERE id=?");
$stmt->bind_param("ssi", $date, $time, $id);
$stmt->execute();
header("Location: ../admin_dashboard.php");
