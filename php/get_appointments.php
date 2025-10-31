<?php
session_start();
header('Content-Type: application/json');
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "wellness_center";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role === 'admin') {
    $query = "SELECT b.*, u.fullname AS user_name, t.fullname AS therapist_name
              FROM bookings b
              LEFT JOIN users u ON b.user_id = u.id
              LEFT JOIN therapists t ON b.therapist_id = t.id";
    $stmt = $conn->prepare($query);
} elseif ($role === 'therapist') {
    $query = "SELECT b.*, u.fullname AS user_name
              FROM bookings b
              LEFT JOIN users u ON b.user_id = u.id
              WHERE b.therapist_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
} else {
    $query = "SELECT b.*, t.fullname AS therapist_name
              FROM bookings b
              LEFT JOIN therapists t ON b.therapist_id = t.id
              WHERE b.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
?>
