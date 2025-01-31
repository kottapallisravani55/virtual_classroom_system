<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "virtual_classroom");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch messages from the database
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");

echo "<h1>Sent Messages</h1>";
echo "<table border='1'>";
echo "<tr><th>Phone Number</th><th>Message</th><th>Provider</th><th>Timestamp</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
    echo "<td>" . htmlspecialchars($row['message']) . "</td>";
    echo "<td>" . htmlspecialchars($row['provider']) . "</td>";
    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
