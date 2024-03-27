<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: PUT, GET, HEAD, POST, DELETE, PATCH, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
header("Content-Type: application/json");


// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'xinstitutedb';
$port = 3306;

// Connect to the database
$conn = new mysqli($host, $username, $password, $database, $port);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to add a user
// Function to add a user
function addUser($conn, $username, $password) {
    // Check if the username already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        return ["status" => "error", "message" => "User already exists"];
    }

    // If username does not exist, proceed with adding the user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
    if ($conn->query($sql) === TRUE) {
        return ["status" => "success", "message" => "User added successfully"];
    } else {
        return ["status" => "error", "message" => "Failed to add user"];
    }
}


function validateLogin($conn, $username, $password) {
    // Prepare and execute a parameterized query to avoid SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $row['password'])) {
            return true; // Login successful
        } else {
            return false; // Incorrect password
        }
    } else {
        return false; // Username not found
    }
}

// Function to handle forgotten passwords (not implemented)
function forgotPassword($conn, $username, $email) {
    // Implement forgot password functionality here
    return false;
}

// Handle actions based on request parameters
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
        exit;
    }

    if (isset($data['action'])) {
        $action = $data['action'];
        switch ($action) {
            case 'addUser':
                if (isset($data['username']) && isset($data['password'])) {
                    $username = $data['username'];
                    $password = $data['password'];
                    if (!empty($username) && !empty($password)) {
                        $result = addUser($conn, $username, $password);
                        if ($result["status"] === "success") {
                            echo json_encode($result);
                        } else {
                            echo json_encode($result);
                        }
                    } else {
                        echo json_encode(["status" => "error", "message" => "Username and password cannot be empty"]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Username and password are required"]);
                }
                break;
            case 'login':
                if (isset($data['username']) && isset($data['password'])) {
                    $username = $data['username'];
                    $password = $data['password'];
                    if (validateLogin($conn, $username, $password)) {
                        echo json_encode(["status" => "success", "message" => "Login successful"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Username and password are required"]);
                }
                break;
            case 'forgotPassword':
                if (isset($data['username']) && isset($data['email'])) {
                    $username = $data['username'];
                    $email = $data['email'];
                    if (forgotPassword($conn, $username, $email)) {
                        echo json_encode(["status" => "success", "message" => "Password reset instructions sent"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Failed to send reset instructions"]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Username and email are required"]);
                }
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Invalid action"]);
        }
    }
}
?>
