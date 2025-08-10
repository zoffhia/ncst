<?php
include_once(__DIR__.'/../includes/config.php');

/**
 * Get all employees from the employee table
 * @return array - Array of employee data
 */
function getAllEmployees() {
    global $db;
    
    try {
        $query = "SELECT 
            e.empID as employeeID,
            e.employeeNo,
            CONCAT(e.firstName, ' ', COALESCE(e.midName, ''), ' ', e.lastName, ' ', COALESCE(e.suffix, '')) as fullName,
            e.email,
            e.user_role as role,
            e.department,
            e.dateCreated,
            CASE 
                WHEN e.status = 1 THEN 'Active'
                ELSE 'Inactive'
            END as status
        FROM employee e
        ORDER BY e.dateCreated DESC";
        
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $employees
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get employees by department
 * @param string $department - Department name
 * @return array - Array of employee data for the department
 */
function getEmployeesByDepartment($department) {
    global $db;
    
    try {
        $department = $db->real_escape_string($department);
        
        $query = "SELECT 
            e.empID as employeeID,
            e.employeeNo,
            CONCAT(e.firstName, ' ', COALESCE(e.midName, ''), ' ', e.lastName, ' ', COALESCE(e.suffix, '')) as fullName,
            e.email,
            e.user_role as role,
            e.department,
            e.dateCreated,
            CASE 
                WHEN e.status = 1 THEN 'Active'
                ELSE 'Inactive'
            END as status
        FROM employee e
        WHERE e.department = '$department'
        ORDER BY e.dateCreated DESC";
        
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $employees
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get detailed employee information by ID
 * @param int $employeeID - Employee ID
 * @return array - Detailed employee information
 */
function getEmployeeDetails($employeeID) {
    global $db;
    
    try {
        $employeeID = $db->real_escape_string($employeeID);
        
        $query = "SELECT * FROM employee WHERE empID = '$employeeID'";
        $result = $db->query($query);
        
        if (!$result || $result->num_rows === 0) {
            throw new Exception('Employee not found');
        }
        
        $employee = $result->fetch_assoc();
        
        return [
            'status' => 'success',
            'data' => $employee
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
    $response = [];
    
    switch ($_POST['action']) {
        case 'get_all_employees':
            $response = getAllEmployees();
            break;
        case 'get_employees_by_department':
            $department = $_POST['department'];
            $response = getEmployeesByDepartment($department);
            break;
        case 'get_employee_details':
            $empID = $_POST['employeeID'];
            $response = getEmployeeDetails($empID);
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
