<?php
include_once(__DIR__.'/../includes/config.php');

function getAllDepartments() {
    global $db;
    
    try {
        $query = "SELECT * FROM departments ORDER BY name";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $departments
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function getDepartmentsByType($type) {
    global $db;
    
    try {
        $type = $db->real_escape_string($type);
        $query = "SELECT * FROM departments WHERE type = '$type' ORDER BY name";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $departments
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function getProgramsByDepartment($departmentId) {
    global $db;
    
    try {
        $departmentId = (int)$departmentId;
        $query = "SELECT * FROM programs WHERE department_id = $departmentId ORDER BY name";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $programs
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function getAllProgramsWithDepartments() {
    global $db;
    
    try {
        $query = "SELECT p.*, d.name as department_name 
                 FROM programs p 
                 JOIN departments d ON p.department_id = d.id 
                 ORDER BY d.name, p.name";
        $result = $db->query($query);
        
        if (!$result) {
            throw new Exception('Database query failed: ' . $db->error);
        }
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        
        return [
            'status' => 'success',
            'data' => $programs
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function addDepartment($name, $type) {
    global $db;
    
    try {
        $name = $db->real_escape_string($name);
        $type = $db->real_escape_string($type);
        
        $query = "INSERT INTO departments (name, type) VALUES ('$name', '$type')";
        
        if ($db->query($query)) {
            return [
                'status' => 'success',
                'message' => 'Department added successfully',
                'id' => $db->insert_id
            ];
        } else {
            throw new Exception('Failed to add department: ' . $db->error);
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function addProgram($departmentId, $name, $code, $level) {
    global $db;
    
    try {
        $departmentId = (int)$departmentId;
        $name = $db->real_escape_string($name);
        $code = $db->real_escape_string($code);
        $level = $db->real_escape_string($level);
        
        $query = "INSERT INTO programs (department_id, name, code, level) VALUES ($departmentId, '$name', '$code', '$level')";
        
        if ($db->query($query)) {
            return [
                'status' => 'success',
                'message' => 'Program added successfully',
                'id' => $db->insert_id
            ];
        } else {
            throw new Exception('Failed to add program: ' . $db->error);
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function updateDepartment($id, $name, $type) {
    global $db;
    
    try {
        $id = (int)$id;
        $name = $db->real_escape_string($name);
        $type = $db->real_escape_string($type);
        
        $query = "UPDATE departments SET name = '$name', type = '$type' WHERE id = $id";
        
        if ($db->query($query)) {
            return [
                'status' => 'success',
                'message' => 'Department updated successfully'
            ];
        } else {
            throw new Exception('Failed to update department: ' . $db->error);
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function updateProgram($id, $departmentId, $name, $code, $level) {
    global $db;
    
    try {
        $id = (int)$id;
        $departmentId = (int)$departmentId;
        $name = $db->real_escape_string($name);
        $code = $db->real_escape_string($code);
        $level = $db->real_escape_string($level);
        
        $query = "UPDATE programs SET department_id = $departmentId, name = '$name', code = '$code', level = '$level' WHERE id = $id";
        
        if ($db->query($query)) {
            return [
                'status' => 'success',
                'message' => 'Program updated successfully'
            ];
        } else {
            throw new Exception('Failed to update program: ' . $db->error);
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Prevent caching
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    
    $response = [];
    
    switch ($_POST['action']) {
        case 'get_all_departments':
            $response = getAllDepartments();
            break;
        case 'get_departments_by_type':
            $type = $_POST['type'];
            $response = getDepartmentsByType($type);
            break;
        case 'get_programs_by_department':
            $departmentId = $_POST['department_id'];
            $response = getProgramsByDepartment($departmentId);
            break;
        case 'get_all_programs_with_departments':
            $response = getAllProgramsWithDepartments();
            break;
        case 'add_department':
            $name = $_POST['name'];
            $type = $_POST['type'];
            $response = addDepartment($name, $type);
            break;
        case 'add_program':
            $departmentId = $_POST['department_id'];
            $name = $_POST['name'];
            $code = $_POST['code'];
            $level = $_POST['level'];
            $response = addProgram($departmentId, $name, $code, $level);
            break;
        case 'update_department':
            $id = $_POST['id'];
            $name = $_POST['name'];
            $type = $_POST['type'];
            $response = updateDepartment($id, $name, $type);
            break;
        case 'update_program':
            $id = $_POST['id'];
            $departmentId = $_POST['department_id'];
            $name = $_POST['name'];
            $code = $_POST['code'];
            $level = $_POST['level'];
            $response = updateProgram($id, $departmentId, $name, $code, $level);
            break;
        default:
            $response = [
                'status' => 'error',
                'message' => 'Invalid action specified'
            ];
    }
    
    echo json_encode($response);
    exit;
}
?>