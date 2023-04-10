<?php
use Skpassegna\PerfectmoneyPhp;
require_once '../vendor/autoload.php';


// Set Perfect Money account ID and passphrase
$accountId = 'your_account_id_here';
$passphrase = 'your_passphrase_here';

// Create new PerfectMoney object
$pm = new PerfectMoney($accountId, $passphrase);

// Define payment details
$amount = 10.00;
$currency = 'USD';
$description = 'Test Payment';
$orderId = 'PM' . time();
$successUrl = 'https://example.com/success.php';
$cancelUrl = 'https://example.com/cancel.php';

// Generate payment form HTML code
$paymentForm = $pm->generatePaymentForm($amount, $currency, $description, $orderId, $successUrl, $cancelUrl);

// Output payment form HTML code
echo $paymentForm;
