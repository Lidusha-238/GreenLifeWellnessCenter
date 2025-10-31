<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "wellness_center";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Get session values safely
$user_fullname = $_SESSION['user']['fullname'] ?? 'Unknown';
$service = $_POST['service'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

if (empty($service) || empty($date) || empty($time)) {
    die("All fields are required.");
}

// Insert appointment
$stmt = $conn->prepare("INSERT INTO appointments (user_name, service, date, time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $user_fullname, $service, $date, $time);
$stmt->execute();
$stmt->close();

header("Location: ../user_dashboard.php");
exit();
?>
