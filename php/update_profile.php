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

$user = $_SESSION['user'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];

if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET email=?, phone=?, password=? WHERE fullname=?");
    $stmt->bind_param("ssss", $email, $phone, $hashed, $user);
} else {
    $stmt = $conn->prepare("UPDATE users SET email=?, phone=? WHERE fullname=?");
    $stmt->bind_param("sss", $email, $phone, $user);
}
$stmt->execute();

header("Location: ..user_dashboard.php");
