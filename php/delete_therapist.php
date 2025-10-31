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
    $stmt = $conn->prepare("DELETE FROM therapists WHERE id=?");

    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Therapist deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting therapist: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "SQL error: " . $conn->error;
    }
}

$conn->close();
header("Location: ../admin_dashboard.php");
exit();
