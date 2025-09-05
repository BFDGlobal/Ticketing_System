<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "client") {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $desc = $_POST["description"];
    $department = $_POST["department"];  
    $action_taken = $_POST["action_taken"];  

    $sql = "INSERT INTO tickets (user_id, title, description, department, action_taken) 
            VALUES ('$user_id', '$title', '$desc', '$department', '$action_taken')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Ticket created successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$tickets = $conn->query("SELECT * FROM tickets WHERE user_id='$user_id'");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="style.css">

    <script>
    function updateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: 'numeric', minute: 'numeric', second: 'numeric',
            hour12: true 
        };
        document.getElementById('real-time-clock').textContent = now.toLocaleString('en-US', options);
    }

    setInterval(updateTime, 1000);
    window.onload = updateTime;
    </script>
</head>
<body>
<div class="container">
    <h2>Welcome Client</h2>
    <a href="logout.php" class="logout-button">Logout</a>

    <div id="real-time-clock" style="font-weight: bold; margin-bottom: 15px; font-size: 1.2em;"></div>

    <h3>Create Ticket</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Fullname" required><br>
        <textarea name="description" placeholder="Status Problem" required></textarea><br>
        <input type="text" name="department" placeholder="Department (e.g. IT, Maintenance)" required><br>
        <textarea name="action_taken" placeholder="Action Taken (Optional)" required></textarea><br>
        <button type="submit">Submit</button>
    </form>

    <h3>My Tickets</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Fullname</th>
            <th>Status</th>
            <th>Created</th>
            <th>End At</th>
            <th>Department</th>
            <th>Action Taken</th>
            <th>Status Problem</th>
            <th>Downtime (minutes)</th>
        </tr>
        <?php while($row = $tickets->fetch_assoc()) { 
          
            $created_at = new DateTime($row['created_at']);
            $end_at = new DateTime($row['end_at']);
            $downtime = $created_at->diff($end_at);
            $downtime_minutes = $downtime->h * 60 + $downtime->i; 
        ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><?= htmlspecialchars($row['end_at']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['action_taken']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $downtime_minutes ?> minutes</td>  
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
