<?php

session_start();

include '../../amazon-pay.phar';
require_once '../../config.php';

// Instantiate the client object with the configuration
$client = new AmazonPay\Client($amazonpay_config);
$requestParameters = array();

// Required parameters
$requestParameters['amazon_billing_agreement_id'] = $_POST['amazon_billing_agreement_id'];

// Optional parameters
$requestParameters['seller_note']       = $game_config['seller_note'];
$requestParameters['seller_order_id']   = $game_config['seller_order_id'];
$requestParameters['store_name']        = $game_config['gamename'];
$requestParameters['seller_billing_agreement_id']   = '5678-example-order';
$requestParameters['custom_information']= $game_config['custom_information'];
$requestParameters['merchant_id']       = $amazonpay_config['merchant_id'];
$requestParameters['platform_id']       = null; // only used for Solution Providers
$requestParameters['mws_auth_token']    = null;

// Make SetBillingAgreementDetails API call
$response = $client->setBillingAgreementDetails($requestParameters);

// If the API call was a success, make the GetBillingAgreementDetails API call
if ($client->success)
{
    $requestParameters['access_token'] = $_POST['accessToken']; // use this if removing address widget
    //$requestParameters['address_consent_token'] = $_POST['accessToken'];
    $response = $client->getBillingAgreementDetails($requestParameters);
}
// Adding the Amazon Billing Agreement ID to the session so that we can use it in ConfirmAndAuthorize.php
$_SESSION['amazon_billing_agreement_id'] = $_POST['amazon_billing_agreement_id'];

// Pretty print the Json and then echo it for the Ajax success to take in
$json = json_decode($response->toJson());
echo json_encode($json, JSON_PRETTY_PRINT);

?>
