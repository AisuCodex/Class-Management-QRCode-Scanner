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

// Retrieve all data from `attendance_table`
$sql = "SELECT * FROM attendance_table";
$result = $conn->query($sql);

// Check if the query returns any rows
if ($result->num_rows > 0) {
    $dataFetched = true;
} else {
    $dataFetched = false;
}
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
</head>
<body style="background-color: #f5f5f5;">

    <h2>Attendance Table</h2>

    <?php if ($dataFetched): ?>
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
            // Display rows of data
            while ($row = $result->fetch_assoc()) {
                // Compare time_in and deadline to set status
                $status = 'On Time'; // Default status
                if ($row['time_in'] && strtotime($row['time_in']) > strtotime($row['deadline'])) {
                    $status = 'Late';
                }

                echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . $status . "</td>
                    <td>" . $row['studentname'] . "</td>
                    <td>" . $row['gender'] . "</td>
                    <td>" . $row['lrn'] . "</td>
                    <td>" . $row['time_in'] . "</td>
                    <td>" . $row['deadline'] . "</td>
                    <td>" . $row['date_created'] . "</td>
                </tr>";
            }
            ?>
        </table>
    <?php else: ?>
        <p>No data found in the attendance table.</p>
    <?php endif; ?>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>