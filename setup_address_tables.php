<?php
require_once '../api/db.php';

echo "Starting database setup for address tables...\n";

try {
    // Read the SQL file
    $sql = file_get_contents('./sql/create_address_tables.sql');

    if (!$sql) {
        die("Could not read SQL file\n");
    }

    // Execute the entire SQL file at once
    if ($conn->multi_query($sql)) {
        echo "✓ SQL script executed successfully!\n";

        // Consume all results to avoid "Commands out of sync" error
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        echo "\n🎉 Address tables created successfully! You can now use the address dropdown system.\n";
    } else {
        echo "✗ Error executing SQL: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}

$conn->close();
?>