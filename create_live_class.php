<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Load PHPMailer library

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Live Class - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="dashboard-container">
        <h2>Create Live Class</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

        <form action="create_live_class.php" method="POST">
            <label for="class_name">Class Name:</label>
            <input type="text" name="class_name" id="class_name" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="class_date">Class Date and Time:</label>
            <input type="datetime-local" name="class_date" id="class_date" required>

            <label for="class_link">Class Link (Zoom/Google Meet/Other):</label>
            <input type="url" name="class_link" id="class_link" required>

            <label for="email">Your Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Your Email Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn">Create Class</button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            include '../database/db_config.php';

            // Retrieve form inputs
            $class_name = $_POST['class_name'];
            $description = $_POST['description'];
            $class_date = $_POST['class_date'];
            $class_link = $_POST['class_link'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $teacher_id = $_SESSION['user_id'];

            // Check for conflicting live classes
            $conflict_query = "SELECT * FROM live_classes WHERE class_date = ? AND class_name = ?";
            $conflict_stmt = $conn->prepare($conflict_query);
            $conflict_stmt->bind_param("ss", $class_date, $class_name);
            $conflict_stmt->execute();
            $conflict_result = $conflict_stmt->get_result();

            if ($conflict_result->num_rows > 0) {
                // Conflict detected
                echo "<script>alert('A live class is already scheduled for \"$class_name\" at $class_date by another teacher. Please choose a different time or class.');</script>";
            } else {
                // Insert the live class details into the database
                $query = "INSERT INTO live_classes (class_name, description, class_date, teacher_id, class_link)
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssis", $class_name, $description, $class_date, $teacher_id, $class_link);

                if ($stmt->execute()) {
                    echo "Live class created successfully!";

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
                            $mail->Subject = "New Live Class Scheduled: $class_name";
                            $mail->isHTML(true);
                            $mail->Body = "A new live class has been scheduled. <br><br>
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
                    } else {
                        echo "No students found in the database.";
                    }

                    // Redirect to the live classes page
                    header("Location: view_live_classes.php");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        }
        ?>
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
