<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: PUT, GET, HEAD, POST, DELETE, PATCH, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
header("Content-Type: application/json");


$host = 'localhost';
$username = 'root';
$password = '';
$database = 'xinstitutedb';
$port = 3306;


$conn = new mysqli($host, $username, $password, $database, $port);


if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}


$json_data = file_get_contents("php://input");


$data = json_decode($json_data, true); 


$endpoint = $_SERVER['REQUEST_URI'];


if (strpos($endpoint, '/addStudents') !== false) {
    $table = 'students';
    $id_field = 'student_id';
    $data_fields = ['first_name', 'last_name', 'birthday', 'address', 'contact_number', 'course_name'];
    handleInsertion($conn, $data, $table, $id_field, $data_fields);
} elseif (strpos($endpoint, '/addCourses') !== false) {
    $table = 'courses';
    $id_field = 'course_id';
    $data_fields = ['course_name', 'department', 'fee'];
    handleInsertion($conn, $data, $table, $id_field, $data_fields);
} elseif (strpos($endpoint, '/showAllUsers') !== false) {
    handleShowAll($conn, 'students');
} elseif (strpos($endpoint, '/showUserById') !== false) {
    $id = $_GET['id']; 
    handleShowById($conn, 'students', $id);
} elseif (strpos($endpoint, '/showAllCourses') !== false) {
    handleShowAll($conn, 'courses');
} else {
    echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
}


$conn->close();


function handleInsertion($conn, $data, $table, $id_field, $data_fields) {
    $sql = "INSERT INTO $table (";
    $sql .= implode(',', $data_fields);
    $sql .= ") VALUES (";
    $values = [];
    foreach ($data_fields as $field) {
        $values[] = "'" . (isset($data[$field]) ? $data[$field] : '') . "'";
    }
    $sql .= implode(',', $values);
    $sql .= ")";

    if ($conn->query($sql) === TRUE) {
        $inserted_id = $conn->insert_id;
        $response = [
            "status" => "success",
            "message" => "Data added successfully",
            "data" => [
                $id_field => $inserted_id,
                "details" => $data
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}


function handleShowAll($conn, $table) {
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $records]);
    } else {
        echo json_encode(["status" => "error", "message" => "No records found"]);
    }
}


function handleShowById($conn, $table, $id) {
    $sql = "SELECT * FROM $table WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $record]);
    } else {
        echo json_encode(["status" => "error", "message" => "Record not found"]);
    }
}
?>
