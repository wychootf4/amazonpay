<?php

session_start();

include '../../amazon-pay.phar';
require_once '../../config.php';

// 实例化client对象
$client = new AmazonPay\Client($amazonpay_config);
$requestParameters = array();

// 创建参数数组来设定订单详情
$requestParameters['amount']            = $game_config['amount'];
$requestParameters['currency_code']     = $amazonpay_config['currency_code'];
$requestParameters['seller_note']       = $game_config['seller_note']; // 透传
$requestParameters['seller_order_id']   = $game_config['seller_order_id']; // 透传
$requestParameters['store_name']        = $game_config['gamename'];
$requestParameters['custom_information']= $game_config['custom_information']; // 透传
$requestParameters['mws_auth_token']    = null; // 如果是其他人调用API才非空,不用动
$requestParameters['amazon_order_reference_id'] = $_POST['orderReferenceId'];

// 调用SetOrderReferenceDetails API来设定订单详情
$response = $client->setOrderReferenceDetails($requestParameters);

// 调用GetOrderReferenceDetails获取剩余的玩家信息，像是名字等
if ($client->success)
{
    $requestParameters['access_token'] = $_POST['accessToken'];
    $response = $client->getOrderReferenceDetails($requestParameters);
}

// 将Order Reference ID加到session中以便接下来在ConfirmAndAuthorize.php中使用
$_SESSION['amazon_order_reference_id'] = $_POST['orderReferenceId'];

// Pretty print the Json and then echo it for the Ajax success to take in
$json = json_decode($response->toJson());
echo json_encode($json, JSON_PRETTY_PRINT);

?>

