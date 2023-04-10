# Notice
This project is still under development, and this is only a preview.

## **Usage**

To use the **`PerfectMoney`** class in your PHP project, you can follow these steps:

1. Include the **`PerfectMoney.php`** file in your project:

```php
use Skpassegna\PerfectmoneyPhp;
require_once '/path/to/PerfectMoney.php';
```

1. Create an instance of the **`PerfectMoney`** class:

```php
$accountId = 'your_account_id';
$passphrase = 'your_passphrase';
$pm = new PerfectMoney($accountId, $passphrase);
```

1. Use the methods of the **`PerfectMoney`** class to interact with the Perfect Money API. For example, to generate a payment form for a new payment:

```php
$amount = 10.00;
$currency = 'USD';
$description = 'Payment for your order';
$orderId = '123456789';
$successUrl = 'https://example.com/success';
$cancelUrl = 'https://example.com/cancel';
$formHtml = $pm->generatePaymentForm($amount, $currency, $description, $orderId, $successUrl, $cancelUrl);
echo $formHtml;
```

1. Handle webhook requests from Perfect Money to update the payment status:

```php
$requestData = $_POST;
$secretKey = 'your_secret_key';
$isRequestValid = $pm->validateWebhookRequest($requestData, $secretKey);
if ($isRequestValid) {
    $paymentId = $requestData['PAYMENT_ID'];
    $paymentBatchNum = $requestData['PAYMENT_BATCH_NUM'];
    $paymentStatus = $requestData['PAYMENT_STATUS'];
    // Update payment status in your database
    $pm->processWebhookRequest($requestData);
    echo 'Payment status updated';
} else {
    echo 'Invalid webhook request';
}
```

## **Testing**

This package comes with unit tests that you can run to ensure that everything is working correctly. To run the tests, follow these steps:

1. Copy the **`phpunit.xml.dist`** file to **`phpunit.xml`**:

```bash
cp phpunit.xml.dist phpunit.xml
```

1. Edit the **`phpunit.xml`** file and replace the **`YOUR_ACCOUNT_ID`**, **`YOUR_PASSPHRASE`**, and **`YOUR_SECRET_KEY`** placeholders with your own values.
2. Run the tests using PHPUnit:

```bash
vendor/bin/phpunit
```

## **Feedback**

If you find any issues with this package, please feel free to open an issue on the GitHub repository or contact me directly. I welcome any feedback or suggestions for improvement.

## **License**

This package is open source and available under the MIT License.