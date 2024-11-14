<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "table_db"; // Target database for the new table

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tables from table_db
$tableList = [];
$tableResult = $conn->query("SHOW TABLES");
while ($row = $tableResult->fetch_array()) {
    $tableList[] = $row[0];
}

// Fetch tables from masterlistdb to populate the dropdown
$masterlistTables = [];
$masterlistConn = new mysqli($servername, $username, $password, "masterlistdb");
if ($masterlistConn->connect_error) {
    die("Connection failed: " . $masterlistConn->connect_error);
}

$masterlistResult = $masterlistConn->query("SHOW TABLES");
while ($row = $masterlistResult->fetch_array()) {
    $masterlistTables[] = $row[0];
}

// Initialize variable to store the name of the new table created
$createdTableName = '';
$tableCreationMessage = ''; // Message for success or error

// Handle table creation with data copying from masterlistdb
if (isset($_POST['create_table'])) {
    $tableName = $_POST['table_name'];
    $deadline = $_POST['deadline'];
    $selectedMasterlistTable = $_POST['masterlist_table']; // Selected table from masterlistdb

    // Ensure deadline format is valid (HH:MM:SS)
    $deadline = date('H:i:s', strtotime($deadline));

    // Create new table
    $createSql = "CREATE TABLE IF NOT EXISTS $tableName (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        status VARCHAR(50) DEFAULT NULL,
        studentname VARCHAR(100) DEFAULT NULL,
        gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
        lrn VARCHAR(20) NOT NULL,
        time_in TIME DEFAULT NULL,
        deadline TIME DEFAULT '$deadline',
        date_created DATE DEFAULT CURRENT_DATE
    )";

    if ($conn->query($createSql) === TRUE) {
        $createdTableName = $tableName;
        $tableCreationMessage = "Table '$tableName' created successfully!";

        // Copy data from selected masterlistdb table
        $copySql = "INSERT INTO $tableName (status, studentname, gender, lrn, deadline)
                    SELECT NULL, studentname, gender, lrn, '$deadline' AS deadline
                    FROM masterlistdb.$selectedMasterlistTable";

        if ($conn->query($copySql) === TRUE) {
            $tableCreationMessage .= "<br>Data copied from '$selectedMasterlistTable' to '$tableName' successfully!";
        } else {
            $tableCreationMessage .= "<br>Error copying data: " . $conn->error;
        }

        // Update status based on time_in and deadline
        $updateStatusSql = "
            UPDATE $tableName 
            SET status = CASE 
                WHEN TIME(time_in) <= TIME(deadline) THEN 'on time'
                ELSE 'late'
            END
            WHERE time_in IS NOT NULL
        ";

        if ($conn->query($updateStatusSql) === TRUE) {
            $tableCreationMessage .= "<br>Statuses updated successfully based on time_in and deadline!";
        } else {
            $tableCreationMessage .= "<br>Error updating statuses: " . $conn->error;
        }

    } else {
        $tableCreationMessage = "Error creating table: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Table with Masterlist Data Copy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/addTable.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-header {
            font-size: 1.5em;
            font-weight: bold;
        }
        .modal-footer {
            text-align: right;
        }
        .close {
            color: #aaa;
            font-size: 1.5em;
            font-weight: bold;
            float: right;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">
    <h2>Create a New Table with Data Copy</h2>
    <form action="" method="POST">
        <label for="table_name">Enter Table Name:</label>
        <input type="text" id="table_name" name="table_name" required>
        <br>
        <label for="deadline">Set Deadline (HH:MM:SS):</label>
        <input type="time" id="deadline" name="deadline" required>
        <br>
        <label for="masterlist_table">Select Table to Copy From:</label>
        <select name="masterlist_table" id="masterlist_table" required>
            <option value="">Select masterlist table...</option>
            <?php foreach ($masterlistTables as $table) : ?>
                <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit" name="create_table">Create Table and Copy Data</button>
    </form>
    <button onclick="window.location.href='QRScanner.php'">Go to QR Code Scanner</button>
    
    <!-- Modal for success/error message -->
    <div id="tableCreationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">Table Creation Status</div>
            <div class="modal-body">
                <?php echo $tableCreationMessage; ?>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <?php if (!empty($tableList)): ?>
        <h3>All Created Tables</h3>
        <table border="1">
            <tr>
                <th>Table Name</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($tableList as $tableName): ?>
                <tr>
                    <td><?php echo $tableName; ?></td>
                    <td>
                        <a href="?table=<?php echo $tableName; ?>">View Data</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['table'])): ?>
            <h3>Table Data for '<?php echo $_GET['table']; ?>'</h3>
            <table border="1">
                <tr>
                    <th>Id</th>
                    <th>Status</th>
                    <th>Student Name</th>
                    <th>Gender</th>
                    <th>LRN</th>
                    <th>Time In</th>
                    <th>Deadline</th>
                    <th>Date Created</th>
                </tr>
                <?php
                $viewTableName = $_GET['table'];
                $dataResult = $conn->query("SELECT * FROM $viewTableName");
                if ($dataResult === false) {
                    echo "<tr><td colspan='8'>Error retrieving data: " . $conn->error . "</td></tr>";
                } else if ($dataResult->num_rows > 0) {
                    while ($dataRow = $dataResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $dataRow['id'] . "</td>";
                        echo "<td>" . $dataRow['status'] . "</td>";
                        echo "<td>" . $dataRow['studentname'] . "</td>";
                        echo "<td>" . $dataRow['gender'] . "</td>";
                        echo "<td>" . $dataRow['lrn'] . "</td>";
                        echo "<td>" . $dataRow['time_in'] . "</td>";
                        echo "<td>" . $dataRow['deadline'] . "</td>";
                        echo "<td>" . $dataRow['date_created'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No data found</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        // Function to close the modal
        function closeModal() {
            document.getElementById('tableCreationModal').style.display = 'none';
        }

        // Show the modal if a table is created
        <?php if (!empty($tableCreationMessage)) : ?>
            document.getElementById('tableCreationModal').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
