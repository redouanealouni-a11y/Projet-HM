<?php

/**
 * Global helper functions for the application.
 */

/**
 * Formats a numeric amount into a currency string.
 *
 * @param float|int|string $amount The numeric amount.
 * @param string|null      $currency The currency code (e.g., 'EUR', 'USD'). Defaults to 'EUR'.
 * @return string The formatted currency string.
 */
function formatCurrency($amount, $currency = null)
{
    if ($currency === null) {
        $currency = 'EUR';
    }

    $symbols = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'DZD' => 'DA'
    ];

    $symbol = $symbols[$currency] ?? '€';
    $amount = is_numeric($amount) ? floatval($amount) : 0;

    return number_format($amount, 2, ',', ' ') . ' ' . $symbol;
}

/**
 * Formats a date string or object into 'd/m/Y' format.
 *
 * @param string|DateTime $date The date to format.
 * @return string The formatted date string.
 */
function formatDate($date)
{
    if (is_string($date)) {
        try {
            $date = new DateTime($date);
        } catch (Exception $e) {
            return 'Date invalide';
        }
    }
    return $date->format('d/m/Y');
}

/**
 * Formats a datetime string or object into 'd/m/Y H:i' format.
 *
 * @param string|DateTime $datetime The datetime to format.
 * @return string The formatted datetime string.
 */
function formatDateTime($datetime)
{
    if (is_string($datetime)) {
        try {
            $datetime = new DateTime($datetime);
        } catch (Exception $e) {
            return 'Date invalide';
        }
    }
    return $datetime->format('d/m/Y H:i');
}

/**
 * Sends a JSON response with appropriate headers.
 *
 * @param mixed $data The data to encode as JSON.
 * @param int   $status The HTTP status code to send.
 */
function jsonResponse($data, $status = 200)
{
    if (php_sapi_name() !== 'cli') {
        // Check if headers were already sent
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if (!defined('API_TESTING_MODE')) {
        exit;
    }
}

/**
 * Sends a standardized JSON error response.
 *
 * @param string $message The error message.
 * @param int    $status The HTTP status code.
 */
function handleError($message, $status = 500)
{
    // Log error for debugging
    if (APP_DEBUG) {
        error_log("API Error [{$status}]: {$message}");
    }
    
    jsonResponse([
        'error' => true,
        'success' => false,
        'message' => $message,
        'status' => $status
    ], $status);
}

/**
 * Validates that all required fields are present in an array.
 *
 * @param array $data The data to validate.
 * @param array $required_fields An array of required field names.
 * @return array An array of error messages for missing fields.
 */
function validateRequired($data, $required_fields)
{
    $errors = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && empty(trim($data[$field])))) {
            $errors[] = "Le champ '$field' est requis";
        }
    }
    return $errors;
}

/**
 * Validates that a value is numeric.
 *
 * @param mixed  $value The value to validate.
 * @param string $field_name The name of the field for the error message.
 * @return string|null An error message string if invalid, otherwise null.
 */
function validateNumber($value, $field_name = 'valeur')
{
    if (!is_numeric($value)) {
        return "Le champ '$field_name' doit être un nombre";
    }
    return null;
}

if (!function_exists('get_request_input')) {
    /**
     * Gets the raw JSON input from the request body.
     * This is wrapped in a function to allow for easier testing.
     * When API_TESTING_MODE is true, it can return a global mock variable.
     *
     * @return array The decoded JSON data.
     */
    function get_request_input()
    {
        if (defined('API_TESTING_MODE') && isset($GLOBALS['mock_input'])) {
            return $GLOBALS['mock_input'];
        }
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}

/**
 * Validates that a value is a valid email address.
 *
 * @param string $email The email address to validate.
 * @return string|null An error message string if invalid, otherwise null.
 */
function validateEmail($email)
{
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "L'adresse email n'est pas valide";
    }
    return null;
}
