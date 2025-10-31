<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'therapist') {
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

$therapist_id = $_SESSION['user']['id'] ?? null;
$therapist_email = $_SESSION['user']['email'] ?? null;

$stmt = $conn->prepare("SELECT name FROM therapists WHERE id = ?");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$result = $stmt->get_result();
$therapist = $result->fetch_assoc();
$stmt->close();

$therapistName = $therapist['name'] ?? 'Therapist';

$appointments = $conn->query("SELECT * FROM appointments ORDER BY date DESC");
$clients = $conn->query("SELECT fullname, email, phone FROM users WHERE role = 'user'");
$inquiries = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");

$totalAppointments = $appointments->num_rows;
$totalClients = $clients->num_rows;
$totalInquiries = $inquiries->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Therapist Dashboard - GreenLife</title>
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
        }
        .sidebar h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .sidebar a {
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-weight: 500;
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
        }
        input[type="text"], input[type="date"], input[type="time"] {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-left: 5px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h1>Therapist Panel</h1>
    <a href="#dashboard">Dashboard</a>
    <a href="#appointments">Appointments</a>
    <a href="#clients">Clients</a>
    <a href="#inquiries">Inquiries</a>
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
            <h3>Total Clients</h3>
            <p><?= $totalClients ?></p>
        </div>
        <div class="card">
            <h3>Total Inquiries</h3>
            <p><?= $totalInquiries ?></p>
        </div>
    </div>

    <div class="section" id="appointments">
        <h2>Manage Appointments</h2>
        <table>
            <tr><th>User</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
            <?php while ($a = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($a['user_name']) ?></td>
                    <td><?= htmlspecialchars($a['service']) ?></td>
                    <td><?= htmlspecialchars($a['date']) ?></td>
                    <td><?= htmlspecialchars($a['time']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($a['status'] ?? 'pending')) ?></td>
                    <td>
                        <?php if (($a['status'] ?? '') !== 'cancelled'): ?>
                            <form action="php/edit_appointment.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <input type="date" name="date" value="<?= $a['date'] ?>" required>
                                <input type="time" name="time" value="<?= $a['time'] ?>" required>
                                <button type="submit">Update</button>
                            </form>
                            <form action="php/cancel_appointment.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                            </form>
                        <?php else: ?>
                            <em>Cancelled</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section" id="clients">
        <h2>Client Profiles</h2>
        <table>
            <tr><th>Name</th><th>Email</th><th>Phone</th></tr>
            <?php while ($c = $clients->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($c['fullname']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section" id="inquiries">
        <h2>User Inquiries</h2>
        <table>
            <tr><th>User</th><th>Message</th><th>Response</th><th>Action</th></tr>
            <?php while ($i = $inquiries->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($i['user_name']) ?></td>
                    <td><?= htmlspecialchars($i['message']) ?></td>
                    <td><?= $i['response'] ? htmlspecialchars($i['response']) : "<em>Pending</em>" ?></td>
                    <td>
                        <form action="php/respond_inquiry.php" method="POST">
                            <input type="hidden" name="id" value="<?= $i['id'] ?>">
                            <input type="text" name="response" placeholder="Respond..." required>
                            <button type="submit">Reply</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
