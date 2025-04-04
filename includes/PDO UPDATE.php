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
    $stmt = $pdo->prepare("INSERT INTO your_table_name (column1, column2) VALUES (:value1, :value2)");

    // Bind parameters to the SQL statement
    $stmt->bindParam(':value1', $value1);
    $stmt->bindParam(':value2', $value2);

    // Values to insert
    $value1 = 'some_value';
    $value2 = 'another_value';

    // Execute the statement
    $stmt->execute();

    echo "Data inserted successfully!";
} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=tu_base_de_datos', 'usuario', 'contraseña');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT * FROM tabla WHERE id = :id');
    $stmt->execute(['id' => 1]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        echo $row['nombre'];
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
