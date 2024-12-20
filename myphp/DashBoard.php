<?php
// Start the session
session_start();

// Database configuration for `u193875898_dashboard_db`
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u193875898_dashboard_db"; // Target database

// Create connection to `u193875898_dashboard_db`
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection to `u193875898_dashboard_db`
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search and section filter
$searchQuery = '';
$sectionFilter = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        $searchQuery = $_POST['search_term'];
        // Sanitize the input to prevent SQL injection
        $searchQuery = $conn->real_escape_string($searchQuery);
    }
    if (isset($_POST['section_filter'])) {
        $sectionFilter = $_POST['section_filter'];
        // Sanitize the input to prevent SQL injection
        $sectionFilter = $conn->real_escape_string($sectionFilter);
    }
}

// Function to validate if the input is a valid date format (YYYY-MM-DD)
function isValidDate($date) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
}

// Fetch all table names from the `u193875898_dashboard_db` database
$sql = "SHOW TABLES";
$tableResult = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Table Display</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/addTable.css">
    <style>
        /* Add styles for highlighting */
        .highlight {
            background-color: yellow;
        }
    </style>
    <script type="text/javascript">
        function confirmDelete(tableName) {
            return confirm("Are you sure you want to delete the table '" + tableName + "'? This action cannot be undone!");
        }
    </script>
    <style>
        .table-actions {
            margin: 10px 0;
        }

        .action-button {
            padding: 8px 15px;
            margin-right: 10px;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            color: white;
            font-size: 14px;
        }

        .action-button:hover {
            opacity: 0.9;
        }

        a.action-button {
            background-color: #4CAF50;
            display: inline-block;
        }

        .delete-button {
            background-color: #f44336;
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">

    <h2>Attendance Tables</h2>

    <!-- Filter and search form -->
    <form method="POST">
        <!-- Dropdown for section filter -->
        <label for="section_filter">Filter by Section:</label>
        <select name="section_filter" id="section_filter">
            <option value="">Section Filter</option>
            <option value="">GR-10</option>
            <option value="Amber" <?php if ($sectionFilter == "Amber") echo "selected"; ?>>Amber</option>
            <option value="Amethyst" <?php if ($sectionFilter == "Amethyst") echo "selected"; ?>>Amethyst</option>
            <option value="Diamond" <?php if ($sectionFilter == "Diamond") echo "selected"; ?>>Diamond</option>
            <option value="Emerald" <?php if ($sectionFilter == "Emerald") echo "selected"; ?>>Emerald</option>
            <option value="Garnet" <?php if ($sectionFilter == "Garnet") echo "selected"; ?>>Garnet</option>
            <option value="Jade" <?php if ($sectionFilter == "Jade") echo "selected"; ?>>Jade</option>
            <option value="Onyx" <?php if ($sectionFilter == "Onyx") echo "selected"; ?>>Onyx</option>
            <option value="Pearl" <?php if ($sectionFilter == "Pearl") echo "selected"; ?>>Pearl</option>
            <option value="Ruby" <?php if ($sectionFilter == "Ruby") echo "selected"; ?>>Ruby</option>
            <option value="Sapphire" <?php if ($sectionFilter == "Sapphire") echo "selected"; ?>>Sapphire</option>
            <option value="">GR-9</option>

            <option value="Amber" <?php if ($sectionFilter == "Agoncillo") echo "selected"; ?>>Agoncillo</option>
            <option value="Amethyst" <?php if ($sectionFilter == "Aquino") echo "selected"; ?>>Aquino</option>
            <option value="Diamond" <?php if ($sectionFilter == "Balagtas") echo "selected"; ?>>Balagtas</option>
            <option value="Emerald" <?php if ($sectionFilter == "Bonifacio") echo "selected"; ?>>Bonifacio</option>
            <option value="Garnet" <?php if ($sectionFilter == "Jacinto") echo "selected"; ?>>Jacinto</option>
            <option value="Jade" <?php if ($sectionFilter == "Lapu-Lapu") echo "selected"; ?>>Lapu-Lapu</option>
            <option value="Onyx" <?php if ($sectionFilter == "Mabini") echo "selected"; ?>>Mabini</option>
            <option value="Pearl" <?php if ($sectionFilter == "Ponce") echo "selected"; ?>>Ponce</option>
            <option value="Ruby" <?php if ($sectionFilter == "Rizal") echo "selected"; ?>>Rizal</option>

            <option value="">GR-8</option>

            <option value="">GR-7</option>

        </select>

        <!-- Search bar -->
        <input type="text" name="search_term" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>" />
        <button type="submit" name="search">Apply Filters</button>
    </form>
    

    <?php
    if ($tableResult->num_rows > 0) {
        // Loop through all tables
        while ($tableRow = $tableResult->fetch_array()) {
            $tableName = $tableRow[0]; // Get the name of each table

            // Modify the SQL query to search within the table if filters are applied
            $dataSql = "SELECT * FROM `$tableName` WHERE 1";
            if ($searchQuery) {
                $dataSql .= " AND (
                    studentname LIKE '%$searchQuery%' OR
                    gender LIKE '%$searchQuery%' OR
                    lrn LIKE '%$searchQuery%'";
                if (isValidDate($searchQuery)) {
                    $dataSql .= " OR date_created LIKE '%$searchQuery%'";
                }
                $dataSql .= ")";
            }
            if ($sectionFilter) {
                $dataSql .= " AND section = '$sectionFilter'";
            }

            $dataResult = $conn->query($dataSql);

            echo "<h3>Table: $tableName</h3>";
            echo "<div class='table-actions'>";
            echo "<a href='download.php?table=$tableName' target='_blank' class='action-button'>Download CSV</a>";
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='table_to_delete' value='$tableName'>";
            echo "<button type='submit' name='delete_table' class='action-button delete-button' onclick='return confirmDelete(\"$tableName\")'>Delete Table</button>";
            echo "</form>";
            echo "</div>";
            
            if ($dataResult->num_rows > 0) {
                echo "<table border='1'>
                        <tr>
                            <th>Id</th>
                            <th>Section</th> <!-- Added Section Column -->
                            <th>Status</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>LRN</th>
                            <th>Time In</th>
                            <th>Deadline</th>
                            <th>Date Created</th>
                        </tr>";

                // Iterate through each row in the table and display the data
                while ($row = $dataResult->fetch_assoc()) {
                    // Determine status based on time_in and deadline
                    if ($row['time_in']) {
                        if (strtotime($row['time_in']) > strtotime($row['deadline'])) {
                            $status = 'Late';
                            // Update the status in the database
                            $updateStatus = "UPDATE $tableName SET status = 'Late' WHERE id = " . $row['id'];
                            $conn->query($updateStatus);
                        } else {
                            $status = 'Present';
                            // Update the status in the database
                            $updateStatus = "UPDATE $tableName SET status = 'Present' WHERE id = " . $row['id'];
                            $conn->query($updateStatus);
                        }
                    } else {
                        $status = $row['status']; // Use existing status from database
                    }

                    // Convert time_in and deadline to 12-hour format
                    $time_in_formatted = $row['time_in'] ? date("h:i:s A", strtotime($row['time_in'])) : '';
                    $deadline_formatted = date("h:i:s A", strtotime($row['deadline']));

                    echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . $row['section'] . "</td>
                        <td>" . $status . "</td>
                        <td>" . $row['studentname'] . "</td>
                        <td>" . $row['gender'] . "</td>
                        <td>" . $row['lrn'] . "</td>
                        <td>" . $time_in_formatted . "</td>
                        <td>" . $deadline_formatted . "</td>
                        <td>" . $row['date_created'] . "</td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No data found matching your filters in table '$tableName'.</p>";
            }
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }
    ?>
</body>
</html>

<?php
// Handle table deletion
if (isset($_POST['delete_table']) && isset($_POST['table_to_delete'])) {
    $tableToDelete = $_POST['table_to_delete'];
    
    // Sanitize the table name to prevent SQL injection
    $tableToDelete = $conn->real_escape_string($tableToDelete);
    
    // Drop the table
    $dropQuery = "DROP TABLE IF EXISTS `$tableToDelete`";
    if ($conn->query($dropQuery) === TRUE) {
        echo "<script>alert('Table \"$tableToDelete\" has been successfully deleted.');</script>";
        echo "<script>window.location.href = window.location.pathname;</script>";
    } else {
        echo "<script>alert('Error deleting table: " . $conn->error . "');</script>";
    }
}

// Close the database connection
$conn->close();
?>
