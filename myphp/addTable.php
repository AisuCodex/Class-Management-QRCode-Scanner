<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create and Display Tables with Deadline</title>
</head>
<body>
    <h2>Create a New Table</h2>
    <form action="" method="POST">
        <label for="table_name">Enter Table Name:</label>
        <input type="text" id="table_name" name="table_name" required>
        <button type="submit" name="create_table">Create Table</button>
    </form>

    <?php
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

    // Handle table creation request
    if (isset($_POST['create_table'])) {
        $tableName = $_POST['table_name'];

        // SQL query to create the table with specified columns
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            status VARCHAR(50),
            studentname VARCHAR(100) NOT NULL,
            gender ENUM('Male', 'Female', 'Other') NOT NULL,
            lrn VARCHAR(20) NOT NULL,
            time_in TIME,
            deadline TIME
        )";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Table '$tableName' created successfully!</p>";
        } else {
            echo "<p>Error creating table: " . $conn->error . "</p>";
        }
    }

    // Handle deadline update
    if (isset($_POST['set_deadline'])) {
        $tableName = $_POST['table_name'];
        $deadline = $_POST['deadline'];

        $sql = "UPDATE $tableName SET deadline = '$deadline'";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Deadline set to $deadline for table '$tableName'.</p>";
        } else {
            echo "<p>Error setting deadline: " . $conn->error . "</p>";
        }
    }

    // Display all tables in the database
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
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
                    </tr>";

            // Fetch existing rows and apply 'late' status if time_in is after the deadline
            $dataResult = $conn->query("SELECT *, IF(time_in > deadline, 'Late', 'On time') AS status FROM $currentTable");
            if ($dataResult->num_rows > 0) {
                while ($dataRow = $dataResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $dataRow['id'] . "</td>
                            <td>" . $dataRow['status'] . "</td>
                            <td>" . $dataRow['studentname'] . "</td>
                            <td>" . $dataRow['gender'] . "</td>
                            <td>" . $dataRow['lrn'] . "</td>
                            <td>" . $dataRow['time_in'] . "</td>
                            <td>" . $dataRow['deadline'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='7' style='text-align:center;'>No data yet</td>
                      </tr>";
            }
            echo "</table><br>";

            // Form to set a deadline for the table
            echo "<form action='' method='POST'>
                    <input type='hidden' name='table_name' value='$currentTable'>
                    <label for='deadline'>Set Deadline (HH:MM:SS):</label>
                    <input type='time' name='deadline' required>
                    <button type='submit' name='set_deadline'>Set Deadline</button>
                  </form><br>";
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }

    // Close connection
    $conn->close();
    ?>

    <!-- Button to redirect to QR code scanner page -->
    <br>
    <button onclick="window.location.href='QRScanner.php'">Go to QR Code Scanner</button>
</body>
</html>
