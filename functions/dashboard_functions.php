<?php
include_once('../includes/config.php');

/**
 * Get dashboard statistics for registrar portal
 * @return array Dashboard statistics
 */
function getRegistrarDashboardStats() {
    global $db;
    
    try {
        $enrolledQuery = "SELECT COUNT(*) as total FROM student";
        $enrolledResult = $db->query($enrolledQuery);
        $enrolled = $enrolledResult->fetch_assoc()['total'];

        $applicationsQuery = "SELECT COUNT(*) as total FROM stud_reg_info";
        $applicationsResult = $db->query($applicationsQuery);
        $totalApplications = $applicationsResult->fetch_assoc()['total'];

        $pendingQuery = "SELECT COUNT(*) as total FROM stud_reg_info WHERE isApproved = 0";
        $pendingResult = $db->query($pendingQuery);
        $pendingApplications = $pendingResult->fetch_assoc()['total'];

        $approvedQuery = "SELECT COUNT(*) as total FROM stud_reg_info WHERE isApproved = 1";
        $approvedResult = $db->query($approvedQuery);
        $approvedApplications = $approvedResult->fetch_assoc()['total'];

        $rejectedQuery = "SELECT COUNT(*) as total FROM stud_reg_info WHERE isApproved = 2";
        $rejectedResult = $db->query($rejectedQuery);
        $rejectedApplications = $rejectedResult->fetch_assoc()['total'];
        
        return [
            'status' => 'success',
            'data' => [
                'enrolled_students' => $enrolled,
                'total_applications' => $totalApplications,
                'pending_applications' => $pendingApplications,
                'approved_applications' => $approvedApplications,
                'rejected_applications' => $rejectedApplications
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
 * Get applications by day for the past week
 * @return array Weekly applications data
 */
function getWeeklyApplications() {
    global $db;
    
    try {
        $query = "SELECT 
                    DATE(dateSubmitted) as application_date,
                    COUNT(*) as application_count
                  FROM stud_reg_info 
                  WHERE dateSubmitted >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(dateSubmitted)
                  ORDER BY application_date";
        
        $result = $db->query($query);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'date' => $row['application_date'],
                'count' => (int)$row['application_count']
            ];
        }

        $filledData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $found = false;
            
            foreach ($data as $item) {
                if ($item['date'] === $date) {
                    $filledData[] = $item;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $filledData[] = [
                    'date' => $date,
                    'count' => 0
                ];
            }
        }
        
        return [
            'status' => 'success',
            'data' => $filledData
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get enrollment by year level
 * @return array Year level enrollment data
 */
function getEnrollmentByYearLevel() {
    global $db;
    
    try {
        $query = "SELECT 
                    yearLevel,
                    COUNT(*) as student_count
                  FROM student 
                  GROUP BY yearLevel
                  ORDER BY yearLevel";
        
        $result = $db->query($query);
        $data = [];

        $yearLevelMap = [
            '1' => '1st Year Level',
            '2' => '2nd Year Level',
            '3' => '3rd Year Level',
            '4' => '4th Year Level'
        ];
        
        while ($row = $result->fetch_assoc()) {
            $level = $row['yearLevel'];
            $data[] = [
                'year_level' => $yearLevelMap[$level] ?? $level,
                'count' => (int)$row['student_count']
            ];
        }
        
        return [
            'status' => 'success',
            'data' => $data
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get recent applications
 * @param int $limit Number of recent applications to fetch
 * @return array Recent applications data
 */
function getRecentApplications($limit = 10) {
    global $db;
    
    try {
        $query = "SELECT 
                    s.studentID,
                    CONCAT(s.firstName, ' ', COALESCE(s.midName, ''), ' ', s.lastName) as fullName,
                    s.email,
                    s.dateSubmitted as application_date,
                    s.isApproved as status,
                    p.name as course_name
                  FROM stud_reg_info s
                  LEFT JOIN programs p ON s.course = p.code
                  ORDER BY s.dateSubmitted DESC
                  LIMIT $limit";
        
        $result = $db->query($query);
        $applications = [];
        
        while ($row = $result->fetch_assoc()) {
            $status = '';
            switch ($row['status']) {
                case 0:
                    $status = 'Pending';
                    break;
                case 1:
                    $status = 'Approved';
                    break;
                case 2:
                    $status = 'Rejected';
                    break;
                case 3:
                    $status = 'Enrolled';
                    break;
                case 4:
                    $status = 'Archived';
                    break;
                default:
                    $status = 'Unknown';
            }
            
            $applications[] = [
                'application_id' => $row['studentID'],
                'name' => $row['fullName'],
                'email' => $row['email'],
                'date' => $row['application_date'],
                'status' => $status,
                'course' => $row['course_name'] ?? 'Not specified'
            ];
        }
        
        return [
            'status' => 'success',
            'data' => $applications
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_dashboard_stats':
            echo json_encode(getRegistrarDashboardStats());
            break;
            
        case 'get_weekly_applications':
            echo json_encode(getWeeklyApplications());
            break;
            
        case 'get_enrollment_by_year_level':
            echo json_encode(getEnrollmentByYearLevel());
            break;
            
        case 'get_recent_applications':
            $limit = $_POST['limit'] ?? 10;
            echo json_encode(getRecentApplications((int)$limit));
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action'
            ]);
    }
    exit;
}
?>