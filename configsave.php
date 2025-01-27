<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Database configuration for table_db
$servername = "localhost";
$username = "u193875898_table_db";
$password = "Hesoyam.com2024";
$dbname = "u193875898_table_db";

// Create connection for table_db
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection for masterlist database - using separate credentials
$masterDbname = "u193875898_masterlistdb";
$masterUsername = "u193875898_masterlistdb";
$masterPassword = "Hesoyam.com2024";

$masterConn = new mysqli($servername, $masterUsername, $masterPassword, $masterDbname);

// Check masterlist connection
if ($masterConn->connect_error) {
    die("Master database connection failed: " . $masterConn->connect_error);
}

// Connection for dashboard database - using separate credentials
$dashboardDbname = "u193875898_dashboard_db";
$dashboardUsername = "u193875898_dashboard_db";
$dashboardPassword = "Hesoyam.com2024";

$dashboardConn = new mysqli($servername, $dashboardUsername, $dashboardPassword, $dashboardDbname);

// Check dashboard connection
if ($dashboardConn->connect_error) {
    die("Dashboard database connection failed: " . $dashboardConn->error);
}

// Handle table creation request with data copy, deadline, and section
if (isset($_POST['create_table'])) {
    $tableName = $_POST['table_name'];
    $deadline = $_POST['deadline'];
    $copyFromTable = $_POST['copy_from_table'];

    // Check if table already exists
    $tableExistsQuery = "SHOW TABLES LIKE '$tableName'";
    $tableExists = $conn->query($tableExistsQuery);
    
    if ($tableExists->num_rows > 0) {
        echo "<script>alert('Error: Table \"$tableName\" already exists! Please choose a different name.');</script>";
    } else {
        // Convert deadline to 24-hour format for storage
        $deadline = date('H:i:s', strtotime($deadline));

        // Create new table with specified structure (including registered_number and section)
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            section VARCHAR(100) NOT NULL, -- New section field
            status VARCHAR(50) NULL DEFAULT '', -- Leave status blank for QR code scanning check
            studentname VARCHAR(100) NOT NULL,
            gender ENUM('Male', 'Female', 'Other') NOT NULL,
            lrn VARCHAR(20) NOT NULL,
            registered_number VARCHAR(6) NOT NULL, -- Field for registered number
            time_in TIME,
            deadline TIME DEFAULT '$deadline', -- Use TIME type for consistent storage
            date_created DATE DEFAULT CURRENT_DATE
        )";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Table '$tableName' with deadline '$deadline' created successfully!</p>";

            // Copy data from selected table in masterlistdb using masterConn instead of conn
            $selectSql = "SELECT section, studentname, gender, lrn, registered_number 
                         FROM $copyFromTable";
            
            $result = $masterConn->query($selectSql);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $section = $conn->real_escape_string($row['section']);
                    $studentname = $conn->real_escape_string($row['studentname']);
                    $gender = $conn->real_escape_string($row['gender']);
                    $lrn = $conn->real_escape_string($row['lrn']);
                    $reg_num = $conn->real_escape_string($row['registered_number']);
                    
                    $insertSql = "INSERT INTO $tableName (section, studentname, gender, lrn, registered_number) 
                                 VALUES ('$section', '$studentname', '$gender', '$lrn', '$reg_num')";
                    
                    if (!$conn->query($insertSql)) {
                        echo "<p>Error inserting row: " . $conn->error . "</p>";
                    }
                }
                echo "<p>Data copied from '$copyFromTable' to '$tableName' successfully!</p>";
            } else {
                echo "<p>Error reading from source table: " . $masterConn->error . "</p>";
            }
        } else {
            echo "<p>Error creating table: " . $conn->error . "</p>";
        }
    }
}

// Handle delete table request
if (isset($_POST['delete_table']) && isset($_POST['table_name'])) {
    $tableName = $dashboardConn->real_escape_string($_POST['table_name']);
    
    // Check if table exists in dashboard database
    $checkTable = "SHOW TABLES LIKE '$tableName'";
    $tableExists = $dashboardConn->query($checkTable);
    
    if ($tableExists && $tableExists->num_rows > 0) {
        $sql = "DROP TABLE `$tableName`";
        if ($dashboardConn->query($sql) === TRUE) {
            echo "<script>alert('Table \"$tableName\" deleted successfully from dashboard!');</script>";
        } else {
            echo "<script>alert('Error deleting table from dashboard: " . $dashboardConn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Table \"$tableName\" not found in dashboard database!');</script>";
    }
}

// Handle QR code scan and update status logic
if (isset($_POST['scan_qr'])) {
    $studentId = $_POST['student_id'];
    $scannedTime = $_POST['scanned_time'];
    $tableName = $_POST['table_name'];

    // Convert scanned time to 24-hour format for storage
    $scannedTime = date('H:i:s', strtotime($scannedTime));

    // Retrieve the deadline from the database for the specific student
    $query = "SELECT deadline, time_in FROM $tableName WHERE registered_number = '$studentId'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deadline = $row['deadline'];

        // Determine status based on scan time
        $status = strtotime($scannedTime) > strtotime($deadline) ? 'Late' : 'Present';

        // Update the status and time_in after QR code scan
        $updateQuery = "UPDATE $tableName SET status = '$status', time_in = '$scannedTime' WHERE registered_number = '$studentId'";
        
        if ($conn->query($updateQuery) === TRUE) {
            echo "QR code scanned and status updated to '$status'.";
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Student not found!";
    }
}

// Handle Finalize Button Click
if (isset($_POST['finalize_table'])) {
    $tableName = $_POST['table_name'];

    // Update the status of students who have no time_in value to 'Absent'
    $updateQuery = "UPDATE $tableName SET status = 'Absent' WHERE time_in IS NULL OR time_in = ''";
    
    if ($conn->query($updateQuery) === TRUE) {
        echo "<p>Status has been updated to 'Absent' for students without time_in in '$tableName'.</p>";
    } else {
        echo "<p>Error updating status: " . $conn->error . "</p>";
    }
}

// Handle "Send to Dashboard" button click
if (isset($_POST['send_to_dashboard'])) {   
    $tableName = $_POST['table_name'];

    // Check if table exists in dashboard using dashboardConn
    $checkDashboardTable = "SHOW TABLES LIKE '$tableName'";
    $dashboardTableExists = $dashboardConn->query($checkDashboardTable);

    if ($dashboardTableExists->num_rows > 0) {
        echo "<script>alert('Error: Table \"$tableName\" already exists in the dashboard! Please use a different name.');</script>";
    } else {
        // Get the structure of the source table
        $getStructure = "SHOW CREATE TABLE $tableName";
        $structureResult = $conn->query($getStructure);
        
        if ($structureResult) {
            $tableStructure = $structureResult->fetch_array()[1];
            
            // Create the table in dashboard database
            if ($dashboardConn->query($tableStructure)) {
                // Copy data row by row
                $selectData = "SELECT * FROM $tableName";
                $dataResult = $conn->query($selectData);
                
                if ($dataResult) {
                    $success = true;
                    while ($row = $dataResult->fetch_assoc()) {
                        $columns = array_keys($row);
                        $values = array_map(function($value) use ($dashboardConn) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return "'" . $dashboardConn->real_escape_string($value) . "'";
                        }, array_values($row));
                        
                        $insertSql = "INSERT INTO $tableName (" . implode(',', $columns) . ") 
                                    VALUES (" . implode(',', $values) . ")";
                        
                        if (!$dashboardConn->query($insertSql)) {
                            $success = false;
                            echo "<p class='error'>Error copying data: " . $dashboardConn->error . "</p>";
                            break;
                        }
                    }
                    
                    if ($success) {
                        echo "<script>alert('Table successfully sent to dashboard!');</script>";
                    }
                } else {
                    echo "<p>Error reading source data: " . $conn->error . "</p>";
                }
            } else {
                echo "<p>Error creating table in dashboard: " . $dashboardConn->error . "</p>";
            }
        } else {
            echo "<p>Error getting table structure: " . $conn->error . "</p>";
        }
    }
}

// Retrieve search query if available
$searchQuery = isset($_POST['search_query']) ? $_POST['search_query'] : '';
?>
