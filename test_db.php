<?php
$host = 'sql102.infinityfree.com';
$db   = 'if0_38825041_calambago';
$user = 'if0_38825041';
$pass = 'gaqAneRGHv9TvRI';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "Connected successfully!";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
