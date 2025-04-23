<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


$host = 'localhost';
$db   = 'calambago';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Routing
$endpoint = $_GET['endpoint'] ?? '';

switch ($endpoint) {
    case 'feedback':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->query("SELECT * FROM feedback ORDER BY time DESC");
            echo json_encode($stmt->fetchAll());
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['name'], $input['email'], $input['message'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO feedback (name, email, message, time) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$input['name'], $input['email'], $input['message']]);
            echo json_encode(['success' => true]);
        }
        break;
    case 'journals':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->query("SELECT * FROM journals ORDER BY date DESC");
            echo json_encode($stmt->fetchAll());
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['title'], $input['text'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO journals (title, text, date) VALUES (?, ?, NOW())");
            $stmt->execute([$input['title'], $input['text']]);
            echo json_encode(['success' => true]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM journals WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        break;
    case 'users':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['username'], $input['email'], $input['password'], $input['type'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'Email already registered']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, type) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $input['username'],
                $input['email'],
                password_hash($input['password'], PASSWORD_DEFAULT),
                $input['type']
            ]);
            echo json_encode(['success' => true]);
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? '';
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM users");
                echo json_encode($stmt->fetchAll());
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $id = $_GET['id'] ?? '';
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$id || !$input || !isset($input['username'], $input['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields or id']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$input['username'], $input['email'], $id]);
            echo json_encode(['success' => true]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        break;
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['email'], $input['password'], $input['type'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND type = ?");
            $stmt->execute([$input['email'], $input['type']]);
            $user = $stmt->fetch();
            if ($user && password_verify($input['password'], $user['password'])) {
                unset($user['password']);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
            }
        }
        break;
    case 'deliveries':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // ?type=driver&driver_id=... or ?type=customer&customer_id=...
            $type = $_GET['type'] ?? '';
            $id = $_GET['id'] ?? '';
            if ($type === 'driver') {
                // Show available and assigned deliveries for this driver
                $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE status IN ('pending','active') AND (driver_id IS NULL OR driver_id = ?)");
                $stmt->execute([$id]);
                $rows = $stmt->fetchAll();
                // Always return coords as JSON arrays
                foreach ($rows as &$row) {
                    if (isset($row['pickup_coords'])) $row['pickup_coords'] = $row['pickup_coords'];
                    if (isset($row['delivery_coords'])) $row['delivery_coords'] = $row['delivery_coords'];
                }
                echo json_encode($rows);
            } elseif ($type === 'customer') {
                // Show all deliveries for this customer
                $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE customer_id = ?");
                $stmt->execute([$id]);
                $rows = $stmt->fetchAll();
                foreach ($rows as &$row) {
                    if (isset($row['pickup_coords'])) $row['pickup_coords'] = $row['pickup_coords'];
                    if (isset($row['delivery_coords'])) $row['delivery_coords'] = $row['delivery_coords'];
                }
                echo json_encode($rows);
            } else {
                // Admin: show all
                $stmt = $pdo->query("SELECT * FROM deliveries");
                $rows = $stmt->fetchAll();
                foreach ($rows as &$row) {
                    if (isset($row['pickup_coords'])) $row['pickup_coords'] = $row['pickup_coords'];
                    if (isset($row['delivery_coords'])) $row['delivery_coords'] = $row['delivery_coords'];
                }
                echo json_encode($rows);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Customer creates a new delivery order
            $input = json_decode(file_get_contents('php://input'), true);
            if (
                !$input ||
                !isset($input['customer_id'], $input['pickup_address'], $input['delivery_address'], $input['fee'], $input['package_info'], $input['priority'])
            ) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            // Accept pickup_coords and delivery_coords if provided (from frontend geocoding)
            $pickup_coords = isset($input['pickup_coords']) ? json_encode($input['pickup_coords']) : null;
            $delivery_coords = isset($input['delivery_coords']) ? json_encode($input['delivery_coords']) : null;
            $stmt = $pdo->prepare("INSERT INTO deliveries (customer_id, pickup_address, delivery_address, pickup_coords, delivery_coords, fee, package_info, priority, notes, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
            $stmt->execute([
                $input['customer_id'],
                $input['pickup_address'],
                $input['delivery_address'],
                $pickup_coords,
                $delivery_coords,
                $input['fee'],
                $input['package_info'],
                $input['priority'],
                $input['notes'] ?? ''
            ]);
            echo json_encode(['success' => true]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            // Driver accepts/rejects/completes a delivery
            $id = $_GET['id'] ?? '';
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$id || !$input || !isset($input['action'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields or id']);
                exit;
            }
            if ($input['action'] === 'accept' && isset($input['driver_id'])) {
                $stmt = $pdo->prepare("UPDATE deliveries SET driver_id = ?, status = 'active', updated_at = NOW() WHERE id = ? AND status = 'pending'");
                $stmt->execute([$input['driver_id'], $id]);
                echo json_encode(['success' => true]);
            } elseif ($input['action'] === 'reject' && isset($input['driver_id'])) {
                $stmt = $pdo->prepare("UPDATE deliveries SET status = 'rejected', updated_at = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->execute([$id, $input['driver_id']]);
                echo json_encode(['success' => true]);
            } elseif ($input['action'] === 'complete' && isset($input['driver_id'])) {
                $stmt = $pdo->prepare("UPDATE deliveries SET status = 'completed', updated_at = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->execute([$id, $input['driver_id']]);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
            }
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown endpoint']);
}

