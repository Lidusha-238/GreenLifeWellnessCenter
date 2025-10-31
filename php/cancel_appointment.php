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


$id = $_POST['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Appointment cancelled.";
        } else {
            $_SESSION['error'] = "Error cancelling appointment: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "SQL error: " . $conn->error;
    }
}

$conn->close();
header("Location: ../admin_dashboard.php");
exit();

