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

            <div class="text-center">
                <button type="button" class="btn btn-danger" id="backButton">返回游戏</button>
            </div>
            <div class="jumbotron jumbotroncodecolor" style="padding-top:25px; margin-top:25px;" id="api-calls">

                <h2>IPN发货通知</h2>
                <p>在接收到IPN时会接收到签名并创建签名校验字符串看是否匹配，若不匹配会报错</p>
                <p>接收到发货通知后检查response中的AuthorizationStatus参数，根据AuthorizationStatus参数处理发货业务逻辑</p>
                <p>如果AuthorizationStatus为Open，则证明授权成功，可以发货</p>
                <div class="text-center">
                    <button type="button" class="btn btn-danger" id="ipnButton">查看IPN通知</button>
                </div>
                <pre id="IPN" style="margin-top:20px;"><div class="text-center"></div></pre>
            </div>

        </div>

        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>

        <script type='text/javascript'>
            $(document).ready(function() {
                $("#ipnButton").click(function(){
                    $("IPN").html("test");
                });
            });

            // 回到游戏
            document.getElementById('backButton').onclick = function() {
                //amazon.Login.logout();
                //document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                window.location = '<?php echo $game_config['gameurl']; ?>'
            }


            window.onAmazonPaymentsReady = function () {

                $(document).ready(function() {
                    $('.start-over').on('click', function() {
                        amazon.Login.logout();
                        document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                        window.location = 'index.php';
                    });
                });





            };



        </script>

        <script async="async" type='text/javascript' src="<?php echo getWidgetsJsURL($amazonpay_config); ?>"></script>

    </body>
</html>