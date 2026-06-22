<?php
include 'db_connect.php';

echo "<h1>BookstoreDB Connection Test</h1>";

// Test Query: Fetch Books
$sql = "SELECT Title, Author, Selling_Price FROM Book";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_assoc()) {
        echo "<li><strong>" . $row["Title"] . "</strong> by " . $row["Author"] . 
             " (Price: " . $row["Selling_Price"] . ")</li>";
    }
    echo "</ul>";
    echo "<h3 style='color:green'>SUCCESS: Connected to BookstoreDB!</h3>";
} else {
    echo "0 results found. Check your database.";
}
?>