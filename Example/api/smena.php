<?php
include("connection.php");
include("auth.php");

// Authenticate the user
$auth = new authObj();
$user = $auth->authenticate(); // Validate JWT and get the user info
$user_id = $user['user_id']; // You can use this in the logic if needed

$db = new dbObj();
$connection = $db->getConnstring();

$request_method = $_SERVER["REQUEST_METHOD"];
switch($request_method) {
    case 'GET':
        if (!empty($_GET["smena_id"])) {
            // Get a specific smena by its ID
            $smena_id = intval($_GET["smena_id"]);
            getSmena($smena_id);
        } else {
            // Get all smenas or filtered smenas
            getAllSmenas();
        }
        break;
    
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        insertSmena($data);
        break;
    
    case 'PUT':
        $smena_id = intval($_GET["smena_id"]);
        $data = json_decode(file_get_contents('php://input'), true);
        updateSmena($smena_id, $data);
        break;
    
    case 'DELETE':
        $smena_id = intval($_GET["smena_id"]);
        deleteSmena($smena_id);
        break;

    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(["error" => "Method Not Allowed"]);
        break;
}

// Get all smenas with optional filters
function getAllSmenas() {
    global $connection;

    // Initialize an empty array to store query conditions
    $conditions = [];

    // Check for each filter parameter in the request and add a corresponding condition if present
    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $conditions[] = "user_id = '$user_id'";
    }
    if (isset($_GET['time_st'])) {
        $time_st = $connection->real_escape_string($_GET['time_st']);
        $conditions[] = "time_st = '$time_st'";
    }
    if (isset($_GET['time_end'])) {
        $time_end = $connection->real_escape_string($_GET['time_end']);
        $conditions[] = "time_end = '$time_end'";
    }
    if (isset($_GET['photo_st'])) {
        $photo_st = $connection->real_escape_string($_GET['photo_st']);
        $conditions[] = "photo_st = '$photo_st'";
    }
    if (isset($_GET['photo_end'])) {
        $photo_end = $connection->real_escape_string($_GET['photo_end']);
        $conditions[] = "photo_end = '$photo_end'";
    }
    if (isset($_GET['building'])) {
        $building = $connection->real_escape_string($_GET['building']);
        $conditions[] = "(building = '$building')";
    }
    if (isset($_GET['building2'])) {
        $building2 = $connection->real_escape_string($_GET['building2']);
        $conditions[] = "(building2 = '$building2')";
    }
    if (isset($_GET['location'])) {
        $location = $connection->real_escape_string($_GET['location']);
        $conditions[] = "location = '$location'";
    }
    if (isset($_GET['photo_problem'])) {
        $photo_problem = $connection->real_escape_string($_GET['photo_problem']);
        $conditions[] = "photo_problem = '$photo_problem'";
    }
    if (isset($_GET['description_problem'])) {
        $description_problem = $connection->real_escape_string($_GET['description_problem']);
        $conditions[] = "description_problem = '$description_problem'";
    }
    if (isset($_GET['confirmed'])) {
        $confirmed = intval($_GET['confirmed']); // Assuming confirmed is a boolean or int
        $conditions[] = "confirmed = '$confirmed'";
    }
    if (isset($_GET['time_moved'])) {
        $time_moved = $connection->real_escape_string($_GET['time_moved']);
        $conditions[] = "time_moved = '$time_moved'";
    }
    if (isset($_GET['custom_object1'])) {
        $custom_object1 = $connection->real_escape_string($_GET['custom_object1']);
        $conditions[] = "custom_object1 = '$custom_object1'";
    }
    if (isset($_GET['custom_object2'])) {
        $custom_object2 = $connection->real_escape_string($_GET['custom_object2']);
        $conditions[] = "custom_object2 = '$custom_object2'";
    }
    if (isset($_GET['problem'])) {
        $problem = $connection->real_escape_string($_GET['problem']);
        $conditions[] = "problem = '$problem'";
    }
  // Now, check for filters based on the `users` table
    if (isset($_GET['Familia'])) {
        $familia = $connection->real_escape_string($_GET['Familia']);
        $conditions[] = "users.Familia LIKE '%$familia%'";
    }
    if (isset($_GET['Name'])) {
        $name = $connection->real_escape_string($_GET['Name']);
        $conditions[] = "users.Name LIKE '%$name%'";
    }
    if (isset($_GET['Otchestvo'])) {
        $name = $connection->real_escape_string($_GET['Otchestvo']);
        $conditions[] = "users.Otchestvo LIKE '%$otchestvo%'";
    }
    if (isset($_GET['Job'])) {
        $job = $connection->real_escape_string($_GET['Job']);
        $conditions[] = "users.Job LIKE '%$job%'";
    }
    if (isset($_GET['dolzhnost'])) {
        $dolzhnost = $connection->real_escape_string($_GET['dolzhnost']);
        $conditions[] = "users. dolzhnost LIKE '%$dolzhnost%'";
    }
    // Add more filters based on the `users` table as needed...

    // Build the base SQL query with a LEFT JOIN to the users table
    $sql = "SELECT smena.*, users.Familia, users.Name, users.Otchestvo, users.Job, users.dolzhnost 
            FROM smena 
            LEFT JOIN users ON smena.user_id = users.id";

    // If there are conditions, add them to the SQL query
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Execute the query
    $result = $connection->query($sql);

    // Prepare the response
    $response = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($response, $row);
        }
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}



// Get a specific smena by smena_id
function getSmena($smena_id) {
    global $connection;
    $sql = "SELECT * FROM smena WHERE smena_id = '".$smena_id."'";
    $result = $connection->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["error" => "Smena not found"]);
    }
}

function insertSmena($data)
{
    global $connection, $user_id;
    $time_st = $data['time_st'];
    $time_end = $data['time_end'];
    $photo_st = $data['photo_st'];
    $photo_end = $data['photo_end'];
    $building = $data['building'];
    $building2 = $data['building2'];
    $location = $data['location'];
    $photo_problem = $data['photo_problem'];
    $description_problem = $data['description_problem'];
    $confirmed = $data['confirmed'];
    $time_moved = $data['time_moved'];
    $custom_object1 = $data['custom_object1'];
    $custom_object2 = $data['custom_object2'];
    $problem = $data['problem'];

    $sql = "INSERT INTO smena (user_id, time_st, time_end, photo_st, photo_end, building, building2, location, photo_problem, description_problem, confirmed, time_moved, custom_object1, custom_object2, problem) 
            VALUES ('$user_id', '$time_st', '$time_end', '$photo_st', '$photo_end', '$building', '$building2', '$location', '$photo_problem', '$description_problem', '$confirmed', '$time_moved', '$custom_object1', '$custom_object2', '$problem')";

    if ($connection->query($sql)) {
        $smena_id = $connection->insert_id; // Get the last inserted ID
        if ($smena_id) {
            header("HTTP/1.0 201 Created");
            echo json_encode([
                "message" => "Smena entry created successfully",
                "smena_id" => $smena_id // Return the created smena_id
            ]);
        } else {
            // Insert successful but could not retrieve insert ID
            header("HTTP/1.0 500 Internal Server Error");
            echo json_encode([
                "error" => "Insert successful, but failed to retrieve smena_id",
                "insert_id_value" => $smena_id, // See the raw value
                "sql" => $sql, // Log the SQL query for debugging
                "sql_error" => $connection->error // Log any SQL error
            ]);
        }
    } else {
        // Insert failed, log error
        header("HTTP/1.0 400 Bad Request");
        echo json_encode([
            "error" => "Failed to create smena entry",
            "sql" => $sql, // Log the SQL query for debugging
            "sql_error" => $connection->error // Log any SQL error
        ]);
    }
}


// UPDATE an existing smena entry
function updateSmena($smena_id, $data)
{
    global $connection;

    // Fetch current data for the smena entry based on smena_id only
    $sql = "SELECT * FROM smena WHERE smena_id='$smena_id'";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        $existingData = $result->fetch_assoc();

        // Use the new values if provided, otherwise keep the existing values
        $time_st = isset($data['time_st']) ? $data['time_st'] : $existingData['time_st'];
        $time_end = isset($data['time_end']) ? $data['time_end'] : $existingData['time_end'];
        $photo_st = isset($data['photo_st']) ? $data['photo_st'] : $existingData['photo_st'];
        $photo_end = isset($data['photo_end']) ? $data['photo_end'] : $existingData['photo_end'];
        $building = isset($data['building']) ? $data['building'] : $existingData['building'];
        $building2 = isset($data['building2']) ? $data['building2'] : $existingData['building2'];
        $location = isset($data['location']) ? $data['location'] : $existingData['location'];
        $photo_problem = isset($data['photo_problem']) ? $data['photo_problem'] : $existingData['photo_problem'];
        $description_problem = isset($data['description_problem']) ? $data['description_problem'] : $existingData['description_problem'];
        $confirmed = isset($data['confirmed']) ? $data['confirmed'] : $existingData['confirmed'];
        $time_moved = isset($data['time_moved']) ? $data['time_moved'] : $existingData['time_moved'];
        $custom_object1 = isset($data['custom_object1']) ? $data['custom_object1'] : $existingData['custom_object1'];
        $custom_object2 = isset($data['custom_object2']) ? $data['custom_object2'] : $existingData['custom_object2'];
        $problem = isset($data['problem']) ? $data['problem'] : $existingData['problem'];

        // Update the smena entry with new or existing data
        $sql = "UPDATE smena SET 
                time_st='$time_st', 
                time_end='$time_end', 
                photo_st='$photo_st', 
                photo_end='$photo_end', 
                building='$building', 
                building2='$building2', 
                location='$location', 
                photo_problem='$photo_problem', 
                description_problem='$description_problem', 
                confirmed='$confirmed', 
                time_moved='$time_moved', 
                custom_object1='$custom_object1', 
                custom_object2='$custom_object2', 
                problem='$problem'
                WHERE smena_id='$smena_id'";

        if ($connection->query($sql)) {
            header("HTTP/1.0 200 OK");
            echo json_encode(["message" => "Smena entry updated successfully"]);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(["error" => "Failed to update smena entry"]);
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(["error" => "Smena entry not found"]);
    }
}



// DELETE an existing smena entry
function deleteSmena($smena_id)
{
    global $connection, $user_id;
    $sql = "DELETE FROM smena WHERE smena_id='$smena_id' AND user_id='$user_id'";
    if ($connection->query($sql)) {
        header("HTTP/1.0 204 No Content");
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo json_encode(["error" => "Failed to delete smena entry"]);
    }
}

?>