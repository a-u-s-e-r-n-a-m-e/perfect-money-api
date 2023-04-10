<?php
require_once '../vendor/autoload.php';

use Skpassegna\PerfectmoneyPhp;


// Initialize PerfectMoney object
$pm = new PerfectMoney('your_account_id', 'your_passphrase');

// Validate webhook request
$requestData = $_POST;
$secretKey = 'your_secret_key';
if (!$pm->validateWebhookRequest($requestData, $secretKey)) {
    // Invalid request, exit
    exit('Invalid request');
}

// Process webhook request
$result = $pm->processWebhookRequest($requestData);

// Handle result
if ($result['status'] === 'success') {
    // Payment was successful, update your database or do other processing
} else {
    // Payment was not successful, handle error
    $errorMessage = $result['errorMessage'];
    // Log error, notify admin, etc.
}

// Send response back to Perfect Money
echo $result['response'];
