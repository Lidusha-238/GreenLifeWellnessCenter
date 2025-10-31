<?php
session_start([
    'cookie_lifetime' => 1800,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

header('Content-Type: application/json');

// 1. Hardcoded Admin Credentials
const ADMIN = [
    'email' => 'admin@greenlife.com',
    'password' => 'Admin@123', // Change in production
    'role' => 'admin',
    'fullname' => 'Admin'
];

// 2. Database Connection
$conn = new mysqli('localhost', 'root', '', 'greenlife_wellness', 3306);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// 3. Get Input
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

// 4. Validate Input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

try {
    // 5. Check Admin First
    if ($email === ADMIN['email'] && $password === ADMIN['password']) {
        $_SESSION['user'] = [
            'id' => 0,
            'email' => ADMIN['email'],
            'fullname' => ADMIN['fullname'],
            'role' => ADMIN['role']
        ];
        echo json_encode([
            'success' => true,
            'redirect' => 'admin_dashboard.php',
            'role' => 'admin'
        ]);
        exit;
    }

    // 6. Check Therapists Table
    $stmt = $conn->prepare("SELECT id, email, name, password FROM therapists WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $therapist = $result->fetch_assoc();
        
        if (password_verify($password, $therapist['password'])) {
            $_SESSION['user'] = [
                'id' => $therapist['id'],
                'email' => $therapist['email'],
                'fullname' => $therapist['name'], // ✅ Store therapist's name
                'role' => 'therapist'
            ];

            echo json_encode([
                'success' => true,
                'redirect' => 'therapist_dashboard.php',
                'role' => 'therapist'
            ]);
            exit;
        }
    }

    // 7. Check Users Table
    $stmt = $conn->prepare("SELECT id, email, fullname, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'fullname' => $user['fullname'], // ✅ Store user's full name
                'role' => 'user'
            ];

            // Optional: "Remember Me" cookie
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days

                $updateStmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
                $updateStmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expiry), $user['id']);
                $updateStmt->execute();

                setcookie('remember_token', $token, [
                    'expires' => $expiry,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            echo json_encode([
                'success' => true,
                'redirect' => 'user_dashboard.php',
                'role' => 'user'
            ]);
            exit;
        }
    }

    // 8. If login fails
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error']);
} finally {
    $conn->close();
}
?>
