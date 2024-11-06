<?php
include("connection.php");

$db = new dbObj();
$connection = $db->getConnstring();

$data = json_decode(file_get_contents('php://input'), true);
$phone_number = $data['phone_number'];
$password = $data['password'];
$Familia = $data['Familia'];
$Name = $data['Name'];
$Otchestvo = $data['Otchestvo'];
$Job = $data['Job'];
$otdel = $data['otdel'];
$dolzhnost = $data['dolzhnost'];

$sql = "INSERT INTO users (phone_number, password, Familia, Name, Otchestvo, Job, otdel, dolzhnost) VALUES ('".$phone_number."','".$password."','".$Familia."','".$Name."','".$Otchestvo."','".$Job."','".$otdel."','".$dolzhnost."')";

if ($connection->query($sql)) {
	header("HTTP/1.0 201");
	echo json_encode(["message" => "User registered successfully"]);
} else {
	header("HTTP/1.0 400");
	echo json_encode(["error" => "User registration failed"]);
}
?>