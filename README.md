# API Documentation

This is a RESTful API for managing users, shifts (`smena`), and buildings, with JWT-based authentication for security. The system allows administrators and users to perform CRUD operations for buildings, shifts, and users.

## Files Overview

- **index.php**: A simple HTML file that displays a "Hello" message.
- **users.php**: Manages CRUD operations for users (Create, Read, Update, Delete).
- **smena.php**: Manages CRUD operations for shifts (`smena`).
- **signup.php**: Endpoint for user registration.
- **login.php**: Endpoint to authenticate users and generate a JWT token.
- **building.php**: Manages CRUD operations for buildings.
- **connection.php**: Contains the database connection logic.
- **auth.php**: Contains logic for JWT authentication and validation.
- **.htaccess**: Configures URL routing and sets headers for CORS (Cross-Origin Resource Sharing).

## Database Structure

The API interacts with a MySQL database. The expected database tables are:

- **users**: Stores information about the users, such as phone number, password, name, job, and position.
- **smena**: Stores information about shifts, including the time, location, photos, problems, and more.
- **buildings**: Stores information about buildings, including their names and validity status.

## Authentication

- Authentication is done via JWT (JSON Web Tokens).
- The `auth.php` file contains the logic to authenticate users via JWT. Users need to provide their credentials via the `login.php` endpoint to receive a token, which is then used to authenticate further requests.

## Error Codes

- `200 OK`: Successful request.
- `201 Created`: Successfully created resource.
- `204 No Content`: Successfully deleted resource.
- `400 Bad Request`: Invalid request, check input data.
- `401 Unauthorized`: Invalid credentials.
- `404 Not Found`: Resource not found.
- `405 Method Not Allowed`: HTTP method not supported.
- `500 Internal Server Error`: Error processing the request.

## Requirements

- PHP >= 7.0
- MySQL or MariaDB database
- JWT for authentication (included in `auth.php`)
- A web server like Apache or Nginx.

## Setup

1. Configure your database connection in `connection.php`.
2. Set up JWT authentication in `auth.php`.
3. Run the API on your server and use tools like Postman or cURL to interact with the endpoints.

## .htaccess Configuration

The `.htaccess` file is configured for URL rewriting and cross-origin resource sharing (CORS). This configuration allows clean URLs and enables access from different origins.

```apache
RewriteEngine On

<IfModule mod_headers.c>
    Header Set Access-Control-Allow-Origin "*"
    Header Set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header Set Access-Control-Allow-Headers "Origin, Content-Type, Authorization, X-Requested-With"
</IfModule>

# Route requests to /smena and /smena/[id] to smena.php
RewriteRule ^smena/?$ smena.php [NC,L]
RewriteRule ^smena/([0-9]+)/?$ smena.php?smena_id=$1 [NC,L]

# Route requests to /users and /users/[id] to users.php
RewriteRule ^users/?$ users.php [NC,L]
RewriteRule ^users/([0-9]+)/?$ users.php?id=$1 [NC,L]

# Route requests to /buildings and /buildings/[id] to buildings.php
RewriteRule ^buildings/?$ buildings.php [NC,L]
RewriteRule ^buildings/([0-9]+)/?$ buildings.php?building_id=$1 [NC,L]

# Route requests to /login to login.php
RewriteRule ^login/?$ login.php [NC,L]

# Route requests to /signup to signup.php
RewriteRule ^

signup/?$ signup.php [NC,L]

# Ensure Authorization header is passed to PHP
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

---

This API allows comprehensive management of users, buildings, and shifts, with the flexibility of CRUD operations and secure JWT-based authentication.
