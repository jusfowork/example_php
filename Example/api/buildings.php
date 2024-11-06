<?php
include("connection.php");
include("auth.php");

// Authenticate the user
$auth = new authObj();
$user = $auth->authenticate(); // Validate JWT and get the user info

$db = new dbObj();
$connection = $db->getConnstring();

$request_method = $_SERVER["REQUEST_METHOD"];
switch($request_method) {
    case 'GET':
        if (!empty($_GET["building_id"]) && !empty($_GET["smenas"])) {
            // Get all smenas for a specific building
            $building_id = intval($_GET["building_id"]);
            getSmenasByBuilding($building_id);
        } elseif (!empty($_GET["building_id"])) {
            // Get a specific building by its building_id
            $building_id = intval($_GET["building_id"]);
            getBuilding($building_id);
        } else {
            // Get all buildings
            getAllBuildings();
        }
        break;

    case 'POST':
        // Create a new building
        createBuilding();
        break;

    case 'PUT':
        // Update an existing building
        if (!empty($_GET["building_id"])) {
            $building_id = intval($_GET["building_id"]);
            updateBuilding($building_id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(["error" => "Building ID is required"]);
        }
        break;

    case 'DELETE':
        // Delete a specific building
        if (!empty($_GET["building_id"])) {
            $building_id = intval($_GET["building_id"]);
            deleteBuilding($building_id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(["error" => "Building ID is required"]);
        }
        break;

    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(["error" => "Method Not Allowed"]);
        break;
}

// Get all buildings or only the valid ones based on the 'include_all' parameter
function getAllBuildings() {
    global $connection;
    
    // Check if 'include_all' parameter is set and is true
    $include_all = isset($_GET['include_all']) && $_GET['include_all'] === 'true';
    
    // Adjust SQL query based on the 'include_all' parameter
    if ($include_all) {
        $sql = "SELECT * FROM buildings";
    } else {
        $sql = "SELECT * FROM buildings WHERE valid = 1"; // Fetch only valid buildings
    }

    $result = $connection->query($sql);

    $response = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($response, $row);
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}


// Get a specific building by building_id
function getBuilding($building_id) {
    global $connection;
    $sql = "SELECT * FROM buildings WHERE building_id = '".$building_id."'";
    $result = $connection->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["error" => "Building not found"]);
    }
}

// Create a new building
function createBuilding() {
    global $connection;
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'];
    $valid = intval($data['valid']);

    if (empty($name)) {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Building name is required"]);
        return;
    }

    $sql = "INSERT INTO buildings (name, valid) VALUES ('".$name."', '".$valid."')";

    if ($connection->query($sql)) {
        header("HTTP/1.0 201 Created");
        echo json_encode(["message" => "Building created successfully"]);
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Building creation failed"]);
    }
}


// Update an existing building
function updateBuilding($building_id) {
    global $connection;
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'];
    $valid = intval($data['valid']);
    
    if (empty($name)) {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Building name is required"]);
        return;
    }

    $sql = "UPDATE buildings SET name = '".$name."', valid = '".$valid."' WHERE building_id = '".$building_id."'";

    if ($connection->query($sql)) {
        header("HTTP/1.0 200 OK");
        echo json_encode(["message" => "Building updated successfully"]);
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Building update failed"]);
    }
}



// Delete a specific building (hard delete)
function deleteBuilding($building_id) {
    global $connection;

    // Perform a hard delete (completely remove the record from the table)
    $sql = "DELETE FROM buildings WHERE building_id = '".$building_id."'";

    if ($connection->query($sql)) {
        header("HTTP/1.0 204 No Content");
        echo json_encode(["message" => "Building deleted successfully"]);
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Building deletion failed"]);
    }
}


?>