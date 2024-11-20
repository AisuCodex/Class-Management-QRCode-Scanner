<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u193875898_dashboard_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the table name is provided
if (isset($_GET['table'])) {
    $tableName = $_GET['table'];

    // Sanitize table name
    $tableName = $conn->real_escape_string($tableName);

    // Fetch data from the table
    $result = $conn->query("SELECT * FROM `$tableName`");

    if ($result->num_rows > 0) {
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Fetch column names
        $columns = array_keys($result->fetch_assoc());
        fputcsv($output, $columns);

        // Fetch data rows
        $result->data_seek(0); // Reset pointer to start of result set
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        echo "No data available in the table.";
    }
} else {
    echo "Table not specified.";
}

// Close the connection
$conn->close();
?>
