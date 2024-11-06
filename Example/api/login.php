<?php
include("connection.php");
include("auth.php");

$db = new dbObj();
$connection = $db->getConnstring();

$data = json_decode(file_get_contents('php://input'), true);
$phone_number = $data['phone_number'];
$password = $data['password'];

$sql = "SELECT id FROM users WHERE phone_number='".$phone_number."' AND password='".$password."'";
$result = $connection->query($sql);

if ($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$user_id = $row['id'];
	$auth = new authObj();
	$jwt = $auth->generateJWT($user_id);

	$response = array(
		"status" => 1,
		"token" => $jwt,
		"user_id" => $user_id
	);
	header('Content-Type: application/json');
	echo json_encode($response);
} else {
	header("HTTP/1.0 401 Unauthorized");
	echo json_encode(["error" => "Invalid phone number or password"]);
}
?>