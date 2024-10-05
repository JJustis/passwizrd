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

// Retrieve words and definitions from the `word` table
$sql = "SELECT word, definition FROM word";
$result = $conn->query($sql);

// Prepare to store word pairs in an array
$word_pairs = [];

// Words to exclude from the related word selection
$excluded_words = ['definition', 'error', 'relating'];

// Check if there are any rows returned from the database
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $word = strtolower($row['word']); // Convert word to lowercase
        $definition = strtolower($row['definition']); // Convert definition to lowercase

        // Extract words that have 5 or more characters from the definition and filter out excluded words
        preg_match_all('/\b\w{5,}\b/', $definition, $matches);
        $long_words = array_filter($matches[0], function($w) use ($excluded_words) {
            return !in_array(strtolower($w), $excluded_words); // Exclude specific words
        });

        // Ensure there are at least 2 long words in the definition after filtering
        if (count($long_words) >= 2) {
            // Use a frequency-based method to find the most common co-occurring word in the definition
            $related_word = find_most_frequent_word($long_words);

            // Convert the related word to lowercase
            $related_word = strtolower($related_word);

            // Store the word pair in the format "word related_word" and convert to lowercase
            $word_pairs[] = "$word $related_word";
        }
    }
}

// Write the word pairs to the wordlist.txt file, appending each pair on a new line
file_put_contents('wordlist.txt', implode("\n", $word_pairs) . "\n", FILE_APPEND);

// Close the database connection
$conn->close();

// Function to find the most frequently occurring word in an array
function find_most_frequent_word($word_array) {
    // Count the frequency of each word
    $word_frequency = array_count_values($word_array);
    
    // Sort the array by frequency in descending order
    arsort($word_frequency);

    // Return the first key of the sorted array
    reset($word_frequency); // Move the array's internal pointer to the first element
    return key($word_frequency); // Return the key of the first element
}

echo "New word pairs have been successfully appended to wordlist.txt";
?>
