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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string(trim($_POST['fullname']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $specialization = $conn->real_escape_string(trim($_POST['specialization']));
    $password = trim($_POST['password']);

    if (empty($fullname) || empty($email) || empty($specialization) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../admin_dashboard.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO therapists (name, email, specialization, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../admin_dashboard.php");
        exit();
    }

    $stmt->bind_param("ssss", $fullname, $email, $specialization, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Therapist added successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: ../admin_dashboard.php"); // ✅ Correct redirect
    exit();
} else {
    header("Location: ../admin_dashboard.php"); // ✅ Also correct for direct access
    exit();
}
