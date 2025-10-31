<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $response = trim($_POST['response']);

    // Validate
    if (empty($id) || empty($response)) {
        echo "Invalid data submitted.";
        exit();
    }

    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "wellness_center";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE inquiries SET response = ? WHERE id = ?");
    $stmt->bind_param("si", $response, $id);

    if ($stmt->execute()) {
        header("Location: ../admin_dashboard.php"); // or wherever your dashboard is
        exit();
    } else {
        echo "Failed to update response.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
