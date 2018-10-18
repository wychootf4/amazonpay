<?php
    session_start();
    require_once "../config.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="css/sample.css">
        <script type='text/javascript'>
            function getURLParameter(name, source) {
                return decodeURIComponent((new RegExp('[?|&|#]' + name + '=' +
                    '([^&]+?)(&|#|;|$)').exec(source) || [,""])[1].replace(/\+/g,
                    '%20')) || null;
            }

            var accessToken = getURLParameter("access_token", location.hash);

            if (typeof accessToken === 'string' && accessToken.match(/^Atza/)) {
                document.cookie = "amazon_Login_accessToken=" + accessToken +
                    ";secure"; //remove ;secure in localhost
                //document.cookie = "amazon_Login_accessToken=" + accessToken; //remove ;secure in localhost
            }

            window.onAmazonLoginReady = function () {
                amazon.Login.setClientId('amzn1.application-oa2-client.a579231722d349d39e0c43fddbe9f3a0');

                amazon.Login.setUseCookie(true);
            };

        </script>
    </head>

    <body>

        <div class="container">

            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <a class="navbar-brand start-over" href="#">Amazon Pay Demo</a>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li><a class="start-over" href="#">logout并返回支付初始页面</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="jumbotron jumbotroncolor" style="padding-top:25px;" id="api-content">
                <div id="section-content">

                    <h2>选择支付方式</h2>
                    <p>
                         URL的querystring附带的accessToken在调用GetOrderReferenceDetails API时需要
                    </p>
                    <div class="text-center" style="margin-top:40px;">
                        <div id="walletWidgetDiv"></div>
                        <div id="consentWidgetDiv" style=""></div>
                        <p>生成wallet widget时会调用SetOrderReferenceDetails API</p>
                        <div style="clear:both;"></div>

                        <form class="form-horizontal" style="margin-top:40px;" role="form" method="post" action="ConfirmPaymentAndAuthorize.php">
                            <button id="place-order" class="btn btn-lg btn-success">确认支付</button>
                            <div id="ajax-loader" style="display:none;"><img src="images/ajax-loader.gif" /></div>
                            <button type="button" class="btn btn-danger btn-lg" id="cancelButton" style="margin-left:20px;">取消支付</button>
                        </form>
                        <p><br>
                            The "Confirm Subscription" button is disabled when either of the following conditions are true:
                            <ul>
                                <li>The consent checkbox allowing future purchases for this payment method is not checked.</li>
                                <li>The "4545" test credit card simulating the PaymentMethodNotAllowed constraint is selected.</li>
                            </ul>
                        </p>
                    </div>
                </div>
            </div>
            <pre>
                <p>生成Wallet组件时会创建一个ORO(OrderReferenceObject),在GetDetails.php中调用SetOrderReferenceDetails API来设定订单详情</p>
                <p>ORO此时为Draft状态</p>
                <p>点击确认支付跳转到订单确认及授权页面ConfirmPaymentAndAuthorize.php</p>
            </pre>

            <p>生成上面Wallet组件时调用GetOrderReferenceDetails API得到的响应如下：</p>
            <pre id="get_details_response">
                <div class="text-center"><img src="images/ajax-loader.gif" /></div>
            </pre>

            <p>在支付方式中有八张信用卡，后四张带星号的模拟会出错拒绝支付的情况如下：</p>
            <pre>
                <ul>
                    <li>5656 - 模拟 TransactionTimedOut reason code：交易超时</li>
                    <p>Authorize call没有在设定的TransactionTimeout参数时间内被处理，ORO(OrderReferenceObject)仍处于Open state</p>
                    <li>4545 - 模拟 PaymentMethodNotAllowed constraint：不被许可的支付方式</li>
                    <p>支付方法不被允许，ORO仍处于Open State</p>
                    <li>2323 - 模拟 AmazonRejected reason code：被拒绝</li>
                    <p>由Amazon做出的终止决定，ORO被设为Closed state</p>
                    <li>3434 - 模拟 InvalidPaymentMethod reason code：无效的支付方式</li>
                    <p>所选的支付方式有问题，ORO被设为Suspended state</p>
                </ul>
            </pre>
        </div>

        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
        <script type='text/javascript'>
            // 回到游戏
            document.getElementById('cancelButton').onclick = function() {
                amazon.Login.logout();
                document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                window.location = '<?php echo $game_config['gameurl']; ?>'
            }

            window.onAmazonLoginReady = function () {
                try {
                    amazon.Login.setClientId('<?php print $amazonpay_config['client_id']; ?>');
                    amazon.Login.setUseCookie(true);
                } catch (err) {
                    alert(err);
                }
            };

            window.onAmazonPaymentsReady = function () {

                new OffAmazonPayments.Widgets.Wallet({
                    sellerId: "<?php echo $amazonpay_config['merchant_id']; ?>",
                    onOrderReferenceCreate: function (orderReference) {

                                            /* 调用 SetOrderReferenceDetails 和 GetOrderReferenceDetails.
                                             * 此操作会设定订单金额并返回order reference details
                                             */

                                            var access_token = '<?php print $_GET["access_token"];?>';

                                            $.post("Apicalls/GetDetails.php", {
                                                orderReferenceId: orderReference.getAmazonOrderReferenceId(), // 获取Order Reference ID
                                                accessToken: access_token
                                            }).done(function (data) {
                                                try {
                                                    JSON.parse(data);
                                                } catch (err) {
                                                }
                                                $("#get_details_response").html(data);
                                            });
                                        },
                    onPaymentSelect: function (orderReference) {
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function (error) {
                        // your error handling code
                        alert("Wallet Widget error: " + error.getErrorCode() + ' - ' + error.getErrorMessage());
                    }
                }).bind("walletWidgetDiv");


                $(document).ready(function() {
                    $('.start-over').on('click', function() {
                        amazon.Login.logout();
                        document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                        window.location = '../index.php';
                    });
                    $('#place-order').on('click', function() {
                        $(this).hide();
                        $('#ajax-loader').show();
                    });
                });



            };

        </script>
        <script async="async" type='text/javascript' src="<?php echo getWidgetsJsURL($amazonpay_config) . "?". $amazonpay_config['merchant_id']; ?>"></script>
    </body>
</html>
