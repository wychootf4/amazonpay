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
                <div class="text-center">
                    <button type="button" class="btn btn-danger" id="backButton">返回游戏</button>
                </div>
                <h2>Confirm</h2>
                <p>调用confirmOrderReferenceAPI, 告诉Amazon玩家已经下单，将ORO设为Open状态并向玩家发送支付确认邮件</p>
                <p>若调用Confirm API后五分钟内交易完成/失败/取消，则不会发送支付确认邮件</p>
                <h3>Confirm 响应</h3>
                <p><em>Confirm</em> API call不会返回特殊的值. 如果不成功则会看到错误信息.</p>
                <pre id="confirm"><code class="json"></code></pre>

                </div>
            </div>

            <div class="jumbotron jumbotroncodecolor" style="padding-top:25px;" id="api-calls">
                <h2>Authorize</h2>
                <p>这里采用同步授权</p>
                <p>成功授权会创建一个状态为Open的Authorize对象</p>
                <p>根据收到的Authorize响应中AuthorizationStatus的State和ReasonCode进行判断；若State为Closed且ReasonCode为MaxCapturesProcessed，则证明为有效的支付，可以进行发货</p>
                <h3>Authorize 响应</h3>
                <div id="result"></div>
                <pre id="authorize"><div class="text-center"></div></pre>
                <p>
                    <em>Authorize</em> API 会授权order reference. <em>Capture</em> API call 将<strong>capture_now</strong>
                    参数设为<strong>true</strong> 那么在同一个调用中就可以实现capture款项，而不用再单独调用Capture API
                </p>
            </div>

            <div class="jumbotron jumbotroncodecolor" style="padding-top:25px;" id="api-calls">
                <h2>Close Order Reference Object</h2>
                <p>如果授权及请求款项成功，将Order Reference Object状态设为Close</p>
                <div id="closeResult"></div>
                <pre id="closeORO"><div class="text-center"></div></pre>
            </div>

        </div>

        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>

        <script type='text/javascript'>


            window.onAmazonPaymentsReady = function () {

                $(document).ready(function() {
                    $('.start-over').on('click', function() {
                        amazon.Login.logout();
                        document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                        window.location = '../index.php';
                    });
                });

                // 回到游戏
                document.getElementById('backButton').onclick = function() {
                    amazon.Login.logout();
                    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                    window.location = '<?php echo $game_config['gameurl']; ?>'
                }

                // 去到IPN通知查看页面
                /*
                document.getElementById('ipnButton').onclick = function() {
                    window.location = 'CheckIpn.php';
                }
                */
            };

            var authorizeResponse;
            var confirmResponse;
            //var closeResponse;
            $.post("Apicalls/ConfirmAndAuthorize.php", {}).done(function(data) {
                try {
                    var obj = jQuery.parseJSON(data);
                    $.each(obj, function(key, value) {
                        if (key == 'confirm') {
                            confirmResponse = value;
                            var str = JSON.stringify(value, null, 2);
                            $("#confirm").html(str);
                        } else if (key == 'authorize') {
                            authorizeResponse = value;
                            var str = JSON.stringify(value, null, 2);
                            $("#authorize").html(str);
                        } else if (key == 'closeORO') {
                            closeResponse = value;
                            var str = JSON.stringify(value, null, 2);
                            $("#closeORO").html(str);
                        }
                    });

                    // 在后端进行处理

                    if (confirmResponse) {
                        if (confirmResponse.Error) {
                            $("#result").html("<font color='red'><strong>Confirm API call failed (see reason above)</strong></font>");
                        }
                    }

                    if (authorizeResponse) {
                        if (authorizeResponse.AuthorizeResult.AuthorizationDetails.AuthorizationStatus.State === "Declined") {
                            $("#result").html("<font color='red'><strong>The authorization was Declined with Reason Code "
                                + authorizeResponse.AuthorizeResult.AuthorizationDetails.AuthorizationStatus.ReasonCode + "</strong></font>");
                        }
                    }

                    //if (closeResponse) {
                      //  if (closeResponse.Error) {
                        //    $("#closeResult").html(<font color='red'><strong>Capture API call failed (see reason above)</strong></font>");
                        //}
                    //}

                } catch (err) {
                    $("#confirm").html(data);
                    console.log(data);
                    console.log(err);
                }

            });
        </script>
    
        <script async="async" type='text/javascript' src="<?php echo getWidgetsJsURL($amazonpay_config); ?>"></script>

    </body>
</html>
