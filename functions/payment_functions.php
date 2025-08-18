<?php
function getStudentData() {
    return [
        "2024-0001" => [
            "name" => "Juan Dela Cruz",
            "tuition" => 25000,
            "other" => 5000,
            "balance" => 3000
        ],
        "2024-0002" => [
            "name" => "Maria Santos",
            "tuition" => 27000,
            "other" => 3000,
            "balance" => 0
        ]
    ];
}

// Search student by ID
function searchStudentById($studentId) {
    $students = getStudentData();
    if (isset($students[$studentId])) {
        return $students[$studentId];
    }
    return null; // Not found
}

// Get payment amount for a student and payment type
function getPaymentAmount($studentId, $paymentType) {
    $student = searchStudentById($studentId);
    if ($student && isset($student[$paymentType])) {
        return $student[$paymentType];
    }
    return null;
}
