<?php
session_start();
include "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $status = $_POST["status"];
    $end_at = $_POST["end_at"];
    $department = $_POST["department"];
    $action_taken = $_POST["action_taken"];
    $description = $_POST["description"];

    // Calculate downtime from created_at and end_at
    $sql = "SELECT created_at FROM tickets WHERE id = $id";
    $result = $conn->query($sql);
    $ticket = $result->fetch_assoc();
    $created_at = new DateTime($ticket['created_at']);
    $end_at_obj = new DateTime($end_at);  // Convert string end_at to DateTime object
    
    // Calculate downtime only if end_at is not NULL
    if ($end_at_obj) {
        $downtime = $created_at->diff($end_at_obj);
        $downtime_minutes = $downtime->h * 60 + $downtime->i; // Calculate downtime in minutes
    } else {
        $downtime_minutes = NULL; // No downtime if end_at is NULL
    }

    // Format DateTime objects to strings for SQL
    $end_at_str = $end_at_obj->format('Y-m-d H:i:s');  // MySQL DATETIME format

    // Update the ticket with the calculated downtime
    $sql = "UPDATE tickets 
            SET status='$status', end_at='$end_at_str', department='$department', action_taken='$action_taken', description='$description', downtime='$downtime_minutes' 
            WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Ticket updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST["delete"])) {
    $id = $_POST["id"];

    $sql = "DELETE FROM tickets WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Ticket deleted successfully!";
        header("Location: admin.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

$tickets = $conn->query("SELECT tickets.*, users.username FROM tickets JOIN users ON tickets.user_id=users.id ORDER BY tickets.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>BFD-Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">

    <script>
    function updateTime() {
        const now = new Date();
        
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: 'numeric', minute: 'numeric', second: 'numeric',
            hour12: true 
        };
        const formatted = now.toLocaleString('en-US', options);
        document.getElementById('real-time-clock').textContent = formatted;
    }

    setInterval(updateTime, 1000);
    window.onload = updateTime; 
    </script>

</head>
<body>
<div class="container">
    <h2>Welcome Back! Admin</h2>
    <a href="logout.php" class="logout-button">Logout</a>
    <div id="real-time-clock" style="font-weight: bold; margin-bottom: 15px; font-size: 1.2em;"></div>
    <h3>All Tickets</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Title</th>
            <th>Status</th>
            <th>Created</th>
            <th>End At</th>
            <th>Department</th>
            <th>Action Taken</th>
            <th>Description</th>
            <th>Downtime</th> 
            <th>Update</th>
            <th>Delete</th>
        </tr>
        <?php while($row = $tickets->fetch_assoc()) { 
            // Calculate downtime in minutes
            $created_at = new DateTime($row['created_at']);
            $end_at = new DateTime($row['end_at']);
            $downtime = $created_at->diff($end_at);
            $downtime_minutes = $downtime->h * 60 + $downtime->i;
        ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><?= htmlspecialchars($row['end_at']) ?></td>
            <td><?= htmlspecialchars($row['department']) ?></td>
            <td><?= htmlspecialchars($row['action_taken']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $downtime_minutes ?> minutes</td>  <!-- Display calculated downtime -->
            <td>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                    <select name="status">
                        <option value="Open" <?= $row['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                        <option value="In Progress" <?= $row['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Closed" <?= $row['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                    </select><br>
                    <input type="datetime-local" name="end_at" value="<?= htmlspecialchars($row['end_at']) ?>"><br>
                    <input type="text" name="department" value="<?= htmlspecialchars($row['department']) ?>" placeholder="Department" required><br>
                    <textarea name="action_taken" placeholder="Action Taken"><?= htmlspecialchars($row['action_taken']) ?></textarea><br>
                    <textarea name="description" placeholder="Description"><?= htmlspecialchars($row['description']) ?></textarea><br>
                    <input type="number" name="downtime" value="<?= $downtime_minutes ?>" placeholder="Downtime (minutes)" min="0" readonly><br> <!-- Auto downtime, readonly -->
                    <button type="submit" name="update">Update</button>
                </form>
            </td>
            <td>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
