<?php
class authObj
{
	private $secret_key = "your_secret_key"; 
	private $issuer = "http://yourdomain.com"; 
	private $audience = "http://yourdomain.com";
	private $issued_at;
	private $expiration;

	function __construct() {
		$this->issued_at = time();
		$this->expiration = $this->issued_at + (60 * 60); // Token valid for 1 hour
	}

	// Generate JWT
	function generateJWT($user_id)
	{
		$payload = array(
			"iss" => $this->issuer,
			"aud" => $this->audience,
			"iat" => $this->issued_at,
			"exp" => $this->expiration,
			"user_id" => $user_id
		);

		$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
		$payload = json_encode($payload);

		$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
		$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
		$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

		$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
		return $jwt;
	}

	// Validate JWT
	function validateJWT($token)
	{
		$parts = explode(".", $token);
		if (count($parts) != 3) {
			header("HTTP/1.0 400");
			echo json_encode(["error" => "Invalid token format"]);
			exit;
		}

		$base64UrlHeader = $parts[0];
		$base64UrlPayload = $parts[1];
		$signatureProvided = $parts[2];

		$header = base64_decode($base64UrlHeader);
		$payload = base64_decode($base64UrlPayload);
		$payloadArray = json_decode($payload, true);

		$expiration = $payloadArray['exp'];

		if ($expiration < time()) {
			header("HTTP/1.0 401");
			echo json_encode(["error" => "Token expired"]);
			exit;
		}

		$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
		$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

		if ($base64UrlSignature !== $signatureProvided) {
			header("HTTP/1.0 401");
			echo json_encode(["error" => "Invalid token signature"]);
			exit;
		}

		// If the token is valid, return the payload (which contains the user ID)
		return $payloadArray;
	}

	function authenticate()
	{
		$headers = getallheaders();
		if (!array_key_exists('Authorization', $headers)) {
			header("HTTP/1.0 400");
			echo json_encode(["error" => "Authorization header is missing"]);
			exit;
		}

		if (substr($headers['Authorization'], 0, 7) !== 'Bearer ') {
			header("HTTP/1.0 400");
			echo json_encode(["error" => "Bearer keyword is missing"]);
			exit;
		}

		$bearerToken = trim(substr($headers['Authorization'], 7));
		return $this->validateJWT($bearerToken);
	}
}
?>