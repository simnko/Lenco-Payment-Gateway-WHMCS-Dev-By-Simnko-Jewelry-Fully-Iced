<?php
/*
this plugin was developed by Simnko jewelry fully iced from https://simnkohost.net
email: simnkoman@gmail.com
phone: 0973215564
for donations you can send ka sumfing on 0973215564

*/

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly.");
}

// configuration
function lenco_MetaData()
{
    return [
        'DisplayName' => 'Lenco Dev By Simnko', //initial release date 05,January 2025.
        'APIVersion' => '1.0',
    ];
}

function lenco_config()
{
    return [
        "FriendlyName" => [
            "Type" => "System",
            "Value" => "Lenco Dev By Simnko",
        ],
        "publicKey" => [
            "FriendlyName" => "Lenco Public Key",
            "Type" => "text",
            "Size" => "50",
            "Default" => "",
            "Description" => "Enter your Lenco public key here", //you need to get the public keys from your lenco dashboard
        ],
        "secretKey" => [
            "FriendlyName" => "Lenco Secret Key", //you need to get the sectret keys from your lenco dashboard
            "Type" => "password",
            "Size" => "50",
            "Default" => "",
            "Description" => "Enter your Lenco secret key here",
        ],
        "exchangeRate" => [
            "FriendlyName" => "USD to ZMW Exchange Rate",
            "Type" => "text",
            "Size" => "10",
            "Default" => "28.50", // exchange rate you can put based on your coutry
            "Description" => "Enter the current exchange rate for USD to ZMW.",
        ],
    ];
}


function lenco_link($params)
{
    // Parameters
    $publicKey = $params['publicKey'];
    $invoiceId = $params['invoiceid'];
    $amountUSD = $params['amount']; // Amount in USD
    $currency = $params['currency'];
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];
    $callbackUrl = $params['systemurl'] . "/modules/gateways/callback/lenco.php?invoiceid=" . $invoiceId;
    
    // Get exchange rate from configuration
    $exchangeRate = floatval($params['exchangeRate']); //fully iced 
    
    // Calculate amount in ZMW or you can replace the variables $amountZMW with your country currency
    $amountZMW = $amountUSD * $exchangeRate;
    
    // Payment Button HTML form
    $htmlOutput = <<<HTML
        <script src="https://pay.lenco.co/js/v1/inline.js"></script>
        <button type="button" onclick="getPaidWithLenco()">Pay Now</button>
        <script>
            function getPaidWithLenco() {
                LencoPay.getPaid({
                    key: '$publicKey',
                    reference: 'ref-' + Date.now(),
                    email: '$email',
                    amount: $amountZMW, // Use amount in ZMW for payment
                    currency: "ZMW",
                    customer: {
                        firstName: '{$params['clientdetails']['firstname']}',
                        lastName: '{$params['clientdetails']['lastname']}',
                        phone: '$phone',
                    },
                    onSuccess: function(response) {
                        window.location = '$callbackUrl&reference=' + response.reference;
                    },
                    onClose: function() {
                        alert('Payment was not completed.');
                    }
                });
            }
        </script>
    HTML;

    return $htmlOutput;
}