<?php
 include('config.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a new attendance record with cut off</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="addTable.css">

    <script type="text/javascript">
        function confirmDelete(tableName) {
            if (confirm('Are you sure you want to delete table "' + tableName + '"? This action cannot be undone.')) {
                document.getElementById('delete_table_name').value = tableName;
                document.getElementById('delete_form').submit();
            }
        }
        function confirmSend() {
            return confirm("Are you sure?");
        }
    </script>
</head>

<style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', 'Segoe UI', sans-serif;
}

body {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
  color: #2c3e50;
  line-height: 1.7;
  min-height: 100%;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  padding-bottom: 5%;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.2);
  z-index: -1;
}

.container {
  background: white;
  padding: 3rem;
  border-radius: 15px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  width: 90%;
  max-width: 100%;
  border: 1px solid blue;
  margin: 2% auto 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  position: relative;
}

h2, h3 {
  display: block;
  width: 100%;
  color: #1a237e;
  margin-bottom: 2rem;
  border-bottom: 3px solid #0099ff;
  padding-bottom: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  text-align: center;
  position: relative;
}

h2::after, h3::after {
  content: '';
  position: absolute;
  bottom: -3px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: #0004ff;
}

form {
  background: rgba(255, 255, 255, 0.95);
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  margin-bottom: 2.5rem;
  width: 40%;
  max-width: 90%;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid rgba(0, 110, 255, 0.591);
  align-items: center;
}

form:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

label {
  display: block;
  margin-bottom: 0.8rem;
  color: #1a237e;
  font-weight: 500;
  font-size: 0.95rem;
  letter-spacing: 0.3px;
}

input[type='text'],
input[type='time'],
select {
  width: 100%;
  padding: 1rem;
  margin-bottom: 1.5rem;
  border: 2px solid #3eb5ff;
  border-radius: 18px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.9);
}

input[type='text']:focus,
input[type='time']:focus,
select:focus {
  outline: none;
  border-color: #3f51b5;
  box-shadow: 0 0 0 4px rgba(63, 81, 181, 0.1);
  background: white;
}

button {
  background: linear-gradient(45deg, #1f41ff, #65a5ff);
  color: white;
  padding: 1rem 2rem;
  border: none;
  border-radius: 28px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  position: relative;
  overflow: hidden;
  margin: 10px;
}

button::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, rgba(255, 255, 255, 0.2), transparent);
  transition: transform 0.3s ease;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(63, 114, 181, 0.3);
}

button:hover::after {
  transform: translateX(100%);
}

.btns {
  margin: 2rem 0;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.dashboard-btn {
  background: linear-gradient(45deg, #1f41ff, #65a5ff);
  color: white;
  padding: 0.8rem 1.5rem;
  text-decoration: none;
  border-radius: 28px;
  font-weight: 500;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  text-transform: uppercase;
}

.dashboard-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(63, 114, 181, 0.3);
}

table {
  width: 80%;
  border-collapse: separate;
  border-spacing: 0;
  background: white;
  margin: 2.5rem auto;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

table:hover {
  transform: translateY(-5px);
}

th, td {
  padding: 1.2rem;
  text-align: left;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

th {
  background: linear-gradient(45deg, rgb(0, 94, 255), #00189e);
  color: white;
  font-weight: 500;
  letter-spacing: 0.5px;
  font-size: 0.95rem;
  text-transform: uppercase;
}

tr:last-child td {
  border-bottom: none;
}

tr:nth-child(even) {
  background-color: #f8f9ff;
}

tr {
  transition: background-color 0.3s ease;
}

tr:hover {
  background-color: #f0f2ff;
}

@media (max-width: 768px) {
  form {
    width: 90%;
    padding: 1.5rem;
  }

  .btns {
    flex-direction: column;
    align-items: stretch;
  }

  .dashboard-btn {
    text-align: center;
  }

  table {
    width: 95%;
  }
}

</style>
<body style="background-color: #f5f5f5;">

<div class="btns" style="text-align: right; padding: 20px;">
            <a href="../php/home.php" class="dashboard-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to HomePage</a>
        </div>


    <h2>Create a new attendance table with cut off</h2>
    <form action="" method="POST">
        <label for="table_name">Enter Section Name:</label>
        <input type="text" id="table_name" placeholder="10_Diamond_Math" name="table_name" required>
        <br>
        <label for="deadline">Set CutOff (Time):</label>
        <input type="time" id="deadline" name="deadline" required step="1">
        <small style="display: block; margin: 5px 0;">Time will be displayed in 12-hour format (AM/PM)</small>
        <br>

        <!-- Dropdown to select table from u193875898_masterlistdb to copy data from -->
        <label for="copy_from_table">Select Section:</label>
        <select id="copy_from_table" name="copy_from_table" required>
            <option value="">Select a Section:</option>
            <?php
            // Fetch tables from u193875898_masterlistdb and populate dropdown
            $tablesResult = $masterConn->query("SHOW TABLES");
            if ($tablesResult->num_rows > 0) {
                while ($tableRow = $tablesResult->fetch_array()) {
                    echo "<option value='" . $tableRow[0] . "'>" . $tableRow[0] . "</option>";
                }
            }
            ?>
        </select>
        <br>
        <button type="submit" name="create_table">Create Attendance</button>
    </form>
    <div class="btns" style="text-align: right; padding: 20px;">
        <a href="QRScanner.php" class="dashboard-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to QRScanner</a>
        <a href="masterList_addTable.php" class="dashboard-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to MasterList Add section</a>
        <a href="qrcodeGenerator.php" class="dashboard-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to QRCodeGenerator</a>
        <a href="DashBoard.php" class="dashboard-btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Attendance Record</a>
    </div>

    <form id="delete_form" method="POST" style="display: none;">
        <input type="hidden" id="delete_table_name" name="table_name">
        <input type="hidden" name="delete_table" value="1">
    </form>

    <!-- Search form -->
    <h3>Search Section Table Name:</h3>
    <form method="POST" action="">
        <input type="text" name="search_query" placeholder="Search Table name..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" required>
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href=''">Reset</button>
    </form>
    <?php
    // Handle delete functionality
    if (isset($_POST['delete_table']) && isset($_POST['table_name'])) {
        $tableName = $conn->real_escape_string($_POST['table_name']);
        
        // Check if table exists in the main database
        $checkTable = "SHOW TABLES LIKE '$tableName'";
        $tableExists = $conn->query($checkTable);
        
        if ($tableExists && $tableExists->num_rows > 0) {
            // Drop the table from the main database
            $sql = "DROP TABLE `$tableName`";
            if ($conn->query($sql) === TRUE) {
                // Also check and delete from dashboard if it exists there
                if (isset($dashboardConn)) {
                    $checkDashboard = "SHOW TABLES LIKE '$tableName'";
                    $dashboardTableExists = $dashboardConn->query($checkDashboard);
                    
                    if ($dashboardTableExists && $dashboardTableExists->num_rows > 0) {
                        $dashboardSql = "DROP TABLE `$tableName`";
                        $dashboardConn->query($dashboardSql);
                    }
                }
                
                echo "<script>
                    alert('Table \"$tableName\" deleted successfully!');
                    window.location.href='addTable.php';
                </script>";
                exit();
            } else {
                echo "<script>
                    alert('Error deleting table: " . $conn->error . "');
                </script>";
            }
        } else {
            echo "<script>
                alert('Table not found in database.');
            </script>";
        }
    }

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

        // Check if table exists in `u193875898_table_db`
        $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
        $checkResult = $conn->query($checkTableQuery);

        if ($checkResult && $checkResult->num_rows > 0) {
            // Use the dashboard database credentials from config.php
            $dashboardDbConn = new mysqli($servername, $dashboardUsername, $dashboardPassword, $dashboardDbname);

            if ($dashboardDbConn->connect_error) {
                die("Connection to dashboard database failed: " . $dashboardDbConn->connect_error);
            }

            // Check if table already exists in dashboard
            $checkDashboardTable = "SHOW TABLES LIKE '$tableName'";
            $dashboardTableExists = $dashboardDbConn->query($checkDashboardTable);

            if ($dashboardTableExists && $dashboardTableExists->num_rows > 0) {
                // Table exists, drop it first
                $dropQuery = "DROP TABLE `$tableName`";
                if (!$dashboardDbConn->query($dropQuery)) {
                    echo "<p class='error'>Error removing existing table: " . $dashboardDbConn->error . "</p>";
                    $dashboardDbConn->close();
                    return;
                }
            }

            // Get the table structure from the source database
            $getStructureQuery = "SHOW CREATE TABLE `$tableName`";
            $structureResult = $conn->query($getStructureQuery);
            
            if ($structureResult && $structureResult->num_rows > 0) {
                $row = $structureResult->fetch_array();
                $createTableSQL = $row[1]; // This contains the CREATE TABLE statement
                
                // Create the table in dashboard database
                if ($dashboardDbConn->query($createTableSQL) === TRUE) {
                    // Get the data from the source table using the original connection
                    $getDataQuery = "SELECT * FROM `$tableName`";
                    $dataResult = $conn->query($getDataQuery);
                    
                    if ($dataResult && $dataResult->num_rows > 0) {
                        // Prepare the column names for the INSERT query
                        $fields = array();
                        $values = array();
                        
                        while ($row = $dataResult->fetch_assoc()) {
                            $rowValues = array();
                            foreach ($row as $value) {
                                // Handle NULL values properly
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    $rowValues[] = "'" . $dashboardDbConn->real_escape_string($value) . "'";
                                }
                            }
                            
                            // Insert each row into the dashboard database
                            $insertQuery = "INSERT INTO `$tableName` VALUES (" . implode(",", $rowValues) . ")";
                            if (!$dashboardDbConn->query($insertQuery)) {
                                echo "<p class='error'>Error inserting row: " . $dashboardDbConn->error . "</p>";
                                continue;
                            }
                        }
                        echo "<p class='success'>Table successfully updated in dashboard!</p>";
                    } else {
                        echo "<p class='warning'>Table structure copied, but no data to transfer.</p>";
                    }
                } else {
                    echo "<p class='error'>Error creating table in dashboard: " . $dashboardDbConn->error . "</p>";
                }
            } else {
                echo "<p class='error'>Error getting table structure: " . $conn->error . "</p>";
            }
            $dashboardDbConn->close();
        } else {
            echo "<p class='error'>Table '$tableName' does not exist in the source database!</p>";
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

            echo "<h3>Attendance of '$currentTable'</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Id</th>
                        <th>Section</th>
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
                            // Update the status in the database
                            $updateStatus = "UPDATE $currentTable SET status = 'Late' WHERE id = " . $dataRow['id'];
                            $conn->query($updateStatus);
                        } elseif (strtotime($dataRow['time_in']) <= strtotime($dataRow['deadline'])) {
                            $status = 'Present';
                            // Update the status in the database
                            $updateStatus = "UPDATE $currentTable SET status = 'Present' WHERE id = " . $dataRow['id'];
                            $conn->query($updateStatus);
                        }
                    } else {
                        $status = $dataRow['status']; // Use existing status from database
                    }

                    // Convert time_in and deadline to 12-hour format
                    $time_in_formatted = $dataRow['time_in'] ? date("h:i:s A", strtotime($dataRow['time_in'])) : '';
                    $deadline_formatted = date("h:i:s A", strtotime($dataRow['deadline']));

                    // Output the table row
                    echo "<tr>
                        <td>" . $dataRow['id'] . "</td>
                        <td>" . $dataRow['section'] . "</td>
                        <td>" . $status . "</td>
                        <td>" . $dataRow['studentname'] . "</td>
                        <td>" . $dataRow['gender'] . "</td>
                        <td>" . $dataRow['lrn'] . "</td>
                        <td>" . $time_in_formatted . "</td>
                        <td>" . $deadline_formatted . "</td>
                        <td>" . $dataRow['date_created'] . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No data found.</td></tr>";
            }

            echo "</table>";

            // Add status update and "Send to Dashboard" buttons below the table
            echo "<form method='POST' onsubmit='return confirmSend();'>
                    <input type='hidden' name='table_name' value='$currentTable'>
                    <button type='submit' name='finalize_table'>Update Status to Absent</button>
                    <button type='submit' name='send_to_dashboard'>Send to Attendance Record</button>
                    <button type='button' class='delete-btn' onclick='confirmDelete(\"" . htmlspecialchars($currentTable) . "\")'>Delete Table</button>
                </form>";
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }
    ?>
</body>
</html>
