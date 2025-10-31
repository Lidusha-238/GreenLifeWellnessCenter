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

if (!isset($_SESSION['user']['id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user']['id'];
$user_email = $_SESSION['user']['email'] ?? '';

$message = $_POST['message'] ?? '';

if (empty(trim($message))) {
    die("Message is required.");
}

// Get user's full name
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$user_name = $user['fullname'] ?? 'User';

// Prepare insert statement
$stmt = $conn->prepare("INSERT INTO inquiries (user_name, message, created_at) VALUES (?, ?, NOW())");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ss", $user_name, $message);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: ../user_dashboard.php");
    exit();
} else {
    die("Execute failed: " . $stmt->error);
}
?>
