<?php

session_start();

include '../../amazon-pay.phar';
require_once '../../config.php';

// Instantiate the client object with the configuration
$client = new AmazonPay\Client($amazonpay_config);

// 创建参数数组来设定订单
$requestParameters = array();
$requestParameters['amazon_order_reference_id'] = $_SESSION['amazon_order_reference_id'];
$requestParameters['mws_auth_token'] = null;

// 调用ConfirmOrderReference将ORO设为Open state并发送支付确认邮件给玩家，在两种情况下不会发送邮件，详见文档
$response = $client->confirmOrderReference($requestParameters);

$responsearray['confirm'] = json_decode($response->toJson());

// 如果API call成功则接着调用Authorize (with Capture) API call
if ($client->success)
{
    $requestParameters['authorization_amount'] = $game_config['amount'];
    // 商家为授权创建的唯一ID，透传
    $requestParameters['authorization_reference_id'] = uniqid();
    // 邮件中所显示的交易描述，只有当CaptureNow设为true才出现，IPN
    $requestParameters['seller_authorization_note'] = $game_config['seller_authorization_note'];
    // Sync Authorization API设为0， Async最小为5，详见：https://pay.amazon.com/us/developer/documentation/lpwa/201952140
    $requestParameters['transaction_timeout'] = 0;
    // 在授权的同时就请求capture
    $requestParameters['capture_now'] = true;
    // 出现在玩家的支付说明中，只有CaptureNow设为true才出现，IPN
    $requestParameters['soft_descriptor'] = $game_config['soft_descriptor'];

    $response = $client->authorize($requestParameters);
    $responsearray['authorize'] = json_decode($response->toJson());
}

// 如果API call成功了则调用CloseOrderReference将ORO设为Close状态
if ($client->success) {
    $requestParameters['closure_reason'] = "Order Completed.";

    $response = $client->closeOrderReference($requestParameters);
    $responsearray['closeORO'] = json_decode($response->toJson());
}

// Echo the Json encoded array for the Ajax success
echo json_encode($responsearray);


?>