<?php
    
    namespace Skpassegna\PerfectmoneyPhp;
 /**
 * PerfectMoney.php
 *
 * A PHP class for interacting with the Perfect Money payment gateway API.
 *
 * @category  Payment Processing
 * @package   PerfectMoney API
 * @author    Samuel <online@skpassegna.me>
 * @license   MIT License
 * @link      https://github.com/a-u-s-e-r-n-a-m-e/perfect-money-api
 */

class PerfectMoney {

    // Attributes
    private $accountId;
    private $passphrase;

    // Constructor
    // Constructor
public function __construct($accountId, $passphrase) {
    $this->accountId = $accountId;
    $this->passphrase = $passphrase;
}


    // Generate payment form method
public function generatePaymentForm($amount, $currency, $description, $orderId, $successUrl, $cancelUrl) {
    // Generate payment form HTML code
    $formHtml = '<form action="https://perfectmoney.is/api/step1.asp" method="POST">
                    <input type="hidden" name="PAYEE_ACCOUNT" value="'.$this->accountId.'">
                    <input type="hidden" name="PAYEE_NAME" value="Merchant Name">
                    <input type="hidden" name="PAYMENT_ID" value="'.$orderId.'">
                    <input type="hidden" name="PAYMENT_AMOUNT" value="'.$amount.'">
                    <input type="hidden" name="PAYMENT_UNITS" value="'.$currency.'">
                    <input type="hidden" name="STATUS_URL" value="'.$successUrl.'">
                    <input type="hidden" name="PAYMENT_URL" value="'.$cancelUrl.'">
                    <input type="hidden" name="NOPAYMENT_URL" value="'.$cancelUrl.'">
                    <input type="hidden" name="BAGGAGE_FIELDS" value="ORDER_ID">
                    <input type="hidden" name="ORDER_ID" value="'.$orderId.'">
                    <input type="hidden" name="PAYMENT_METHOD" value="PerfectMoney">
                    <input type="submit" value="Submit Payment">
                </form>';
    return $formHtml;
}


    // Validate webhook request method
public function validateWebhookRequest($requestData, $secretKey) {
    // Validate webhook request data
    $hash = strtoupper(md5($requestData.$secretKey));
    if ($hash === $_SERVER['HTTP_HMAC']) {
        return true;
    } else {
        return false;
    }
}


    // Process webhook request method
public function processWebhookRequest($requestData) {
    // Parse webhook request data
    parse_str($requestData, $params);

    // Retrieve payment details from database
    $payment = $this->getPaymentByOrderId($params['PAYMENT_ID']);

    // Verify payment details
    if ($payment !== false && $payment->amount == $params['PAYMENT_AMOUNT'] && $payment->currency == $params['PAYMENT_UNITS']) {
        // Update payment status to complete
        $payment->status = 'complete';
        $payment->update();

        // Call payment complete callback function
        if ($payment->callback !== null && function_exists($payment->callback)) {
            call_user_func($payment->callback, $payment);
        }

        // Return success response
        return 'OK';
    } else {
        // Return error response
        return 'ERROR';
    }
}


    // Submit payment request method
public function submitPaymentRequest($amount, $currency, $description, $orderId) {
    // Generate payment request data
    $paymentData = array(
        'PAYEE_ACCOUNT' => $this->accountId,
        'PAYEE_NAME' => $this->accountName,
        'PAYMENT_AMOUNT' => $amount,
        'PAYMENT_UNITS' => $currency,
        'PAYMENT_ID' => $orderId,
        'STATUS_URL' => $this->webhookUrl,
        'PAYMENT_URL' => $this->successUrl,
        'NOPAYMENT_URL' => $this->cancelUrl,
        'BAGGAGE_FIELDS' => $description
    );

    // Generate payment request URL
    $paymentUrl = $this->apiUrl . '?' . http_build_query($paymentData);

    // Send HTTP request to Perfect Money API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paymentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    // Return response from Perfect Money API
    return $response;
}


    // Check payment status method
public function checkPaymentStatus($paymentId) {
    // Generate payment status data
    $statusData = array(
        'AccountID' => $this->accountId,
        'PassPhrase' => $this->passphrase,
        'PAYMENT_ID' => $paymentId
    );

    // Generate payment status URL
    $statusUrl = $this->apiUrl . '/acct/historycsv.asp?' . http_build_query($statusData);

    // Send HTTP request to Perfect Money API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse payment status from response
    $status = 'Unknown';
    $responseArray = str_getcsv($response);
    if (count($responseArray) >= 8) {
        $status = $responseArray[2];
    }

    // Return payment status
    return $status;
}


    // Get transaction history method
public function getTransactionHistory($startDate, $endDate) {
    // Generate transaction history data
    $historyData = array(
        'AccountID' => $this->accountId,
        'PassPhrase' => $this->passphrase,
        'startmonth' => date('m', strtotime($startDate)),
        'startday' => date('d', strtotime($startDate)),
        'startyear' => date('Y', strtotime($startDate)),
        'endmonth' => date('m', strtotime($endDate)),
        'endday' => date('d', strtotime($endDate)),
        'endyear' => date('Y', strtotime($endDate))
    );

    // Generate transaction history URL
    $historyUrl = $this->apiUrl . '/acct/historycsv.asp?' . http_build_query($historyData);

    // Send HTTP request to Perfect Money API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $historyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse transaction history from response
    $history = array();
    $responseArray = str_getcsv($response);
    if (count($responseArray) >= 8) {
        while (($responseArray = str_getcsv($response)) !== false) {
            $history[] = array(
                'Date' => $responseArray[0],
                'Time' => $responseArray[1],
                'Type' => $responseArray[2],
                'Amount' => $responseArray[3],
                'Fee' => $responseArray[4],
                'Batch' => $responseArray[5],
                'Currency' => $responseArray[6],
                'Description' => $responseArray[7]
            );
        }
    }

    // Return transaction history
    return $history;
}

}
