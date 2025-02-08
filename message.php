<?php
// Connecting to the database
$conn = mysqli_connect("localhost", "root", "", "virtual_classroom2") or die("Database connection failed");

// Get user message through AJAX
$getMesg = mysqli_real_escape_string($conn, $_POST['text']);

// Initialize the response
$response = "";

// Predefined greetings and fallback
$greetings = ['hi','hii', 'hello', 'hey', 'good morning', 'good evening', 'howdy', 'sup'];
$goodbye = ['bye', 'goodbye', 'see you later'];

foreach ($greetings as $greeting) {
    if (stripos($getMesg, $greeting) !== false) {
        $response .= "Hello! How can I assist you with your virtual classroom today?";
        echo $response;
        exit();
    }
}

foreach ($goodbye as $exit_word) {
    if (stripos($getMesg, $exit_word) !== false) {
        $response .= "Goodbye! Have a great day!";
        echo $response;
        exit();
    }
}

// Check if the message is related to Study Materials
$check_subject = "SELECT file_name, subject FROM study_materials WHERE subject LIKE '%$getMesg%'";
$run_subject_query = mysqli_query($conn, $check_subject);

// Check for available study materials
if (mysqli_num_rows($run_subject_query) > 0) {
    $response .= "Here are the study materials related to '$getMesg':<br>";
    while ($row = mysqli_fetch_assoc($run_subject_query)) {
        $response .= "<a href='../assets/uploads/{$row['file_name']}' target='_self'>{$row['file_name']}</a><br>";
    }
}

// Check for live class related to the query
$check_live_classes = "SELECT class_name, video_path, class_link FROM live_classes WHERE class_name LIKE '%$getMesg%'";
$run_live_classes_query = mysqli_query($conn, $check_live_classes);

if (mysqli_num_rows($run_live_classes_query) > 0) {
    $response .= "<br>Here are the live class recordings and links for '$getMesg':<br>";
    while ($row = mysqli_fetch_assoc($run_live_classes_query)) {
        $response .= "<b>Class Name:</b> {$row['class_name']}<br>";
        if (!empty($row['video_path'])) {
            $response .= "<a href='../uploads/videos/{$row['video_path']}' target='_self'>Watch Recording</a><br>";
        }
        $response .= "<a href='{$row['class_link']}' target='_blank'>Join Live Class</a><br><br>";
    }
}

// If no direct response, try predefined chatbot queries
if (empty($response)) {
    $check_data = "SELECT replies FROM chatbot WHERE queries LIKE '%$getMesg%'";
    $run_query = mysqli_query($conn, $check_data);

    if (mysqli_num_rows($run_query) > 0) {
        $fetch_data = mysqli_fetch_assoc($run_query);
        echo $fetch_data['replies'];
    } else {
        // Fallback intelligent response
        $response = "I didn't quite catch that. Can you please specify what you're looking for? Try asking about 'study materials', 'live classes', or 'assignments'.";
        echo $response;
    }
} else {
    echo $response;
}
?>
