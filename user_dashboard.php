<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
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

$user_id = $_SESSION['user']['id'] ?? null;
$user_email = $_SESSION['user']['email'] ?? null;

if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

$user_fullname = $_SESSION['user']['fullname']; // Add this line

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

$user_fullname = $user_data['fullname'] ?? '';

$stmt2 = $conn->prepare("SELECT * FROM inquiries WHERE user_name = ? ORDER BY created_at DESC");
$stmt2->bind_param("s", $user_fullname);
$stmt2->execute();
$inquiries = $stmt2->get_result();
$stmt2->close();

$stmt3 = $conn->prepare("SELECT * FROM appointments WHERE user_name = ? ORDER BY date DESC");
$stmt3->bind_param("s", $user_fullname);
$stmt3->execute();
$appointments = $stmt3->get_result();
$stmt3->close();

// Card counts
$totalAppointments = $appointments->num_rows;
$totalInquiries = $inquiries->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Dashboard - GreenLife</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #1b4d20;
            color: white;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
            color: #ffffff;
        }
        .sidebar a {
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: #14532d;
        }
        .logout {
            margin-top: auto;
            padding: 15px 25px;
            background: #b71c1c;
            text-align: center;
        }
        .logout:hover {
            background: #7f1212;
        }
        .content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }
        h2 {
            color: #2e7d32;
        }
        .cards {
            display: flex;
            gap: 20px;
            margin: 20px 0 40px;
        }
        .card {
            background: #e8f5e9;
            padding: 20px;
            flex: 1;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0 0 10px;
            color: #1b4d20;
        }
        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #2e7d32;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        .status-cancelled { color: red; font-weight: bold; }
        .status-other { color: green; }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover { background-color: #27672c; }
    </style>
</head>
<body>

<div class="sidebar">
    <h1>User Panel</h1>
    <a href="#dashboard">Dashboard</a>
    <a href="#profile">Profile</a>
    <a href="#inquiries">Inquiries</a>
    <a href="#appointments">Appointments</a>
    <a href="php/logout.php" class="logout">Logout</a>
</div>

<div class="content">
<?php
$user_fullname = $_SESSION['user']['fullname'] ?? 'User';
?>
<h2>Welcome, <?= htmlspecialchars($user_fullname) ?>!</h2>




    <div class="cards">
        <div class="card">
            <h3>Total Appointments</h3>
            <p><?= $totalAppointments ?></p>
        </div>
        <div class="card">
            <h3>Total Inquiries</h3>
            <p><?= $totalInquiries ?></p>
        </div>
    </div>

    <div class="section" id="profile">
        <h2>Your Profile</h2>
        <form action="php/update_profile.php" method="POST">
            <label>Full Name</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user_data['fullname'] ?? '') ?>" required />
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required />
            <label>Phone</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" />
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" />
            <button type="submit">Update Profile</button>
        </form>
    </div>

    <div class="section" id="inquiries">
        <h2>Your Inquiries</h2>
        <form action="php/submit_inquiry.php" method="POST">
            <textarea name="message" placeholder="Write your inquiry here..." required></textarea>
            <button type="submit">Submit Inquiry</button>
        </form>
        <table>
            <tr><th>Message</th><th>Response</th></tr>
            <?php while ($inq = $inquiries->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($inq['message']) ?></td>
                <td><?= $inq['response'] ? htmlspecialchars($inq['response']) : "<em>Pending</em>" ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section" id="appointments">
        <h2>Your Appointments</h2>
        <form action="php/submit_appointment.php" method="POST">
            <label>Service</label>
            <input type="text" name="service" placeholder="Service Name" required />
            <label>Date</label>
            <input type="date" name="date" required />
            <label>Time</label>
            <input type="time" name="time" required />
            <button type="submit">Book Appointment</button>
        </form>

        <table>
            <tr><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr>
            <?php while ($appt = $appointments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($appt['service']) ?></td>
                <td><?= htmlspecialchars($appt['date']) ?></td>
                <td><?= htmlspecialchars($appt['time']) ?></td>
                <td>
                    <?php if (($appt['status'] ?? '') === 'cancelled'): ?>
                        <span class="status-cancelled">Cancelled by admin</span>
                    <?php else: ?>
                        <span class="status-other"><?= htmlspecialchars(ucfirst($appt['status'] ?? 'pending')) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>