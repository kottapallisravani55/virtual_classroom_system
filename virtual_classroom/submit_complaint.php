<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "virtual_classroom";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = isset($_POST['name']) && !empty($_POST['name']) ? $_POST['name'] : "Anonymous";
    $category = $_POST['category'];
    $description = $_POST['description'];

    // Insert the complaint into the database
    $sql = "INSERT INTO complaints (student_name, category, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $category, $description);

    if ($stmt->execute()) {
        echo "<script>alert('Complaint submitted successfully!'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Error submitting complaint: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
