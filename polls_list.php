<?php
session_start();
include '../database/db_config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch teacher or student data
$is_teacher = false;
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();

if ($user['role'] == 'teacher') {
    $is_teacher = true;
}

// Handle form submission for creating a poll (Teacher or allowed student)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_poll'])) {
    $question = $_POST['question'];
    $options = $_POST['options'];

    // Insert the poll into the database
    $poll_query = "INSERT INTO polls (question, is_active, created_at) VALUES (?, 1, NOW())";
    $stmt = $conn->prepare($poll_query);
    $stmt->bind_param("s", $question);
    $stmt->execute();
    $poll_id = $stmt->insert_id;

    // Insert poll options
    foreach ($options as $option) {
        if (!empty($option)) {
            $option_query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $stmt = $conn->prepare($option_query);
            $stmt->bind_param("is", $poll_id, $option);
            $stmt->execute();
        }
    }

    header("Location: poll_list.php");
    exit();
}

// Handle form submission for poll responses (Student voting)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['poll_id']) && !$is_teacher) {
    $poll_id = $_POST['poll_id'];
    $option_id = $_POST['option'];

    // Check if the poll exists
    $check_poll_query = "SELECT * FROM polls WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($check_poll_query);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $poll_result = $stmt->get_result();

    if ($poll_result->num_rows == 0) {
        echo "<script>alert('This poll does not exist or is no longer active.');</script>";
        exit();
    }

    // Check if the option exists
    $check_option_query = "SELECT * FROM poll_options WHERE id = ? AND poll_id = ?";
    $stmt = $conn->prepare($check_option_query);
    $stmt->bind_param("ii", $option_id, $poll_id);
    $stmt->execute();
    $option_result = $stmt->get_result();

    if ($option_result->num_rows == 0) {
        echo "<script>alert('This option is invalid.');</script>";
        exit();
    }

    // Check if the user has already responded
    $check_query = "SELECT * FROM poll_responses WHERE poll_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $poll_id, $user_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows == 0) {
        // Insert the response if not already voted
        $response_query = "INSERT INTO poll_responses (poll_id, user_id, option_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($response_query);
        $stmt->bind_param("iii", $poll_id, $user_id, $option_id);
        $stmt->execute();
    } else {
        echo "<script>alert('You have already voted for this poll.');</script>";
    }
}

// Deactivate Poll (Teacher or allowed student)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deactivate_poll']) && isset($_POST['poll_id']) && $is_teacher) {
    $poll_id = $_POST['poll_id'];

    // Update the poll status to inactive
    $deactivate_query = "UPDATE polls SET is_active = 0 WHERE id = ?";
    $stmt = $conn->prepare($deactivate_query);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();

    echo "<script>alert('Poll deactivated successfully.');</script>";
}

// Grant student poll creation rights (Teacher only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grant_poll_creation']) && $is_teacher) {
    $student_id = $_POST['student_id'];

    // Grant the student poll creation rights by adding a record to the student_poll_access table
    $grant_access_query = "INSERT INTO student_poll_access (student_id, granted_by) VALUES (?, ?)";
    $stmt = $conn->prepare($grant_access_query);
    $stmt->bind_param("ii", $student_id, $user_id);
    $stmt->execute();
    header("Location: poll_list.php");
    exit();
}

// Fetch active polls
$sql = "SELECT * FROM polls WHERE is_active = 1";
$result = $conn->query($sql);

// Fetch all students to grant poll creation rights
$students_sql = "SELECT * FROM users WHERE role = 'student'";
$students_result = $conn->query($students_sql);

// Fetch students with poll creation access
$poll_access_sql = "SELECT * FROM users WHERE id IN (SELECT student_id FROM student_poll_access)";
$poll_access_result = $conn->query($poll_access_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polls/Surveys - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <script>
        // Function to add new options dynamically
        function addOption() {
            const optionsDiv = document.getElementById('options');
            const newOption = document.createElement('div');
            newOption.classList.add('option');
            newOption.innerHTML = '<input type="text" name="options[]" placeholder="Option" required> <button type="button" onclick="removeOption(this)">Remove Option</button>';
            optionsDiv.appendChild(newOption);
        }

        // Function to remove options dynamically
        function removeOption(button) {
            const optionsDiv = document.getElementById('options');
            optionsDiv.removeChild(button.parentElement);
        }
    </script>
    <div id="google_translate_element"></div>
</head>
<body>

<div class="poll-container">
    <h2>Polls/Surveys</h2>

    <?php if ($is_teacher) { ?>
        <!-- Poll Creation Form for Teachers -->
        <h3>Create New Poll/Survey</h3>
        <form method="POST">
            <label for="question">Poll Question:</label><br>
            <input type="text" name="question" id="question" required><br><br>

            <div id="options">
                <label>Poll Options:</label><br>
                <div class="option">
                    <input type="text" name="options[]" placeholder="Option 1" required>
                    <button type="button" onclick="removeOption(this)">Remove Option</button>
                </div>
                <div class="option">
                    <input type="text" name="options[]" placeholder="Option 2" required>
                    <button type="button" onclick="removeOption(this)">Remove Option</button>
                </div>
            </div>
            <button type="button" onclick="addOption()">Add Option</button><br><br>
            <button type="submit" name="create_poll">Create Poll</button>
        </form>
        <hr>

        <!-- Grant Poll Creation Rights to Students -->
        <h3>Grant Poll Creation Rights</h3>
        <form method="POST">
            <label for="student_id">Select Student:</label>
            <select name="student_id" id="student_id">
                <?php while ($student = $students_result->fetch_assoc()) { ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo $student['username']; ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="grant_poll_creation">Grant Poll Creation Rights</button>
        </form>
        <hr>
    <?php } ?>

    <!-- Display Active Polls -->
    <?php while ($poll = $result->fetch_assoc()) { ?>
        <div class="poll">
            <h3><?php echo $poll['question']; ?></h3>

            <?php if ($is_teacher) { ?>
                <!-- Teacher View: Show responses and manage the poll -->
                <p><strong>Poll Responses:</strong></p>
                <?php
                $options_query = "SELECT * FROM poll_options WHERE poll_id = ?";
                $stmt = $conn->prepare($options_query);
                $stmt->bind_param("i", $poll['id']);
                $stmt->execute();
                $options_result = $stmt->get_result();

                while ($option = $options_result->fetch_assoc()) {
                    $option_id = $option['id'];
                    $response_count_query = "SELECT COUNT(*) AS response_count FROM poll_responses WHERE option_id = ?";
                    $stmt = $conn->prepare($response_count_query);
                    $stmt->bind_param("i", $option_id);
                    $stmt->execute();
                    $response_count_result = $stmt->get_result();
                    $response_count = $response_count_result->fetch_assoc()['response_count'];

                    $total_responses_query = "SELECT COUNT(*) AS total_responses FROM poll_responses WHERE poll_id = ?";
                    $stmt = $conn->prepare($total_responses_query);
                    $stmt->bind_param("i", $poll['id']);
                    $stmt->execute();
                    $total_responses_result = $stmt->get_result();
                    $total_responses = $total_responses_result->fetch_assoc()['total_responses'];

                    $percentage = $total_responses > 0 ? round(($response_count / $total_responses) * 100, 2) : 0;
                ?>

                    <p><?php echo $option['option_text']; ?> - <?php echo $response_count; ?> responses (<?php echo $percentage; ?>%)</p>
                <?php } ?>

                <form method="POST">
                    <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                    <button type="submit" name="deactivate_poll">Deactivate Poll</button>
                </form>

            <?php } else { ?>
                <!-- Students View: Allow voting -->
                <form method="POST">
                    <?php
                    $options_query = "SELECT * FROM poll_options WHERE poll_id = ?";
                    $stmt = $conn->prepare($options_query);
                    $stmt->bind_param("i", $poll['id']);
                    $stmt->execute();
                    $options_result = $stmt->get_result();
                    ?>
                    <?php while ($option = $options_result->fetch_assoc()) { ?>
                        <input type="radio" name="option" value="<?php echo $option['id']; ?>" required>
                        <?php echo $option['option_text']; ?><br>
                    <?php } ?>
                    <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                    <button type="submit">Vote</button>
                </form>
            <?php } ?>
        </div>
        <hr>
    <?php } ?>
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
