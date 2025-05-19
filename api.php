<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Origin');
header('Access-Control-Max-Age: 86400'); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'sql102.infinityfree.com';
$db   = 'if0_38825041_calambago';
$user = 'if0_38825041';
$pass = 'gaqAneRGHv9TvRI';
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
            if (!$input || !isset($input['email'], $input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$input['email']]);
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
            $type = $_GET['type'] ?? '';
            $id = $_GET['id'] ?? '';
            error_log("Fetching deliveries for type: $type, id: $id");

            if ($type === 'driver') {
                $stmt = $pdo->prepare("
                    SELECT d.*, u.username as customer_name,
                        (SELECT COUNT(*) FROM deliveries WHERE driver_id = ? AND status = 'completed') as completed_count,
                        (SELECT SUM(fee) FROM deliveries WHERE driver_id = ? AND status = 'completed') as total_earnings
                    FROM deliveries d 
                    LEFT JOIN users u ON d.customer_id = u.id 
                    WHERE d.status = 'pending' 
                       OR (d.driver_id = ? AND d.status IN ('active','picked_up','completed'))
                    ORDER BY d.created_at DESC
                ");
                $stmt->execute([$id, $id, $id]);
                $rows = $stmt->fetchAll();
                // --- FIX: Always set stats, even if $rows is empty ---
                $stats = [
                    'completed_count' => 0,
                    'total_earnings' => 0
                ];
                if (!empty($rows)) {
                    $stats['completed_count'] = $rows[0]['completed_count'] ?? 0;
                    $stats['total_earnings'] = $rows[0]['total_earnings'] ?? 0;
                }
                
                echo json_encode(['deliveries' => $rows, 'stats' => $stats]);
            } elseif ($type === 'customer') {
                $stmt = $pdo->prepare("SELECT * FROM deliveries WHERE customer_id = ? ORDER BY created_at DESC");
                $stmt->execute([$id]);
                $rows = $stmt->fetchAll();
                
                error_log("Found " . count($rows) . " deliveries for customer"); 
                echo json_encode(['deliveries' => $rows]);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (
                !$input ||
                !isset($input['customer_id'], $input['delivery_address'], 
                       $input['delivery_type'], $input['fee'], 
                       $input['package_info'], $input['priority'])
            ) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO deliveries 
                (customer_id, delivery_address, delivery_type, fee, package_info, 
                 priority, notes, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
            $stmt->execute([
                $input['customer_id'],
                $input['delivery_address'],
                $input['delivery_type'],
                $input['fee'],
                $input['package_info'],
                $input['priority'],
                $input['notes'] ?? ''
            ]);
            echo json_encode(['success' => true]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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
            } elseif ($input['action'] === 'picked_up' && isset($input['driver_id'])) {
                // --- Handle picked up action ---
                $stmt = $pdo->prepare("UPDATE deliveries SET status = 'picked_up', updated_at = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->execute([$id, $input['driver_id']]);
                echo json_encode(['success' => true]);
            } elseif ($input['action'] === 'complete' && isset($input['driver_id'])) {
                $stmt = $pdo->prepare("UPDATE deliveries SET status = 'completed', updated_at = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->execute([$id, $input['driver_id']]);
                $stmt = $pdo->prepare("SELECT customer_id FROM deliveries WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row && $row['customer_id']) {
                    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
                    $stmt->execute([$row['customer_id'], 'Your order has been completed by the driver.']);
                }
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM deliveries WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        break;
    case 'notifications':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $user_id = $_GET['user_id'] ?? '';
            if (!$user_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing user_id']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            echo json_encode(['notifications' => $stmt->fetchAll()]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['user_id'], $input['message'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
            $stmt->execute([$input['user_id'], $input['message']]);
            echo json_encode(['success' => true]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing id']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        break;
    case 'stats':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $driver_id = $_GET['driver_id'] ?? null;
            
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(*) as totalDeliveries,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completedDeliveries,
                        SUM(CASE WHEN status = 'completed' THEN fee ELSE 0 END) as earnings,
                        AVG(CASE 
                            WHEN status = 'completed' 
                            THEN TIMESTAMPDIFF(MINUTE, created_at, updated_at) 
                            ELSE NULL 
                        END) as avgDeliveryTime
                    FROM deliveries 
                    WHERE DATE(created_at) = CURDATE()
                    AND (driver_id = ? OR driver_id IS NULL)
                ");
                
                $stmt->execute([$driver_id]);
                $stats = $stmt->fetch();
                
                echo json_encode([
                    'totalDeliveries' => (int)$stats['totalDeliveries'],
                    'completedDeliveries' => (int)$stats['completedDeliveries'],
                    'earnings' => (float)$stats['earnings'],
                    'avgDeliveryTime' => round((float)$stats['avgDeliveryTime'] ?? 0),
                    'rating' => 4.5
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch stats']);
            }
        }
        break;
    case 'driver_location':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input || !isset($input['driver_id'], $input['lat'], $input['lng'])) {
                    throw new Exception('Missing required fields');
                }
                
                // Validate coordinates
                $lat = floatval($input['lat']);
                $lng = floatval($input['lng']);
                if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    throw new Exception('Invalid coordinates');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO driver_locations (driver_id, lat, lng, updated_at) 
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        lat = VALUES(lat),
                        lng = VALUES(lng),
                        updated_at = VALUES(updated_at)
                ");
                
                if (!$stmt->execute([$input['driver_id'], $lat, $lng])) {
                    throw new Exception('Failed to update location');
                }
                
                echo json_encode(['success' => true]);
                
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $driver_id = $_GET['driver_id'] ?? '';
                if (!$driver_id) {
                    throw new Exception('Missing driver_id');
                }
                
                $stmt = $pdo->prepare("
                    SELECT lat, lng, updated_at 
                    FROM driver_locations 
                    WHERE driver_id = ? 
                    AND updated_at >= NOW() - INTERVAL 5 MINUTE
                ");
                
                if (!$stmt->execute([$driver_id])) {
                    throw new Exception('Failed to fetch location');
                }
                
                $location = $stmt->fetch();
                if ($location) {
                    echo json_encode(['location' => $location]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Location not found or too old']);
                }
                
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Unknown endpoint']);
}