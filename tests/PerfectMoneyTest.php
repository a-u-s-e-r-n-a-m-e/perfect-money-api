<?php
require_once '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Skpassegna\PerfectmoneyPhp;


/**
 * Class PerfectMoneyTest
 *
 * Unit tests for the PerfectMoney class
 *
 * @author Samuel <online@skpassegna.me>
 */
class PerfectMoneyTest extends TestCase
{

    // Test the constructor method of the PerfectMoney class
    public function testConstructor()
    {
        // Initialize test values
        $accountId = 'your_account_id';
        $passphrase = 'your_passphrase';

        // Create new PerfectMoney object
        $pm = new PerfectMoney($accountId, $passphrase);

        // Assert that the object was created successfully
        $this->assertInstanceOf(PerfectMoney::class, $pm);

        // Assert that the accountId attribute was set correctly
        $this->assertEquals($accountId, $pm->accountId);

        // Assert that the passphrase attribute was set correctly
        $this->assertEquals($passphrase, $pm->passphrase);
    }


    // Test the generatePaymentForm method of the PerfectMoney class
    public function testGeneratePaymentForm()
    {
        // Initialize test values
        $amount = '10.00';
        $currency = 'USD';
        $description = 'Test payment';
        $orderId = '123456';
        $successUrl = 'http://example.com/success';
        $cancelUrl = 'http://example.com/cancel';

        // Create new PerfectMoney object
        $pm = new PerfectMoney('your_account_id', 'your_passphrase');

        // Call the generatePaymentForm method and capture the result
        $paymentForm = $pm->generatePaymentForm($amount, $currency, $description, $orderId, $successUrl, $cancelUrl);

        // Assert that the payment form HTML code was generated successfully
        $this->assertNotEmpty($paymentForm);
    }


    // Test the validateWebhookRequest method of the PerfectMoney class
    // Test the validateWebhookRequest method of the PerfectMoney class
    public function testValidateWebhookRequest()
    {
        // Create a PerfectMoney object
        $perfectMoney = new PerfectMoney("account_id", "passphrase");

        // Set up the test data
        $requestData = array(
            'PAYMENT_ID' => '123',
            'PAYEE_ACCOUNT' => 'U1234567',
            'PAYMENT_AMOUNT' => '100.00',
            'PAYMENT_UNITS' => 'USD',
            'PAYMENT_BATCH_NUM' => '456',
            'PAYER_ACCOUNT' => 'U7654321',
            'TIMESTAMPGMT' => '1234567890',
            'V2_HASH' => 'hash'
        );
        $secretKey = 'secret';

        // Call the validateWebhookRequest method
        $result = $perfectMoney->validateWebhookRequest($requestData, $secretKey);

        // Assert that the result is true
        $this->assertTrue($result);
    }


    // Test the processWebhookRequest method of the PerfectMoney class
    public function testProcessWebhookRequest()
    {
        $requestData = [
            'PAYMENT_ID' => '123456',
            'PAYEE_ACCOUNT' => 'U1234567',
            'PAYMENT_AMOUNT' => '100',
            'PAYMENT_UNITS' => 'USD',
            'PAYMENT_BATCH_NUM' => '123456789',
            'PAYER_ACCOUNT' => 'U1234567',
            'TIMESTAMPGMT' => '1619794803',
            'V2_HASH' => 'abcdef1234567890',
        ];

        $pm = new PerfectMoney('account_id', 'passphrase');

        // Call the method to be tested
        $result = $pm->processWebhookRequest($requestData);

        // Assert that the result is correct
        $this->assertTrue($result);
    }


    // Test the submitPaymentRequest method of the PerfectMoney class
    public function testSubmitPaymentRequest()
    {
        // Create PerfectMoney object
        $pm = new PerfectMoney('account_id', 'passphrase');

        // Submit payment request
        $amount = 10.00;
        $currency = 'USD';
        $description = 'Test payment';
        $orderId = '12345';
        $paymentId = $pm->submitPaymentRequest($amount, $currency, $description, $orderId);

        // Assert that payment ID is not empty
        $this->assertNotEmpty($paymentId);
    }


    // Test the checkPaymentStatus method of the PerfectMoney class
    // Test the checkPaymentStatus method of the PerfectMoney class
    public function testCheckPaymentStatus()
    {
        // Initialize PerfectMoney object
        $perfectMoney = new PerfectMoney($this->accountId, $this->passphrase);

        // Submit a payment request
        $amount = 10.00;
        $currency = 'USD';
        $description = 'Test Payment';
        $orderId = 'ORD123456';
        $paymentResponse = $perfectMoney->submitPaymentRequest($amount, $currency, $description, $orderId);

        // Check payment status
        $paymentId = $paymentResponse['PAYMENT_ID'];
        $paymentStatus = $perfectMoney->checkPaymentStatus($paymentId);

        // Assert payment status is "Waiting for payment"
        $this->assertEquals('Waiting for payment', $paymentStatus);
    }


    // Test the getTransactionHistory method of the PerfectMoney class
    public function testGetTransactionHistory()
    {
        // Set up test data
        $startDate = '2022-01-01';
        $endDate = '2022-01-31';

        // Create PerfectMoney object
        $perfectMoney = new PerfectMoney('accountId', 'passphrase');

        // Mock HTTP client
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(
                200,
                [],
                '{"result": true, "history": [{"id": "123", "status": "success", "amount": "100.00"}]}'
            ));

        // Set HTTP client
        $perfectMoney->setHttpClient($httpClientMock);

        // Call getTransactionHistory method
        $history = $perfectMoney->getTransactionHistory($startDate, $endDate);

        // Assert response
        $this->assertTrue($history['result']);
        $this->assertCount(1, $history['history']);
        $this->assertEquals('123', $history['history'][0]['id']);
        $this->assertEquals('success', $history['history'][0]['status']);
        $this->assertEquals('100.00', $history['history'][0]['amount']);
    }
}
