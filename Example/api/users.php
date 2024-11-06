<?php
include("connection.php");
include("auth.php");

//Auth Start
$auth = new authObj();
$user = $auth->authenticate(); // Validate the JWT and get the user info
$user_id = $user['user_id']; // Assuming you want to use this user_id for further logic
//Auth End

$db = new dbObj();
$connection = $db->getConnstring();

$request_method = $_SERVER["REQUEST_METHOD"];
switch($request_method)
{
    case 'GET':
        if(!empty($_GET["id"]))
        {
            $id = intval($_GET["id"]);
            getUser($id);
        }
        else
        {
            getUsers();
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        insertUser($data["phone_number"], $data["password"], $data["Familia"], $data["Name"], $data["Otchestvo"], $data["Job"], $data["otdel"], $data["dolzhnost"]);
        break;
    case 'PUT':
        $id = intval($_GET["id"]);
        $data = json_decode(file_get_contents('php://input'), true);
        updateUser($id, $data["phone_number"], $data["password"], $data["Familia"], $data["Name"], $data["Otchestvo"], $data["Job"], $data["otdel"], $data["dolzhnost"]);
        break;
    case 'DELETE':
        $id = intval($_GET["id"]);
        deleteUser($id);
    default:
        header("HTTP/1.0 405 Method not Implemented");
        break;
}

function getUsers()
{
    global $connection;
    $sql = "SELECT * from users";
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

function getUser($id)
{
    global $connection;
    $sql = "SELECT * from users WHERE id='".$id."'";
    $result = $connection->query($sql);
    $row = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($row);
}

function insertUser($phone_number, $password, $Familia, $Name, $Otchestvo, $Job, $otdel, $dolzhnost)
{
    global $connection;
    $sql = "INSERT INTO users (phone_number, password, Familia, Name, Otchestvo, Job, otdel, dolzhnost) VALUES ('".$phone_number."','".$password."','".$Familia."','".$Name."','".$Otchestvo."','".$Job."','".$otdel."','".$dolzhnost."')";
    $response = array();
    if($connection->query($sql))
    {
        //Success
        header("HTTP/1.0 201");
        $response = array(
            'status' => 1,
            'status_message' => 'Users Added Successfully.'
        );        
    }
    else
    {
        //Failed
        header("HTTP/1.0 400");
        $response = array(
            'status' => 0,
            'status_message' => 'Users Addition Failed.'
        );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

function updateUser($id, $phone_number, $password, $Familia, $Name, $Otchestvo, $Job, $otdel, $dolzhnost)
{
    global $connection;
    $sql = "UPDATE users SET phone_number ='".$phone_number."', password ='".$password."', Familia ='".$Familia."', Name ='".$Name."', Otchestvo ='".$Otchestvo."', Job ='".$Job."', otdel ='".$otdel."', dolzhnost ='".$dolzhnost."' WHERE id='".$id."'";
    $response = array();
    if($connection->query($sql))
    {
        //Success
        $response = array(
            'status' => 1,
            'status_message' => 'Users Updated Successfully.'
        );        
    }
    else
    {
        //Failed
        header("HTTP/1.0 400");
        $response = array(
            'status' => 0,
            'status_message' => 'Users Updation Failed.'
        );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

function deleteUser($id)
{
    global $connection;
    $sql = "DELETE from users WHERE id='".$id."'";
    $response = array();
    if($connection->query($sql))
    {
        //Success
        header("HTTP/1.0 204");    
    }
    else
    {
        //Failed
        header("HTTP/1.0 400");
        $response = array(
            'status' => 0,
            'status_message' => 'Users Deletion Failed.'
        );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>