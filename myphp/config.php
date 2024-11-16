<?php
session_start();

// Database configuration for target database `table_db`
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "table_db"; // Replace with your actual database name

// Create connection for target database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection for target database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Database configuration for source database `masterlistDB`
$masterDbname = "masterlistDB"; // Replace with the actual name of your master database
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

    // Ensure deadline format is valid (HH:MM:SS)
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

        // Copy data from selected table in `masterlistDB` to the newly created table, including section
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
    $studentId = $_POST['student_id']; // Student's unique identifier (e.g., id or registered_number)
    $scannedTime = $_POST['scanned_time']; // Time when the QR code was scanned
    $tableName = $_POST['table_name']; // The table where data needs to be updated

    // Retrieve the deadline from the database for the specific student
    $query = "SELECT deadline, time_in FROM $tableName WHERE registered_number = '$studentId'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deadline = $row['deadline']; // Deadline set for the student
        $timeIn = $row['time_in']; // Student's time_in (if available)

        // Set default status to 'On Time'
        $status = '';

        // If the student scanned the QR code later than the deadline, mark as 'Late'
        if ($scannedTime && strtotime($scannedTime) > strtotime($deadline)) {
            $status = 'Late';
        }

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

// Retrieve search query if available
$searchQuery = isset($_POST['search_query']) ? $_POST['search_query'] : '';
?>
