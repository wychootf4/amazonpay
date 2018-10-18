<?php
    session_start();
    require_once "../config.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width-device-width, initial-scale=1.0, maximum-scale=1.0">
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
                        <a class="navbar-brand start-over" href="#">Amazon Pay PHP Demo: AutoPay</a>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav navbar-right">
                            <li><a class="start-over" href="#">logout并重新开始</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="jumbotron jumbotroncolor" style="padding-top:25px;" id="api-content">
                <div id="section-content">

                    <h2>第一次授权时选择支付方式</h2>
                    <p>
                        将token传入GetBillingAgreementDetails API来获得由widget生成的BillingAgreementID
                    </p>
                    <p>
                        出错的话一般是session过期了，logout重新开始
                    </p>

                    <div class="text-center" style="margin-top:40px;">
                        <div id="walletWidgetDiv" style="width:320px; height:250px; display:inline-block;"></div>
                        <div id="consentWidgetDiv" style="width:320px; height:250px; display:inline-block;"></div>
                        <div style="clear:both;"></div>
                        <form class="form-horizontal" style="margin-top:40px;" role="form" method="post" action="ConfirmPaymentAndAuthorize.php">
                            <button id="confirm-subscription" class="btn btn-lg btn-success" disabled>使用AutoPay付款</button>
                            <div id="ajax-loader" style="display:none;"><img src="images/ajax-loader.gif" /></div>
                        </form>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-danger" id="cancelButton" style="margin-top:20px">取消支付</button>
                    </div>
                    <p><br>
                        使用AutoPay付款 按钮在如下情况是不可点击的：
                        <ul>
                            <li>没有勾选同意未来使用AutoPay对话框</li>
                            <li>使用尾号"4545"的测试信用卡模拟支付方式不被允许</li>
                        </ul>
                    </p>

                </div>

            </div>
                <div class="jumbotron jumbotroncodecolor" style="padding-top:25px;" id="api-calls">
                <p>下面是调用getBillingAgreementDetailsAPI获得的响应：</p>
                <pre id="get_details_response"><div class="text-center"><img src="images/ajax-loader.gif" /></div></pre>
            </div>

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
                amazon.Login.setClientId('<?php print $amazonpay_config['client_id']; ?>');
                amazon.Login.setUseCookie(true);
            };

            window.onAmazonPaymentsReady = function () {
                var billingAgreementId;
                var access_token;
                var buyerBillingAgreementConsentStatus;
                new OffAmazonPayments.Widgets.Wallet({
                    sellerId: "<?php echo $amazonpay_config['merchant_id']; ?>",
                    /*
                     * 如果agreementType为BillingAgreement则会创建BillingAgreement对象
                     * 如果要设置为只允许OneTimePayment，则设为orderReference
                     */
                    agreementType: 'BillingAgreement',
                    // onReady callback会在渲染widget时创建BillingAgreement对象
                    onReady: function(billingAgreement) {
                        // 这个地方看看可不可以做判断，如果前端有值传进来则证明可以直接调之前已有的billingagreementid
                        billingAgreementId = billingAgreement.getAmazonBillingAgreementId();
                        //billingAgreementId = $game_config['billingAgreementId'] != "" ? "<?php echo $game_config['billingAgreementId']; ?>" : billingAgreement.getAmazonBillingAgreementId();
                        access_token = "<?php echo $_GET['access_token'];?>";
                        get_details(billingAgreementId, access_token);

                        // render the consent and widgets once the
                        // address book has loaded
                        new OffAmazonPayments.Widgets.Consent({
                            sellerId: "<?php echo $amazonpay_config['merchant_id']; ?>",
                            // amazonBillingAgreementId obtained from the Amazon Wallet widget.
                            amazonBillingAgreementId: billingAgreementId,
                            design: {
                                designMode: 'responsive'
                            },
                            onReady: function(billingAgreementConsentStatus) {
                                buyerBillingAgreementConsentStatus =
                                      billingAgreementConsentStatus.getConsentStatus(); // temporarily not using
                            },
                            onConsent: function(billingAgreementConsentStatus) {
                                buyerBillingAgreementConsentStatus =
                                      billingAgreementConsentStatus.getConsentStatus(); // temporarily not using
                                // getConsentStatus returns true or false
                                // true – checkbox is selected – buyer has consented
                                // false – checkbox is unselected – buyer has not consented

                                // Replace this code with the action that you want to perform
                                // after the consent checkbox is selected/unselected.
                                // 看看能否在这边实现
                                get_details(billingAgreementId, access_token);
                            },
                            onError: function (error) {
                                // your error handling code
                                alert("Consent Widget error: " + error.getErrorCode() + ' - ' + error.getErrorMessage());
                            }
                        }).bind("consentWidgetDiv");
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onPaymentSelect: function(billingAgreement) {
                        // Do something after payment method is selected
                        get_details(billingAgreementId, access_token);
                    },
                    onError: function (error) {
                        // your error handling code
                        alert("Wallet Widget error: " + error.getErrorCode() + ' - ' + error.getErrorMessage());
                    }
                }).bind("walletWidgetDiv");

                function get_details(billingAgreementId, access_token) {
                    $.post("Apicalls/GetDetails.php", {
                        amazon_billing_agreement_id: billingAgreementId,
                        accessToken: access_token
                    }).done(function (data) {
                        // 如果有限制条件则不能点击下单按钮，显示调用getBillingAgreementDetails所得到的响应
                        if (data) {
                            try {
                                var details = jQuery.parseJSON(data).GetBillingAgreementDetailsResult.BillingAgreementDetails;
                                var message = data;
                                if (details.Constraints) {
                                    $('#confirm-subscription').prop('disabled', true);

                                    var constraints = [];
                                    if (details.Constraints.Constraint instanceof Array) {
                                       constraints = details.Constraints.Constraint;
                                    } else {
                                        constraints[0] = details.Constraints.Constraint;
                                    }

                                    message = "<font color='red'><strong>Failed with Constraint(s):\n";
                                    constraints.forEach(function(entry) {
                                        message += entry.ConstraintID + ": " + entry.Description + "\n";
                                    });
                                    message += "</strong></font>\n" + data;
                                } else {
                                    // if there are no constraints, enable the "Confirm Subscription" button
                                    $('#confirm-subscription').prop('disabled', false);
                                }
                                $("#get_details_response").html(message);

                            } catch (err) {
                                $("#get_details_response").html(data);
                                alert(err);
                            }
                        }

                    });
                }

            };

            $(document).ready(function() {
                $('.start-over').on('click', function() {
                    amazon.Login.logout();
                    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                    window.location = '../index.php';
                });
            });

        </script>
        <script async="async" type='text/javascript' src="<?php echo getWidgetsJsURL($amazonpay_config); ?>"></script>

    </body>
</html>
