<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database configuration for target database `u193875898_table_db`
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u193875898_table_db"; // Replace with your actual database name

// Create connection for target database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection for target database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Database configuration for source database `u193875898_masterlistdb`
$masterDbname = "u193875898_masterlistdb"; // Replace with the actual name of your master database
$masterConn = new mysqli($servername, $username, $password, $masterDbname);

// Check connection for source database
if ($masterConn->connect_error) {
    die("Connection to master database failed: " . $masterConn->connect_error);
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

            // Copy data from selected table in `u193875898_masterlistdb` to the newly created table, including section
            $copySql = "INSERT INTO $tableName (section, studentname, gender, lrn, registered_number) 
                        SELECT section, studentname, gender, lrn, registered_number 
                        FROM $masterDbname.$copyFromTable";  // Ensure section is copied

            if ($conn->query($copySql) === TRUE) {
                echo "<p>Data copied from '$copyFromTable' to '$tableName' successfully!</p>";
            } else {
                echo "<p>Error copying data: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error creating table: " . $conn->error . "</p>";
        }
    }
}

// Handle delete table request
if (isset($_POST['delete_table'])) {
    $tableName = $_POST['table_name'];
    $sql = "DROP TABLE IF EXISTS $tableName";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Table '$tableName' deleted successfully!</p>";
    } else {
        echo "<p>Error deleting table: " . $conn->error . "</p>";
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

    // Check if table exists in `u193875898_dashboard_db`
    $dashboardDbConn = new mysqli($servername, $username, $password, "u193875898_dashboard_db");
    if ($dashboardDbConn->connect_error) {
        die("Connection to dashboard database failed: " . $dashboardDbConn->connect_error);
    }

    // Check if table already exists in dashboard
    $checkDashboardTable = "SHOW TABLES FROM `u193875898_dashboard_db` LIKE '$tableName'";
    $dashboardTableExists = $dashboardDbConn->query($checkDashboardTable);

    if ($dashboardTableExists->num_rows > 0) {
        echo "<script>alert('Error: Table \"$tableName\" already exists in the dashboard! Please use a different name.');</script>";
    } else {
        // Check if table exists in `u193875898_table_db`
        $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
        $checkResult = $conn->query($checkTableQuery);

        if ($checkResult && $checkResult->num_rows > 0) {
            // Create the same table structure in `u193875898_dashboard_db`
            $createTableQuery = "CREATE TABLE IF NOT EXISTS `u193875898_dashboard_db`.`$tableName` LIKE `u193875898_table_db`.`$tableName`";
            if ($dashboardDbConn->query($createTableQuery) === TRUE) {
                // Copy all data from `u193875898_table_db` to `u193875898_dashboard_db`
                $copyDataQuery = "INSERT INTO `u193875898_dashboard_db`.`$tableName` SELECT * FROM `u193875898_table_db`.`$tableName`";
                if ($dashboardDbConn->query($copyDataQuery) === TRUE) {
                    echo "<p>Table '$tableName' successfully sent to the dashboard database!</p>";
                } else {
                    echo "<p>Error copying data: " . $dashboardDbConn->error . "</p>";
                }
            } else {
                echo "<p>Error creating table in dashboard: " . $dashboardDbConn->error . "</p>";
            }
        } else {
            echo "<p>Error: Source table not found in the main database.</p>";
        }
    }
    $dashboardDbConn->close();
}

// Retrieve search query if available
$searchQuery = isset($_POST['search_query']) ? $_POST['search_query'] : '';
?>
