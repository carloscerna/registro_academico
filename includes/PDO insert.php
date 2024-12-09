<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'your_database_name';
$user = 'your_username';
$pass = 'your_password';

try {
    // Create a new PDO instance
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare an SQL statement for execution
    $stmt = $pdo->prepare("UPDATE your_table_name SET column1 = :value1, column2 = :value2 WHERE your_condition");

    // Bind parameters to the SQL statement
    $stmt->bindParam(':value1', $value1);
    $stmt->bindParam(':value2', $value2);

    // Values to update
    $value1 = 'new_value1';
    $value2 = 'new_value2';

    // Execute the statement
    $stmt->execute();

    echo "Data updated successfully!";
} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>
