<?php
// Start the session
session_start();

// Database configuration for `dashboard_db`
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dashboard_db"; // Target database

// Create connection to `dashboard_db`
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection to `dashboard_db`
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$searchQuery = '';
if (isset($_POST['search'])) {
    $searchQuery = $_POST['search_term'];
    // Sanitize the input to prevent SQL injection
    $searchQuery = $conn->real_escape_string($searchQuery);
}

// Fetch all table names from the `dashboard_db` database
$sql = "SHOW TABLES";
$tableResult = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Table Display</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/styles.css">
    <style>
        /* Add styles for highlighting */
        .highlight {
            background-color: yellow;
        }
    </style>
</head>
<body style="background-color: #f5f5f5;">

    <h2>Attendance Tables</h2>

    <!-- Search form -->
    <form method="POST">
        <input type="text" name="search_term" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>" />
        <button type="submit" name="search">Search</button>
    </form>

    <?php
    if ($tableResult->num_rows > 0) {
        // Loop through all tables
        while ($tableRow = $tableResult->fetch_array()) {
            $tableName = $tableRow[0]; // Get the name of each table

            // Modify the SQL query to search within the table if a search term is provided
            $dataSql = "SELECT * FROM `$tableName`";
            if ($searchQuery) {
                $dataSql .= " WHERE 
                    studentname LIKE '%$searchQuery%' OR
                    gender LIKE '%$searchQuery%' OR
                    section LIKE '%$searchQuery%' OR
                    lrn LIKE '%$searchQuery%'";
            }
            $dataResult = $conn->query($dataSql);

            echo "<h3>Table: $tableName</h3>";

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
                    // Default to 'Present'
                    $status = '';
                    // Determine status based on time_in and deadline
                    if ($row['time_in']) {
                        if (strtotime($row['time_in']) > strtotime($row['deadline'])) {
                            $status = 'Late';
                        } else {
                            $status = 'Present';
                        }
                    } else {
                        $status = 'Absent';
                    }

                    // Highlight matching search terms
                    $highlightedName = preg_replace("/($searchQuery)/i", "<span class='highlight'>$1</span>", $row['studentname']);
                    $highlightedSection = preg_replace("/($searchQuery)/i", "<span class='highlight'>$1</span>", $row['section']);
                    $highlightedGender = preg_replace("/($searchQuery)/i", "<span class='highlight'>$1</span>", $row['gender']);
                    $highlightedLrn = preg_replace("/($searchQuery)/i", "<span class='highlight'>$1</span>", $row['lrn']);

                    echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . $highlightedSection . "</td> <!-- Display Section Field -->
                        <td>" . $status . "</td>
                        <td>" . $highlightedName . "</td>
                        <td>" . $highlightedGender . "</td>
                        <td>" . $highlightedLrn . "</td>
                        <td>" . $row['time_in'] . "</td>
                        <td>" . $row['deadline'] . "</td>
                        <td>" . $row['date_created'] . "</td>
                    </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No data found matching your search in table '$tableName'.</p>";
            }
        }
    } else {
        echo "<p>No tables found in the database.</p>";
    }
    ?>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
