<?php
include_once('../includes/config.php');
include_once('admission_functions.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_student_details') {
    $studentID = $_POST['studentID'] ?? null;
    
    if (!$studentID) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Student ID is required'
        ]);
        exit;
    }
    
    $result = getStudentDetails($studentID);
    echo json_encode($result);
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request'
]);
?>
