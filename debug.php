<?php
$conn = new mysqli('sql300.infinityfree.com', 'if0_37718039', 'WE3aYUOKoO7Ax', 'if0_37718039_sportma_res');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>