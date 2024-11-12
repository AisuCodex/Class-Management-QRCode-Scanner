<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "table_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle table creation request with deadline
if (isset($_POST['create_table'])) {
    $tableName = $_POST['table_name'];
    $deadline = $_POST['deadline'];

    // Ensure deadline format is valid (HH:MM:SS)
    $deadline = date('H:i:s', strtotime($deadline));

    $sql = "CREATE TABLE IF NOT EXISTS $tableName (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        status VARCHAR(50),
        studentname VARCHAR(100) NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        lrn VARCHAR(20) NOT NULL,
        time_in TIME,
        deadline TIME DEFAULT '$deadline',
        date_created DATE DEFAULT CURRENT_DATE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Table '$tableName' with deadline '$deadline' created successfully!</p>";
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

// Retrieve search query if available
$searchQuery = isset($_POST['search_query']) ? $_POST['search_query'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create, Display, Edit, and Delete Tables with Deadline</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/addTable.css">
</head>
<body style="background-color: #f5f5f5;">
    <h2>Create a New Table with Deadline</h2>
    <form action="" method="POST">
        <label for="table_name">Enter Table Name:</label>
        <input type="text" id="table_name" name="table_name" required>
        <br>
        <label for="deadline">Set Deadline (HH:MM:SS):</label>
        <input type="time" id="deadline" name="deadline" required>
        <br>
        <button type="submit" name="create_table">Create Table with Deadline</button>
    </form>
    <button onclick="window.location.href='QRScanner.php'">Go to QR Code Scanner</button>
    <!-- Search form -->
    <h3>Search for a Table</h3>
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search table name..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php
    // Display all tables that match the search query
    if ($searchQuery) {
        $searchPattern = "%" . $conn->real_escape_string($searchQuery) . "%";
        $result = $conn->query("SHOW TABLES LIKE '$searchPattern'");
    } else {
        $result = $conn->query("SHOW TABLES");
    }

    if ($result === false) {
        echo "<p>Error retrieving tables: " . $conn->error . "</p>";
    } else if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $currentTable = $row[0];

            echo "<h3>Table Structure for '$currentTable'</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Id</th>
                        <th>Status</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>LRN</th>
                        <th>Time In</th>
                        <th>Deadline</th>
                        <th>Date Created</th>
                    </tr>";

            // Fetch existing rows and apply 'late' status if time_in is after the deadline
            $dataResult = $conn->query("SELECT *, IF(time_in > deadline, 'Late', 'On time') AS status FROM $currentTable");

            if ($dataResult === false) {
                echo "<p>Error retrieving data from '$currentTable': " . $conn->error . "</p>";
            } else if ($dataResult->num_rows > 0) {
                while ($dataRow = $dataResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $dataRow['id'] . "</td>
                            <td>" . $dataRow['status'] . "</td>
                            <td>" . $dataRow['studentname'] . "</td>
                            <td>" . $dataRow['gender'] . "</td>
                            <td>" . $dataRow['lrn'] . "</td>
                            <td>" . $dataRow['time_in'] . "</td>
                            <td>" . $dataRow['deadline'] . "</td>
                            <td>" . $dataRow['date_created'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='8' style='text-align:center;'>No data yet</td>
                      </tr>";
            }
            echo "</table><br>";

            // Form to delete the table
            echo "<form action='' method='POST' onsubmit=\"return confirm('Are you sure you want to delete this table?');\">
                    <input type='hidden' name='table_name' value='$currentTable'>
                    <button type='submit' name='delete_table' style='color: red;'>Delete Table</button>
                  </form><br>";
        }
    } else {
        echo "<p>No tables found in the database matching '$searchQuery'.</p>";
    }
    ?>
</body>
</html>
