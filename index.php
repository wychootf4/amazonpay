<?php
    session_start();
    require_once "config.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <link rel="stylesheet" href="phpdemo/css/sample.css">
    </head>

    <body>
        <div class="container">

            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="#">Amazon Pay Demo</a>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li><a id="Logout" href="#">logout并返回支付初始页面</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="jumbotron jumbotroncolor" style="padding-top:25px;" id="api-content">
                <div id="section-content">
                
                    <h2><?php echo $game_config['gamename']; ?>（游戏名）</h2>
                    <p style="margin-top:20px;">
                        自定义内容，不需要可以去掉
                    </p>

                    <div class="panel panel-default" style="margin-top:25px;">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>商品名</th>
                                            <th>描述</th>
                                            <th class="text-center">元宝数</th>
                                            <th class="text-center">总金额(日元)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><div class="btn btn-default"><img class="media-object" src="phpdemo/images/icon.png" alt="PHP SDK"></div> <?php echo $game_config['itemName']; ?></td>
                                            <td>
                                                <div><strong>
                                                    <?php echo $game_config['despTitle']; ?>
                                                </strong></div>
                                                <div><em>
                                                    <?php echo $game_config['despContent']; ?>
                                                </em></div>
                                            </td>
                                            <td class="text-center"><?php echo $game_config['quantity']; ?></td>
                                            <td class="text-center"><?php echo $game_config['amount']; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-center" style="margin-top:40px;" id="AmazonPayOneTimeButton"></div>
                    <div class="text-center" style="margin-top:40px;" id="AmazonPayAutoButton"></div>
                    <div class="text-center" style="margin-top:10px;">
                        <label><input type="checkbox" id="switch" checked>不使用AutoPay请取消勾选，转为使用一次性付款(One-Time payment)</label>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-danger" id="cancelButton" style="margin-top:20px">取消支付</button>
                    </div>
                </div>
            </div>

            <pre>
                <p>此页面显示的参数在config.php中进行配置，一部分参数需要从前端传入，详见config.php</p>
                <p>点击AmazonPay按钮，弹出窗口中输入测试账号密码，首次使用会向玩家请求授权。测试账号密码见整合文档中的参数文件</p>
                <p>账户登录结束后跳转到支付方式选择页面SetPaymentDetails.php</p>
            </pre>
        </div>
        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
        <script type='text/javascript'>
            var checkbox = $('#switch');
            var onetimeButton = $('#AmazonPayOneTimeButton');
            var autoPayButton = $('#AmazonPayAutoButton');
            onetimeButton.hide();
            checkbox.on('click', function() {
                if ($(this).is(':checked')) {
                    onetimeButton.hide();
                    autoPayButton.show();
                } else {
                    autoPayButton.hide();
                    onetimeButton.show();
                }
            });

            // 此回调设定client id，用来渲染AddressBook和Wallet widget
            window.onAmazonLoginReady = function () {
                try {
                    // 设定client id
                    amazon.Login.setClientId("<?php echo $amazonpay_config['client_id']; ?>");
                    amazon.Login.setUseCookie(true);
                } catch (err) {
                    alert(err);
                }
            };
            // 此回调会渲染AmazonPay button
            window.onAmazonPaymentsReady = function () {
                var authRequest;
                // button的参数列表：https://pay.amazon.com/us/developer/documentation/lpwa/201953980
                // AmazonPay one-time payment button
                OffAmazonPayments.Button("AmazonPayOneTimeButton", "<?php echo $amazonpay_config['merchant_id']; ?>", {
                    type: "PwA",       // PwA, Pay, A, LwA, Login
                    color: "LightGray", // Gold, LightGray, DarkGray
                    size: "medium",    // small, medium, large, x-large
                    language: "en-GB", // for Europe/UK regions only: en-GB, de-DE, fr-FR, it-IT, es-ES
                    authorization: function() {
                       // https://pay.amazon.com/us/developer/documentation/lpwa/201953170 如果popup设为false，用自己的redirect url处理
                       // 如果popup设为false需要为所有redirect url设置白名单
                        loginOptions = { scope: "profile payments:widget", popup: false };
                        authRequest = amazon.Login.authorize(loginOptions, "phpdemo/SetPaymentDetails.php");
                    },
                    onError: function(error) {
                        alert("出错了！具体内容如下：" + error.getErrorCode() + " - " + error.getErrorMessage());
                    }
                });
                // AmazonPay AutoPay button
                OffAmazonPayments.Button("AmazonPayAutoButton", "<?php echo $amazonpay_config['merchant_id']; ?>", {
                    type: "PwA",       // PwA, Pay, A, LwA, Login
                    color: "DarkGray", // Gold, LightGray, DarkGray
                    size: "medium",    // small, medium, large, x-large
                    language: "en-GB", // for Europe/UK regions only: en-GB, de-DE, fr-FR, it-IT, es-ES
                    authorization: function() {
                        loginOptions = { scope: "profile payments:widget", popup: false };
                        //authRequest = amazon.Login.authorize(loginOptions, "autodemo/SetPaymentDetails.php");
                        authRequest = amazon.Login.authorize(loginOptions, "autodemo/ConfirmPaymentAndAuthorize.php");
                        // Authorize的redirect进行下跳转判定，如果是有ID直接走ConfirmAndAuthorize，如果没有正常走SetPaymentDetails
                    },
                    onError: function(error) {
                        // something bad happened
                    }
                });
                // 退出登录
                document.getElementById('Logout').onclick = function() {
                    amazon.Login.logout();
                    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                    window.location = 'index.php';
                };

                // 回到游戏
                document.getElementById('cancelButton').onclick = function() {
                    amazon.Login.logout();
                    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                    window.location = '<?php echo $game_config['gameurl']; ?>'
                }
            };
        </script>
        <!-- 调用对应的widget js来渲染AddressBook和Wallet widget-->
        <script async="async" type='text/javascript' src="<?php echo getWidgetsJsURL($amazonpay_config); ?>"></script>

    </body>
</html>
