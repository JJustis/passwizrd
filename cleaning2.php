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

// Retrieve words with empty definitions from the `word` table
$sql = "SELECT word FROM word WHERE definition = '' OR definition IS NULL";
$result = $conn->query($sql);

// Check if there are any rows returned from the database
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $word = $row['word'];
        $definition = '';

        // Step 1: Scrape the Merriam-Webster website for the word's definition
        $definition = scrape_merriam_webster($word);

        // Step 2: If Merriam-Webster has no result, look up the word on Wikipedia
        if (empty($definition)) {
            $definition = lookup_wikipedia($word);
        }

        // Step 3: If a definition is found, update the row in the `word` table
        if (!empty($definition)) {
            $update_sql = "UPDATE word SET definition = ? WHERE word = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ss", $definition, $word);
            $stmt->execute();
            $stmt->close();
            echo "Definition updated for word: $word\n";
        } else {
            echo "No definition found for word: $word\n";
        }
    }
} else {
    echo "No words with empty definitions found.\n";
}

// Close the database connection
$conn->close();

// Function to scrape Merriam-Webster's website for a word's definition
function scrape_merriam_webster($word) {
    $url = "https://www.merriam-webster.com/dictionary/" . urlencode($word);
    $html = file_get_contents($url);

    if ($html === FALSE) {
        return ''; // Return empty string if the page cannot be loaded
    }

    // Load the HTML into DOMDocument and parse it using DOMXPath
    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
    $doc->loadHTML($html);
    libxml_clear_errors();

    // Use XPath to find the definition
    $xpath = new DOMXPath($doc);

    // Merriam-Webster's definitions are usually in a span with class "dtText"
    $nodes = $xpath->query('//span[@class="dtText"]');

    if ($nodes->length > 0) {
        $definitions = [];
        foreach ($nodes as $node) {
            $definitions[] = trim($node->textContent);
        }
        // Return the first definition found
        return implode("; ", $definitions);
    }

    return '';
}

// Function to look up a word's definition in Wikipedia using its API
function lookup_wikipedia($word) {
    $url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exintro=&titles=" . urlencode($word);
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Retrieve the page content from Wikipedia API response
    if (isset($data['query']['pages'])) {
        $pages = $data['query']['pages'];
        foreach ($pages as $page) {
            if (isset($page['extract'])) {
                // Return the first paragraph as the definition
                return strip_tags($page['extract']);
            }
        }
    }
    return '';
}
?>
