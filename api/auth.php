<?php
require_once '../config/db.php';
header('Content-Type: application/json');

class AuthService {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Register a new user
    public function register($data) {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $password2 = $data['password2'] ?? '';
        $errors = [];

        // Validation
        if ($name === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $password2 && $password2 !== '') $errors[] = 'Passwords do not match.';

        // Check if email exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email already registered.';

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        // Hash password with bcrypt
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
        if ($stmt->execute([$name, $email, $hash])) {
            return ['success' => true, 'message' => 'Registration successful.'];
        } else {
            return ['success' => false, 'errors' => ['Registration failed.']];
        }
    }

    // Login user
    public function login($data) {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $errors = [];

        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if ($password === '') $errors[] = 'Password is required.';

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        // Fetch user
        $stmt = $this->pdo->prepare("SELECT id, name, email, password_hash, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'errors' => ['Invalid email or password.']];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'errors' => ['Account is not active.']];
        }

        // Set session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        // Encrypt user info (simple base64 for demonstration)
        $userData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $encrypted = base64_encode(json_encode($userData)); // Use stronger encryption in production

        return ['success' => true, 'message' => 'Login successful.', 'user' => $encrypted];
    }
}

// Controller
$pdo = $pdo ?? null;
$auth = new AuthService($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'register') {
        echo json_encode($auth->register($input));
    } elseif ($action === 'login') {
        echo json_encode($auth->login($input));
    } else {
        echo json_encode(['success' => false, 'errors' => ['Invalid action.']]);
    }
    exit;
}

echo json_encode(['success' => false, 'errors' => ['Invalid request.']]);