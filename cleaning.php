<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "reservesphp"; // Replace with your database name

// Create connection to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update rows where `wiki` column has the value "Error decoding JSON."
$sql = "UPDATE word SET definition = '' WHERE definition = 'Definition not found'";
if ($conn->query($sql) === TRUE) {
    echo "Entries in the wiki column with 'Error decoding JSON.' have been successfully updated to an empty string.";
} else {
    echo "Error updating wiki column: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
