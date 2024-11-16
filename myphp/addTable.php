<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create, Display, Edit, and Delete Tables with Deadline</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/addTable.css">

    <script type="text/javascript">
        function confirmDelete() {
            return confirm("Are you sure you want to delete this table?");
        }
        function confirmSend() {
            return confirm("Are you sure you want to send this table to the dashboard?");
        }
    </script>
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

        <!-- Dropdown to select table from masterlistDB to copy data from -->
        <label for="copy_from_table">Select Master List Table to Copy From:</label>
        <select id="copy_from_table" name="copy_from_table" required>
            <option value="">Select a table</option>
            <?php
            // Fetch tables from masterlistDB and populate dropdown
            $tablesResult = $masterConn->query("SHOW TABLES");
            if ($tablesResult->num_rows > 0) {
                while ($tableRow = $tablesResult->fetch_array()) {
                    echo "<option value='" . $tableRow[0] . "'>" . $tableRow[0] . "</option>";
                }
            }
            ?>
        </select>
        <br>
        <button type="submit" name="create_table">Create Table with Deadline and Copy Data</button>
    </form>
    <button onclick="window.location.href='QRScanner.php'">Go to QR Code Scanner</button>

    <!-- Search form -->
    <h3>Search for a Table</h3>
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search table name..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php
    // Handle Finalize Button Click
    if (isset($_POST['finalize_table'])) {
        $tableName = $_POST['table_name'];

        // Update the status of students who have no time_in value to 'Absent'
        $updateQuery = "UPDATE $tableName SET status = 'Absent' WHERE time_in IS NULL OR time_in = ''";

        // Execute the query
        if ($conn->query($updateQuery) === TRUE) {
            echo "<p>Status has been updated to 'Absent' for students without time_in in '$tableName'.</p>";
        } else {
            echo "<p>Error updating status: " . $conn->error . "</p>";
        }
    }

    // Handle "Send to Dashboard" button click
    if (isset($_POST['send_to_dashboard'])) {   
        $tableName = $_POST['table_name'];

        // Check if table exists in `table_db`
        $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
        $checkResult = $conn->query($checkTableQuery);

        if ($checkResult && $checkResult->num_rows > 0) {
            // Connect to `dashboard_db`
            $dashboardDbConn = new mysqli($servername, $username, $password, "dashboard_db");

            if ($dashboardDbConn->connect_error) {
                die("Connection to dashboard database failed: " . $dashboardDbConn->connect_error);
            }

            // Create the same table structure in `dashboard_db`
            $createTableQuery = "CREATE TABLE IF NOT EXISTS `dashboard_db`.`$tableName` LIKE `table_db`.`$tableName`";
            if ($dashboardDbConn->query($createTableQuery) === TRUE) {
                // Copy all data from `table_db` to `dashboard_db`
                $copyDataQuery = "INSERT INTO `dashboard_db`.`$tableName` SELECT * FROM `table_db`.`$tableName`";
                if ($dashboardDbConn->query($copyDataQuery) === TRUE) {
                    echo "<p>Table '$tableName' successfully sent to the dashboard database!</p>";
                } else {
                    echo "<p>Error copying data to dashboard database: " . $dashboardDbConn->error . "</p>";
                }
            } else {
                echo "<p>Error creating table in dashboard database: " . $dashboardDbConn->error . "</p>";
            }

            $dashboardDbConn->close();
        } else {
            echo "<p>Table '$tableName' does not exist in the source database!</p>";
        }
    }

    // Display all tables and add "Send to Dashboard" button for each
    if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
        $searchQuery = $_POST['search_query'];
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

// Fetch existing rows
$dataResult = $conn->query("SELECT * FROM $currentTable");
if ($dataResult && $dataResult->num_rows > 0) {
    while ($dataRow = $dataResult->fetch_assoc()) {
        // Determine status based on time_in and deadline
        $status = '';
        if ($dataRow['time_in']) {
            if (strtotime($dataRow['time_in']) > strtotime($dataRow['deadline'])) {
                $status = 'Late';
            } elseif (strtotime($dataRow['time_in']) <= strtotime($dataRow['deadline'])) {
                $status = 'Present';
            }
        } else {
            $status = '';
        }

        // Output the table row
        echo "<tr>
            <td>" . $dataRow['id'] . "</td>
            <td>" . $status . "</td>
            <td>" . $dataRow['studentname'] . "</td>
            <td>" . $dataRow['gender'] . "</td>
            <td>" . $dataRow['lrn'] . "</td>
            <td>" . $dataRow['time_in'] . "</td>
            <td>" . $dataRow['deadline'] . "</td>
            <td>" . $dataRow['date_created'] . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8'>No data found.</td></tr>";
}

            echo "</table>";

            // Add status update and "Send to Dashboard" buttons below the table
            echo "<form method='POST' onsubmit='return confirmSend();'>
                    <input type='hidden' name='table_name' value='$currentTable'>
                    <button type='submit' name='finalize_table'>Update Status to Absent</button>
                    <button type='submit' name='send_to_dashboard'>Send to Dashboard</button>
                    <button type='submit' name='delete_table'>Delete Table</button>
                </form>";
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }
    ?>
</body>
</html>
