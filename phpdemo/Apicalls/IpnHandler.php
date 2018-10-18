<?php
include '../../amazon-pay.phar';

/*
 * 1. 要成功接收IPN发货通知需要配置有效的SSL
 * 2. 在Seller Central中配置Notification endpoint
 * 3. 在此文件中处理IPN信息source的验证以及IPN中的data
 */
// 获得IPN headers和 Message body
$headers = getallheaders();
$body = file_get_contents('php://input');

// 构造此对象时会调用内部方法根据得到的参数构造签名校验字符串，并验证是否与IPN传入的签名匹配
$ipnHandler = new AmazonPay\IpnHandler($headers, $body);

// JSON response
$msg = $ipnHandler->toJson();

echo $msg;
// Do something here
?>