<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Load PHPMailer library

include '../database/db_config.php';

// Get the class ID
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    echo "Class ID is required.";
    exit();
}

// Fetch class details
$query = "SELECT * FROM live_classes WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Class not found or you do not have permission to edit it.";
    exit();
}

$class = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update class details
    $class_name = $_POST['class_name'];
    $description = $_POST['description'];
    $class_date = $_POST['class_date'];
    $class_link = $_POST['class_link'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $updateQuery = "UPDATE live_classes SET class_name = ?, description = ?, class_date = ?, class_link = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssii", $class_name, $description, $class_date, $class_link, $class_id);

    if ($updateStmt->execute()) {
        echo "Class updated successfully!";

        // Fetch all student emails
        $studentQuery = "SELECT email FROM students";
        $result = $conn->query($studentQuery);

        if ($result->num_rows > 0) {
            // Prepare PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $email; // Teacher's email
                $mail->Password = $password; // Teacher's email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email settings
                $mail->setFrom($email, 'Virtual Classroom');
                $mail->Subject = "Updated Live Class Details: $class_name";
                $mail->isHTML(true);
                $mail->Body = "The live class details have been updated. <br><br>
                               <strong>Class Name:</strong> $class_name<br>
                               <strong>Description:</strong> $description<br>
                               <strong>Date and Time:</strong> $class_date<br>
                               <strong>Join Class:</strong> <a href='$class_link' target='_blank'>Click here</a>";

                // Add all student emails as BCC
                while ($student = $result->fetch_assoc()) {
                    $mail->addBCC($student['email']);
                }

                // Send the email
                $mail->send();
                echo "Emails sent to all students successfully!";
            } catch (Exception $e) {
                echo "Error while sending emails: {$mail->ErrorInfo}";
            }
        }

        // Redirect to view classes
        header("Location: view_live_classes.php");
        exit();
    } else {
        echo "Error updating class: " . $updateStmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Live Class</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="dashboard-container">
        <h2>Edit Live Class</h2>
        <form action="edit_live_class.php?class_id=<?php echo $class_id; ?>" method="POST">
            <label for="class_name">Class Name:</label>
            <input type="text" name="class_name" id="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required><?php echo htmlspecialchars($class['description']); ?></textarea>

            <label for="class_date">Class Date and Time:</label>
            <input type="datetime-local" name="class_date" id="class_date" value="<?php echo date('Y-m-d\TH:i', strtotime($class['class_date'])); ?>" required>

            <label for="class_link">Class Link:</label>
            <input type="url" name="class_link" id="class_link" value="<?php echo htmlspecialchars($class['class_link']); ?>" required>

            <label for="email">Your Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Your Email Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn">Update Class</button>
        </form>
    </div>
    <script src="script.js"></script>
      <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement(
                {pageLanguage: 'en'},
                'google_translate_element'
            );
        } 
  </script>
  <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
