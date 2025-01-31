<?php
// Connecting to the database
$conn = mysqli_connect("localhost", "root", "", "virtual_classroom") or die("Database connection failed");

// Get user message through AJAX
$getMesg = mysqli_real_escape_string($conn, $_POST['text']);

// Initialize the response
$response = "";

// Check if the user query matches a subject in the study_material table
$check_subject = "SELECT file_name FROM study_materials WHERE subject LIKE '%$getMesg%'";
$run_subject_query = mysqli_query($conn, $check_subject);

// If study materials are found
if (mysqli_num_rows($run_subject_query) > 0) {
    $response .= "Here are the study materials for '$getMesg':<br>";
    while ($row = mysqli_fetch_assoc($run_subject_query)) {
        // Adjust file path for uploads directory outside pages folder
        $response .= "<a href='../assets/uploads/{$row['file_name']}' target='_self'>{$row['file_name']}</a><br>";
    }
}

// Check if there are live class recordings in the liveclasses table
$check_live_classes = "SELECT class_name, video_path, class_link FROM live_classes WHERE class_name LIKE '%$getMesg%'";
$run_live_classes_query = mysqli_query($conn, $check_live_classes);

// If live class recordings are found
if (mysqli_num_rows($run_live_classes_query) > 0) {
    $response .= "<br>Here are the live class recordings and links for '$getMesg':<br>";
    while ($row = mysqli_fetch_assoc($run_live_classes_query)) {
        $response .= "<b>Class Name:</b> {$row['class_name']}<br>";
        if (!empty($row['video_path'])) {
            // Adjust file path for videos in uploads/videos directory
            $response .= "<a href='../uploads/videos/{$row['video_path']}' target='_self'>Watch Recording</a><br>";
        }
        $response .= "<a href='{$row['class_link']}' target='_blank'>Join Live Class</a><br><br>";
    }
}

// If no study materials or live class recordings are found, check predefined replies in the chatbot table
if (empty($response)) {
    $check_data = "SELECT replies FROM chatbot WHERE queries LIKE '%$getMesg%'";
    $run_query = mysqli_query($conn, $check_data);

    if (mysqli_num_rows($run_query) > 0) {
        $fetch_data = mysqli_fetch_assoc($run_query);
        echo $fetch_data['replies'];
    } else {
        echo "Sorry, I can't find any study materials, live classes, or replies related to your query.";
    }
} else {
    echo $response;
}
?>
