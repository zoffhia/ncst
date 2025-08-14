<?php
    error_reporting(0);
    ini_set('display_errors', 0);

    ob_start();
    
    try {
        include('../includes/config.php');

        if (!isset($db) || !$db) {
            throw new Exception('Database connection failed');
        }
        
        function getTotalUsers() {
            global $db;
            
            $total = 0;

            $studentQuery = "SELECT COUNT(*) as count FROM student";
            $studentResult = $db->query($studentQuery);
            if ($studentResult) {
                $studentCount = $studentResult->fetch_assoc()['count'];
                $total += $studentCount;
            }

            $employeeQuery = "SELECT COUNT(*) as count FROM employee";
            $employeeResult = $db->query($employeeQuery);
            if ($employeeResult) {
                $employeeCount = $employeeResult->fetch_assoc()['count'];
                $total += $employeeCount;
            }

            $adminQuery = "SELECT COUNT(*) as count FROM admin";
            $adminResult = $db->query($adminQuery);
            if ($adminResult) {
                $adminCount = $adminResult->fetch_assoc()['count'];
                $total += $adminCount;
            }
            
            return $total;
        }

        function getStudentCount() {
            global $db;
            
            $query = "SELECT COUNT(*) as count FROM student";
            $result = $db->query($query);
            
            if ($result) {
                return $result->fetch_assoc()['count'];
            }
            
            return 0;
        }

        function getRecordsCount() {
            global $db;
            
            $query = "SELECT COUNT(*) as count FROM employee WHERE user_role = 'custodian' OR user_role = 'records'";
            $result = $db->query($query);
            
            if ($result) {
                return $result->fetch_assoc()['count'];
            }
            
            return 0;
        }

        function getRegistrarCount() {
            global $db;
            
            $query = "SELECT COUNT(*) as count FROM employee WHERE user_role = 'registrar'";
            $result = $db->query($query);
            
            if ($result) {
                return $result->fetch_assoc()['count'];
            }
            
            return 0;
        }


        function getTreasuryCount() {
            global $db;
            
            $query = "SELECT COUNT(*) as count FROM employee WHERE user_role = 'treasury'";
            $result = $db->query($query);
            
            if ($result) {
                return $result->fetch_assoc()['count'];
            }
            
            return 0;
        }

        function getAllUsers() {
            global $db;
            
            $users = [];

            $employeeQuery = "SELECT
                'employee' as user_type,
                employeeNo as id_no,
                firstName,
                midName,
                lastName,
                suffix,
                CONCAT(firstName, ' ', COALESCE(midName, ''), ' ', lastName, ' ', COALESCE(suffix, '')) as full_name,
                birthDate,
                email,
                user_role as role,
                department,
                dateCreated,
                CASE
                    WHEN status = 1 THEN 'Active'
                    ELSE 'Inactive'
                END as status
            FROM employee
            ORDER BY employeeNo ASC";
            
            $employeeResult = $db->query($employeeQuery);
            if ($employeeResult) {
                while ($row = $employeeResult->fetch_assoc()) {
                    $users[] = $row;
                }
            }

            $adminQuery = "SELECT
                'admin' as user_type,
                adminNo as id_no,
                firstName,
                midName,
                lastName,
                suffix,
                CONCAT(firstName, ' ', COALESCE(midName, ''), ' ', lastName, ' ', COALESCE(suffix, '')) as full_name,
                birthDate,
                email,
                user_role as role,
                department,
                dateCreated,
                CASE
                    WHEN status = 1 THEN 'Active'
                    ELSE 'Inactive'
                END as status
            FROM admin
            ORDER BY adminNo ASC";
            
            $adminResult = $db->query($adminQuery);
            if ($adminResult) {
                while ($row = $adminResult->fetch_assoc()) {
                    $users[] = $row;
                }
            }

            usort($users, function($a, $b) {
                return strcmp($a['id_no'], $b['id_no']);
            });
            
            return $users;
        }

        function addUser($userData) {
            global $db;
            
            $response = ['status' => 'error', 'message' => 'Unknown error occurred'];
            
            try {
                $userType = $userData['userType'];
                $firstName = $db->real_escape_string($userData['firstName']);
                $midName = $db->real_escape_string($userData['midName']);
                $lastName = $db->real_escape_string($userData['lastName']);
                $suffix = $db->real_escape_string($userData['suffix']);
                $birthDate = $db->real_escape_string($userData['birthDate']);
                $email = $db->real_escape_string($userData['email']);
                $role = $db->real_escape_string($userData['role']);
                $department = $db->real_escape_string($userData['department']);

                $password = password_hash($userData['password'], PASSWORD_DEFAULT);
                
                if ($userType === 'employee') {
                    $employeeNo = $db->real_escape_string($userData['empID']);
                                    // Generate next employeeNo in backend
                    $currentYear = date('Y');
                    $lastEmpQuery = "SELECT employeeNo FROM employee WHERE employeeNo LIKE '$currentYear-%' ORDER BY employeeNo DESC LIMIT 1";
                    $lastEmpResult = $db->query($lastEmpQuery);

                    if ($lastEmpResult && $lastEmpResult->num_rows > 0) {
                        $lastEmp = $lastEmpResult->fetch_assoc();
                        $lastNumber = intval(substr($lastEmp['employeeNo'], 5));
                        $newNumber = $lastNumber + 1;
                    } else {
                        $newNumber = 1;
                    }
                    $employeeNo = sprintf('%s-%05d', $currentYear, $newNumber);

                    $userRole = '';
                    switch ($role) {
                        case 'registrar':
                            $userRole = 'registrar';
                            break;
                        case 'treasury':
                            $userRole = 'treasury';
                            break;
                        case 'department head':
                            $userRole = 'department_head';
                            break;
                        case 'custodian':
                            $userRole = 'custodian';
                            break;
                        case 'records':
                            $userRole = 'records';
                            break;
                        default:
                            $userRole = $role;
                    }
                    
                    $query = "INSERT INTO employee (employeeNo, firstName, midName, lastName, suffix, birthDate, email, user_role, department, password, dateCreated) 
                             VALUES ('$employeeNo', '$firstName', '$midName', '$lastName', '$suffix', '$birthDate', '$email', '$userRole', '$department', '$password', NOW())";
                    
                    if ($db->query($query)) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Employee added successfully!',
                            'empId' => $employeeNo // Return the generated employeeNo
                        ];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Failed to add employee: ' . $db->error];
                    }
                }elseif ($userType === 'admin') {
                    // Generate next adminNo in backend
                    $currentYear = date('Y');
                    $lastAdminQuery = "SELECT adminNo FROM admin WHERE adminNo LIKE '$currentYear-%' ORDER BY adminNo DESC LIMIT 1";
                    $lastAdminResult = $db->query($lastAdminQuery);

                    if ($lastAdminResult && $lastAdminResult->num_rows > 0) {
                        $lastAdmin = $lastAdminResult->fetch_assoc();
                        $lastNumber = intval(substr($lastAdmin['adminNo'], 5));
                        $newNumber = $lastNumber + 1;
                    } else {
                        $newNumber = 1;
                    }
                    $adminNo = sprintf('%s-%05d', $currentYear, $newNumber);

                    $query = "INSERT INTO admin (adminNo, firstName, midName, lastName, suffix, birthDate, email, user_role, department, password, dateCreated) 
                            VALUES ('$adminNo', '$firstName', '$midName', '$lastName', '$suffix', '$birthDate', '$email', '$role', '$department', '$password', NOW())";
                    if ($db->query($query)) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Admin added successfully!',
                            'adminNo' => $adminNo // Return the generated adminNo
                        ];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Failed to add admin: ' . $db->error];
                    }
                } else {
                    $response = ['status' => 'error', 'message' => 'Invalid user type'];
                }
                
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()];
            }
            
            return $response;
        }

        function toggleUserStatus($userType, $userId, $newStatus) {
            global $db;
            
            $response = ['status' => 'error', 'message' => 'Unknown error occurred'];
            
            try {
                $userId = $db->real_escape_string($userId);
                $newStatus = $db->real_escape_string($newStatus);
                $statusValue = ($newStatus === 'Active') ? 1 : 0;
                
                if ($userType === 'employee') {
                    $query = "UPDATE employee SET status = $statusValue WHERE employeeNo = '$userId'";
                } elseif ($userType === 'admin') {
                    $query = "UPDATE admin SET status = $statusValue WHERE adminNo = '$userId'";
                } else {
                    $response = ['status' => 'error', 'message' => 'Invalid user type'];
                    return $response;
                }
                
                if ($db->query($query)) {
                    $action = ($newStatus === 'Active') ? 'activated' : 'deactivated';
                    $response = ['status' => 'success', 'message' => "User $action successfully!"];
                } else {
                    $response = ['status' => 'error', 'message' => 'Failed to update user status: ' . $db->error];
                }
                
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()];
            }
            
            return $response;
        }

        function updateUser($userData) {
            global $db;

            
            $response = ['status' => 'error', 'message' => 'Unknown error occurred'];
            
            try {
                if (!isset($userData['userType']) || !isset($userData['userId'])) {
                    return ['status' => 'error', 'message' => 'Missing required fields: userType or userId'];
                }
                
                $userType = $userData['userType'];
                $userId = $userData['userId'];

                $requiredFields = ['firstName', 'lastName', 'email', 'role', 'department', 'birthDate'];
                foreach ($requiredFields as $field) {
                    if (!isset($userData[$field])) {
                        return ['status' => 'error', 'message' => 'Missing required field: ' . $field];
                    }
                }
                
                $firstName = $db->real_escape_string($userData['firstName']);
                $midName = isset($userData['midName']) ? $db->real_escape_string($userData['midName']) : '';
                $lastName = $db->real_escape_string($userData['lastName']);
                $suffix = isset($userData['suffix']) ? $db->real_escape_string($userData['suffix']) : '';
                $birthDate = $db->real_escape_string($userData['birthDate']);
                $email = $db->real_escape_string($userData['email']);
                $role = $db->real_escape_string($userData['role']);
                $department = $db->real_escape_string($userData['department']);
                
                if ($userType === 'employee') {
                    $userRole = '';
                    switch ($role) {
                        case 'registrar':
                            $userRole = 'registrar';
                            break;
                        case 'treasury':
                            $userRole = 'treasury';
                            break;
                        case 'department head':
                            $userRole = 'department_head';
                            break;
                        case 'custodian':
                            $userRole = 'custodian';
                            break;
                        case 'records':
                            $userRole = 'records';
                            break;
                        default:
                            $userRole = $role;
                    }
                    
                    $query = "UPDATE employee SET
                                firstName = '$firstName',
                                midName = '$midName',
                                lastName = '$lastName',
                                suffix = '$suffix',
                                birthDate = '$birthDate',
                                email = '$email',
                                user_role = '$userRole',
                                department = '$department'
                              WHERE employeeNo = '$userId'";
                    
                    if ($db->query($query)) {
                        $response = ['status' => 'success', 'message' => 'Employee updated successfully!'];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Failed to update employee: ' . $db->error];
                    }
                } elseif ($userType === 'admin') {
                    $query = "UPDATE admin SET
                                firstName = '$firstName',
                                midName = '$midName',
                                lastName = '$lastName',
                                suffix = '$suffix',
                                birthDate = '$birthDate',
                                email = '$email',
                                user_role = '$role',
                                department = '$department'
                              WHERE adminNo = '$userId'";
                    
                    if ($db->query($query)) {
                        $response = ['status' => 'success', 'message' => 'Admin updated successfully!'];
                    } else {
                        $response = ['status' => 'error', 'message' => 'Failed to update admin: ' . $db->error];
                    }
                } else {
                    $response = ['status' => 'error', 'message' => 'Invalid user type'];
                }
                
            } catch (Exception $e) {
                $response = ['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()];
            }
            
            return $response;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            ob_clean();

            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Content-Type: application/json');
            
            switch ($_POST['action']) {
                case 'get_user_counts':
                    $data = [
                        'total_users' => getTotalUsers(),
                        'students' => getStudentCount(),
                        'records' => getRecordsCount(),
                        'registrars' => getRegistrarCount(),
                        'treasury' => getTreasuryCount()
                    ];
                    echo json_encode($data);
                    break;
                    
                case 'get_all_users':
                    $users = getAllUsers();
                    echo json_encode(['status' => 'success', 'users' => $users]);
                    break;
                    
                case 'add_user':
                    $result = addUser($_POST);
                    echo json_encode($result);
                    break;
                    
                case 'toggle_user_status':
                    $result = toggleUserStatus($_POST['userType'], $_POST['userId'], $_POST['newStatus']);
                    echo json_encode($result);
                    break;
                    
                case 'update_user':
                    $result = updateUser($_POST);
                    echo json_encode($result);
                    break;
                    
                default:
                    echo json_encode(['error' => 'Invalid action']);
            }
            exit;
        }
        
    } catch (Exception $e) {
        ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'PHP Error: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
        exit;
    }

    ob_end_clean();
?> 