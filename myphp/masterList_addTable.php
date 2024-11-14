<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "masterlistDB"; // Ensure this matches your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle table creation request
if (isset($_POST['create_table'])) {
    // Retrieve and sanitize the table name from the form input
    $tableName = $_POST['table_name'];
    $tableName = preg_replace("/[^a-zA-Z0-9_]/", "", $tableName);

    // Define the SQL query to create the table, omitting a default value for `status`
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        registered_number VARCHAR(10) UNIQUE NOT NULL,
        status VARCHAR(50) NULL,
        studentname VARCHAR(100) NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        lrn VARCHAR(20) NOT NULL,
        time_in TIME,
        deadline TIME,
        date_created DATE DEFAULT CURRENT_DATE
    )";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "<p>Table '$tableName' created successfully in master list database!</p>";
    } else {
        echo "<p>Error creating table. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a New Table in Master List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Add basic styling */
        body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; padding: 20px; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 8px; color: #555; }
        input[type="text"] { width: 100%; padding: 8px; margin-bottom: 16px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        p { text-align: center; color: #333; }
    </style>
</head>
<body>

<h2>Create a New Table in Master List</h2>
<form action="" method="POST">
    <label for="table_name">Enter Table Name (e.g., Section Name):</label>
    <input type="text" id="table_name" name="table_name" required placeholder="Enter table name (e.g., Diamond)">
    <button type="submit" name="create_table">Create Table</button>
</form>

</body>
</html>
