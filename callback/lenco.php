<?php
//this was developed by simnko jewelry fully iced from https://simnkohost.net
//incase you have issues you can contact admin on 0973215564
require_once "../../../init.php";
require_once "../../../includes/gatewayfunctions.php";
require_once "../../../includes/invoicefunctions.php";

// Retrieve gateway module configuration
$gatewayModuleName = "lenco";
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (!$gatewayParams["type"]) {
    die("Module not activated.");
}

// Get the invoice ID and payment reference
$invoiceId = $_GET['invoiceid'];
$reference = $_GET['reference'];

if (!$invoiceId || !$reference) {
    die("Invalid request.");
}

// Lenco API details
$secretKey = $gatewayParams['secretKey'];
$apiUrl = "api.lenco.co/access/v2/collections/status/$reference";

// Verify payment via Lenco API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secretKey"
]);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

// Check payment status
if ($responseData['status'] && $responseData['data']['status'] === 'successful') {
    // Payment successful, mark the invoice as paid
    $transactionId = $responseData['data']['lencoReference'];
    $amount = $responseData['data']['amount'] / 1; // Convert back to major currency unit

    addInvoicePayment($invoiceId, $transactionId, $amount, 0, $gatewayModuleName);

    // Update custom table or log if needed
    logTransaction($gatewayModuleName, $responseData, "Successful");
} else {
    // Payment failed or pending
    logTransaction($gatewayModuleName, $responseData, "Unsuccessful");
}

header("Location: " . $gatewayParams['systemurl'] . "/viewinvoice.php?id=" . $invoiceId);