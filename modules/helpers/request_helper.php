<?php
/**
 * Helper functions for handling HTTP requests
 */

/**
 * Check if the current request is a POST request
 * @return bool
 */
function is_post_request() {
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if the current request is a GET request
 * @return bool
 */
function is_get_request() {
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get a POST parameter with optional default value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_post_param($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Get a GET parameter with optional default value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_get_param($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Get a request parameter (POST or GET) with optional default value
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_request_param($key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}

/**
 * Validate that required POST parameters are present
 * @param array $required_params
 * @return array [bool $is_valid, array $missing_params]
 */
function validate_post_params($required_params) {
    $missing = [];
    foreach ($required_params as $param) {
        if (!isset($_POST[$param]) || empty($_POST[$param])) {
            $missing[] = $param;
        }
    }
    return [empty($missing), $missing];
}

/**
 * Validate that required GET parameters are present
 * @param array $required_params
 * @return array [bool $is_valid, array $missing_params]
 */
function validate_get_params($required_params) {
    $missing = [];
    foreach ($required_params as $param) {
        if (!isset($_GET[$param]) || empty($_GET[$param])) {
            $missing[] = $param;
        }
    }
    return [empty($missing), $missing];
}

/**
 * Get all POST parameters
 * @return array
 */
function get_all_post_params() {
    return $_POST;
}

/**
 * Get all GET parameters
 * @return array
 */
function get_all_get_params() {
    return $_GET;
}

/**
 * Check if a POST parameter exists
 * @param string $key
 * @return bool
 */
function has_post_param($key) {
    return isset($_POST[$key]);
}

/**
 * Check if a GET parameter exists
 * @param string $key
 * @return bool
 */
function has_get_param($key) {
    return isset($_GET[$key]);
}

/**
 * Get the current request method
 * @return string
 */
function get_request_method() {
    return $_SERVER['REQUEST_METHOD'] ?? 'CLI';
}

/**
 * Check if the current request is an AJAX request
 * @return bool
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get the request URI
 * @return string
 */
function get_request_uri() {
    return $_SERVER['REQUEST_URI'] ?? '';
}

/**
 * Get the request path (without query string)
 * @return string
 */
function get_request_path() {
    $uri = get_request_uri();
    $path = parse_url($uri, PHP_URL_PATH);
    return $path ?: '/';
}

/**
 * Get the query string
 * @return string
 */
function get_query_string() {
    return $_SERVER['QUERY_STRING'] ?? '';
}

/**
 * Get the referer URL
 * @return string|null
 */
function get_referer() {
    return $_SERVER['HTTP_REFERER'] ?? null;
}

/**
 * Get the client's IP address
 * @return string
 */
function get_client_ip() {
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? 
          'unknown';
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

/**
 * Get the user agent string
 * @return string
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

/**
 * Check if the current request is over HTTPS
 * @return bool
 */
function is_https() {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

/**
 * Get the host name from the current request
 * @return string
 */
function get_host() {
    return $_SERVER['HTTP_HOST'] ?? '';
}

/**
 * Get the full URL of the current request
 * @return string
 */
function get_current_url() {
    $protocol = is_https() ? 'https' : 'http';
    $host = get_host();
    $uri = get_request_uri();
    return "$protocol://$host$uri";
}

/**
 * Clean and validate input data
 * @param mixed $data
 * @return mixed
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get a cleaned POST parameter
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_clean_post($key, $default = null) {
    return isset($_POST[$key]) ? clean_input($_POST[$key]) : $default;
}

/**
 * Get a cleaned GET parameter
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_clean_get($key, $default = null) {
    return isset($_GET[$key]) ? clean_input($_GET[$key]) : $default;
}

/**
 * Redirect to a URL
 * @param string $url
 * @param int $status_code
 */
function redirect($url, $status_code = 302) {
    header("Location: $url", true, $status_code);
    exit();
}

/**
 * Send a JSON response
 * @param mixed $data
 * @param int $status_code
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Check if the current request accepts JSON responses
 * @return bool
 */
function accepts_json() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return strpos($accept, 'application/json') !== false;
}

/**
 * Get the content type of the current request
 * @return string
 */
function get_content_type() {
    return $_SERVER['CONTENT_TYPE'] ?? '';
}

/**
 * Check if the current request has a specific content type
 * @param string $type
 * @return bool
 */
function has_content_type($type) {
    $current = get_content_type();
    return stripos($current, $type) !== false;
}

/**
 * Get raw input data from the request body
 * @return string
 */
function get_raw_input() {
    return file_get_contents('php://input');
}

/**
 * Parse JSON input from the request body
 * @return array|null
 */
function get_json_input() {
    if (!has_content_type('application/json')) {
        return null;
    }
    
    $raw = get_raw_input();
    $data = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $data : null;
} 