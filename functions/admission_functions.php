<?php
include_once(__DIR__.'/../includes/config.php');

/**
 * Insert admission data into database
 * @param array $student - Student information
 * @param array $education - Educational background
 * @param array $parent - Parent/Guardian information
 * @return array - Response with status and message
 */
function insertAdmission($student, $education, $parent) {
    global $db;
    
    try {
        $db->begin_transaction();

        $studentSql = "INSERT INTO stud_reg_info (
            firstName, midName, lastName, suffix, address, zip, phone, 
            gender, civilStatus, nationality, birthDate, birthPlace, email, religion,
            employer, position, course, yearLevel, houseHeroes, nstp, dateSubmitted, isApproved
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
        
        $studentStmt = $db->prepare($studentSql);
        
        if (!$studentStmt) {
            throw new Exception('Database preparation failed: ' . $db->error);
        }
        
        $studentStmt->bind_param("ssssssssssssssssssss",
            $student['firstName'],
            $student['midName'],
            $student['lastName'],
            $student['suffix'],
            $student['address'],
            $student['zip'],
            $student['phone'],
            $student['gender'],
            $student['civilStatus'],
            $student['nationality'],
            $student['birthDate'],
            $student['birthPlace'],
            $student['email'],
            $student['religion'],
            $student['employer'],
            $student['position'],
            $student['course'],
            $student['yearLevel'],
            $student['houseHeroes'],
            $student['nstp']
        );
        
        if (!$studentStmt->execute()) {
            throw new Exception('Failed to insert student data: ' . $studentStmt->error);
        }
        
        $studentID = $studentStmt->insert_id;
        $studentStmt->close();

        $educationSql = "INSERT INTO educ_reg_info (
            studentID, primarySchool, primaryYear, secondarySchool, secondaryYear,
            tertiarySchool, tertiaryYear, courseGraduated
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $educationStmt = $db->prepare($educationSql);
        
        if (!$educationStmt) {
            throw new Exception('Database preparation failed: ' . $db->error);
        }
        
        $educationStmt->bind_param("isssssss",
            $studentID,
            $education['primarySchool'],
            $education['primaryYear'],
            $education['secondarySchool'],
            $education['secondaryYear'],
            $education['tertiarySchool'],
            $education['tertiaryYear'],
            $education['courseGraduated']
        );
        
        if (!$educationStmt->execute()) {
            throw new Exception('Failed to insert education data: ' . $educationStmt->error);
        }
        
        $educationStmt->close();

        $parentSql = "INSERT INTO parents_reg_info (
            studentID, fatherFirstName, fatherMidName, fatherLastName, fatherSuffix,
            fatherAddress, fatherPhone, fatherOccupation, motherFirstName, motherMidName,
            motherLastName, motherAddress, motherPhone, motherOccupation,
            guardianFirstName, guardianMidName, guardianLastName, guardianSuffix,
            guardianAddress, guardianPhone, guardianOccupation, guardianRelationship
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $parentStmt = $db->prepare($parentSql);
        
        if (!$parentStmt) {
            throw new Exception('Database preparation failed: ' . $db->error);
        }
        
        $parentStmt->bind_param("isssssssssssssssssssss",
            $studentID,
            $parent['fatherFirstName'],
            $parent['fatherMidName'],
            $parent['fatherLastName'],
            $parent['fatherSuffix'],
            $parent['fatherAddress'],
            $parent['fatherPhone'],
            $parent['fatherOccupation'],
            $parent['motherFirstName'],
            $parent['motherMidName'],
            $parent['motherLastName'],
            $parent['motherAddress'],
            $parent['motherPhone'],
            $parent['motherOccupation'],
            $parent['guardianFirstName'],
            $parent['guardianMidName'],
            $parent['guardianLastName'],
            $parent['guardianSuffix'],
            $parent['guardianAddress'],
            $parent['guardianPhone'],
            $parent['guardianOccupation'],
            $parent['guardianRelationship']
        );
        
        if (!$parentStmt->execute()) {
            throw new Exception('Failed to insert parent data: ' . $parentStmt->error);
        }
        
        $parentStmt->close();
        $db->commit();
        
        return [
            'status' => 'success',
            'message' => 'Admission submitted successfully!',
            'studentID' => $studentID
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        
        return [
            'status' => 'error',
            'message' => 'Admission submission failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Get all student registration data for admissions management
 * @return array - Array of student registration data
 */
function getAllStudentRegistrations() {
    global $db;
    
    try {
        $query = "SELECT 
            s.studentID,
            CONCAT(s.firstName, ' ', COALESCE(s.midName, ''), ' ', s.lastName, ' ', COALESCE(s.suffix, '')) as fullName,
            s.email,
            s.course,
            s.yearLevel,
            s.dateSubmitted,
            CASE 
                WHEN s.isApproved = 1 THEN 'Approved'
                WHEN s.isApproved = 2 THEN 'Rejected'
                WHEN s.isApproved = 3 THEN 'Processed'
                ELSE 'Pending'
            END as status
        FROM stud_reg_info s
        WHERE s.isApproved != 4
        ORDER BY s.dateSubmitted DESC";
        
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $students
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get detailed student information by ID
 * @param int $studentID - Student ID
 * @return array - Detailed student information
 */
function getStudentDetails($studentID) {
    global $db;
    
    try {
        $studentID = $db->real_escape_string($studentID);
        
        // Get student information
        $studentQuery = "SELECT * FROM stud_reg_info WHERE studentID = '$studentID'";
        $studentResult = $db->query($studentQuery);
        
        if (!$studentResult || $studentResult->num_rows === 0) {
            throw new Exception('Student not found');
        }
        
        $student = $studentResult->fetch_assoc();
        
        // Get education information
        $educationQuery = "SELECT * FROM educ_reg_info WHERE studentID = '$studentID'";
        $educationResult = $db->query($educationQuery);
        $education = $educationResult ? $educationResult->fetch_assoc() : null;
        
        // Get parent information
        $parentQuery = "SELECT * FROM parents_reg_info WHERE studentID = '$studentID'";
        $parentResult = $db->query($parentQuery);
        $parent = $parentResult ? $parentResult->fetch_assoc() : null;
        
        return [
            'status' => 'success',
            'data' => [
                'student' => $student,
                'education' => $education,
                'parent' => $parent
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Approve a student admission
 * @param int $studentID - Student ID to approve
 * @return array - Response with status and message
 */
function approveStudentAdmission($studentID) {
    global $db;
    
    try {
        $studentID = $db->real_escape_string($studentID);
        
        $query = "UPDATE stud_reg_info SET isApproved = 1 WHERE studentID = '$studentID'";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database update failed: ' . $db->error);
        }
        
        if ($db->affected_rows === 0) {
            throw new Exception('Student not found');
        }
        
        return [
            'status' => 'success',
            'message' => 'Student admission approved successfully!'
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Reject a student admission
 * @param int $studentID - Student ID to reject
 * @return array - Response with status and message
 */
function rejectStudentAdmission($studentID) {
    global $db;
    
    try {
        $studentID = $db->real_escape_string($studentID);
        
        $query = "UPDATE stud_reg_info SET isApproved = 2 WHERE studentID = '$studentID'";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database update failed: ' . $db->error);
        }
        
        if ($db->affected_rows === 0) {
            throw new Exception('Student not found');
        }
        
        return [
            'status' => 'success',
            'message' => 'Student admission rejected successfully!'
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Archive a student admission
 * @param int $studentID - Student ID to archive
 * @return array - Response with status and message
 */
function archiveStudent($studentID) {
    global $db;
    
    try {
        $studentID = $db->real_escape_string($studentID);
        
        $query = "UPDATE stud_reg_info SET isApproved = 4 WHERE studentID = '$studentID'";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database update failed: ' . $db->error);
        }
        
        if ($db->affected_rows === 0) {
            throw new Exception('Student not found');
        }
        
        return [
            'status' => 'success',
            'message' => 'Student archived successfully!'
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Create a student record in the student table after ID generation
 * @param int $studentID - Original student registration ID
 * @param string $fullName - Student's full name
 * @param string $email - Student's email
 * @param string $course - Student's course
 * @param string $yearLevel - Student's year level
 * @return array - Response with status, message, and generated student number
 */
function createStudentRecord($studentID, $fullName, $email, $course, $yearLevel) {
    global $db;
    
    try {
        $studentID = $db->real_escape_string($studentID);
        $fullName = $db->real_escape_string($fullName);
        $email = $db->real_escape_string($email);
        $course = $db->real_escape_string($course);
        $yearLevel = $db->real_escape_string($yearLevel);

        $currentYear = date('Y');

        $lastStudentQuery = "SELECT studentNo FROM student WHERE studentNo LIKE '$currentYear-%' ORDER BY studentNo DESC LIMIT 1";
        $lastStudentResult = $db->query($lastStudentQuery);
        
        if ($lastStudentResult && $lastStudentResult->num_rows > 0) {
            $lastStudent = $lastStudentResult->fetch_assoc();
            $lastNumber = intval(substr($lastStudent['studentNo'], 5));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $studentNo = sprintf('%s-%05d', $currentYear, $newNumber);

        $nameParts = explode(' ', trim($fullName));
        $lastName = end($nameParts) ?? '';
        
        $query = "INSERT INTO student (studentNo, fullName, email, course, yearLevel, dateCreated, password, role) 
                  VALUES ('$studentNo', '$fullName', '$email', '$course', '$yearLevel', NOW(), '$lastName', 'student')";
        
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Failed to create student record: ' . $db->error);
        }

        $updateQuery = "UPDATE stud_reg_info SET isApproved = 3 WHERE studentID = '$studentID'";
        $updateResult = $db->query($updateQuery);
        
        if (!$updateResult) {
            throw new Exception('Failed to update registration status: ' . $db->error);
        }
        
        return [
            'status' => 'success',
            'message' => 'Student record created successfully!',
            'studentNo' => $studentNo
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Search for a student by their student ID
 * @param string $studentId - Student ID to search for
 * @return array - Response with status and student data
 */
function searchStudentById($studentId) {
    global $db;
    
    try {
        $studentId = $db->real_escape_string($studentId);
        
        $query = "SELECT * FROM student WHERE studentNo = '$studentId'";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        if ($result->num_rows === 0) {
            return [
                'status' => 'error',
                'message' => 'No student found with the provided Student ID'
            ];
        }
        
        $student = $result->fetch_assoc();
        
        return [
            'status' => 'success',
            'data' => $student
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = [];
    
    switch ($_POST['action']) {
        case 'admission':
            $student = json_decode($_POST['student'], true);
            $education = json_decode($_POST['education'], true);
            $parent = json_decode($_POST['parent'], true);

            $response = insertAdmission($student, $education, $parent);
            break;
        case 'get_all_student_registrations':
            $response = getAllStudentRegistrations();
            break;
        case 'get_student_details':
            $studentID = $_POST['studentID'];
            $response = getStudentDetails($studentID);
            break;
        case 'approve_student':
            $studentID = $_POST['studentID'];
            $response = approveStudentAdmission($studentID);
            break;
        case 'reject_student':
            $studentID = $_POST['studentID'];
            $response = rejectStudentAdmission($studentID);
            break;
        case 'create_student_record':
            $studentID = $_POST['studentID'];
            $fullName = $_POST['fullName'];
            $email = $_POST['email'];
            $course = $_POST['course'];
            $yearLevel = $_POST['yearLevel'];
            $response = createStudentRecord($studentID, $fullName, $email, $course, $yearLevel);
            break;
        case 'archive_student':
            $studentID = $_POST['studentID'];
            $response = archiveStudent($studentID);
            break;
        case 'search_student_by_no':
            $studentNo = $_POST['studentNo'];
            $response = searchStudentById($studentNo);
            break;
        default:
            $response = [
                'status' => 'error',
                'message' => 'Invalid action specified'
            ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>