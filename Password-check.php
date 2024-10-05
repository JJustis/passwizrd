<?php
// password-check.php

// Set the correct password
$correctPassword = "open sesame"; // Change this to your desired correct password

// Retrieve the submitted password
if (isset($_POST['password'])) {
    $submittedPassword = $_POST['password'];

    // Check if the submitted password matches the correct password
    if ($submittedPassword === $correctPassword) {
        echo json_encode(["locked" => false]);
    } else {
        echo json_encode(["locked" => true]);
    }
}
?>
