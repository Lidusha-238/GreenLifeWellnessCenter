<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
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

$appointments = $conn->query("SELECT * FROM appointments ORDER BY date DESC");
$users = $conn->query("SELECT * FROM users WHERE role = 'user'");
$inquiries = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
$therapists = $conn->query("SELECT * FROM therapists ORDER BY id DESC");

$totalAppointments = $conn->query("SELECT COUNT(*) AS total FROM appointments")->fetch_assoc()['total'];
$totalClients = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user'")->fetch_assoc()['total'];
$totalInquiries = $conn->query("SELECT COUNT(*) AS total FROM inquiries WHERE response IS NULL OR response = ''")->fetch_assoc()['total'];
$totalTherapists = $conn->query("SELECT COUNT(*) AS total FROM therapists")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard - GreenLife</title>
<style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    display: flex;
    height: 100vh;
    background: #f4f4f4;
  }
  .sidebar {
    width: 250px;
    background: #1b4d20;
    color: white;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  }
  .sidebar a {
    color: white;
    padding: 15px 25px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
  }
  .sidebar a:hover,
  .sidebar a.active {
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
    background: #ffffff;
  }
  section {
    display: none;
  }
  section.active {
    display: block;
  }
  h2 {
    color: #2e7d32;
    margin-bottom: 20px;
  }
  .cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
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
  table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }
  th, td {
    border: 1px solid #e0e0e0;
    padding: 10px;
    text-align: left;
  }
  th {
    background: #e8f5e9;
    font-weight: bold;
  }
  form.inline {
    display: inline-block;
    margin-right: 5px;
  }
  button {
    background-color: #2e7d32;
    color: white;
    border: none;
    padding: 6px 12px;
    cursor: pointer;
    border-radius: 4px;
  }
  button:hover {
    background-color: #1b4d20;
  }
  input[type="text"], input[type="email"], input[type="password"], input[type="date"], input[type="time"] {
    padding: 8px;
    margin: 5px 0;
    width: 100%;
    box-sizing: border-box;
  }
</style>
</head>
<body>

<div class="sidebar">
  <a href="#" class="tab-link active" data-tab="dashboard">Dashboard</a>
  <a href="#" class="tab-link" data-tab="appointments">Appointments</a>
  <a href="#" class="tab-link" data-tab="clients">Clients</a>
  <a href="#" class="tab-link" data-tab="inquiries">Inquiries</a>
  <a href="#" class="tab-link" data-tab="therapists">Therapists</a>
  <a href="php/logout.php" class="logout">Logout</a>
</div>

<div class="content">
  <section id="dashboard" class="active">
    <h2>Welcome, Admin!</h2>
    <div class="cards">
      <div class="card">
        <h3>Total Appointments</h3>
        <p><?= $totalAppointments ?></p>
      </div>
      <div class="card">
        <h3>Active Clients</h3>
        <p><?= $totalClients ?></p>
      </div>
      <div class="card">
        <h3>Pending Queries</h3>
        <p><?= $totalInquiries ?></p>
      </div>
      <div class="card">
        <h3>Therapists</h3>
        <p><?= $totalTherapists ?></p>
      </div>
    </div>
  </section>

  <section id="appointments">
    <h2>Manage Appointments</h2>
    <table>
      <tr>
        <th>User</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th>
      </tr>
      <?php while ($a = $appointments->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($a['user_name']) ?></td>
        <td><?= htmlspecialchars($a['service']) ?></td>
        <td><?= htmlspecialchars($a['date']) ?></td>
        <td><?= htmlspecialchars($a['time']) ?></td>
        <td><?= ucfirst(htmlspecialchars($a['status'])) ?></td>
        <td>
          <?php if ($a['status'] !== 'cancelled'): ?>
          <form action="php/edit_appointment.php" method="POST" class="inline">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <input type="date" name="date" value="<?= $a['date'] ?>" required>
            <input type="time" name="time" value="<?= $a['time'] ?>" required>
            <button type="submit">Update</button>
          </form>
          <form action="php/cancel_appointment.php" method="POST" class="inline" onsubmit="return confirm('Cancel this appointment?')">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button type="submit">Cancel</button>
          </form>
          <?php else: ?>
            <em>Cancelled</em>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </section>

  <section id="clients">
    <h2>Client Profiles</h2>
    <table>
      <tr><th>Name</th><th>Email</th><th>Phone</th></tr>
      <?php while ($u = $users->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($u['fullname']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone']) ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </section>

  <section id="inquiries">
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
            <input type="text" name="response" placeholder="Write response..." required>
            <button type="submit">Reply</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </section>

  <section id="therapists">
    <h2>Therapist Management</h2>
    <form action="php/add_therapist.php" method="POST" style="margin-bottom:20px;">
      <input type="text" name="fullname" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="specialization" placeholder="Specialization" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Add Therapist</button>
    </form>

    <h3>All Therapists</h3>
    <table>
      <tr><th>Name</th><th>Email</th><th>Specialization</th><th>Actions</th></tr>
      <?php while ($t = $therapists->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($t['name']) ?></td>
        <td><?= htmlspecialchars($t['email']) ?></td>
        <td><?= htmlspecialchars($t['specialization']) ?></td>
        <td>
          <form action="php/delete_therapist.php" method="POST" onsubmit="return confirm('Delete this therapist?')" style="display:inline;">
            <input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </section>
</div>

<script>
  const tabs = document.querySelectorAll('.tab-link');
  const sections = document.querySelectorAll('section');
  tabs.forEach(tab => {
    tab.addEventListener('click', e => {
      e.preventDefault();
      tabs.forEach(t => t.classList.remove('active'));
      sections.forEach(s => s.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab).classList.add('active');
    });
  });
</script>
</body>
</html>
