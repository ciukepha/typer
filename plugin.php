<?php
/*
Plugin Name: typer_wechat
Description: This plugin will detect if the request is from WeChat's built-in browser and ask the visitor to open in a regular browser. If not in WeChat, it shows a dynamic countdown before redirecting.
Version: 0.4
Author: @calzh
*/

define('TYPER_PAGE_CHAR', '_');
define('WECHAT_UA_STRING', 'MicroMessenger');

// Hook into YOURLS's loader_failed action
yourls_add_action('loader_failed', 'typer_detect_request');

function typer_detect_request($args) {
    $request = $args[0];
    $pattern = yourls_make_regexp_pattern(yourls_get_shorturl_charset());
    if (preg_match("@^([$pattern]+)" . TYPER_PAGE_CHAR . "$@", $request, $matches)) {
        $keyword = isset($matches[1]) ? $matches[1] : '';
        $keyword = yourls_sanitize_keyword($keyword);
        if (yourls_is_shorturl($keyword)) {
            // Detect if the user is using WeChat's built-in browser
            if (strpos($_SERVER['HTTP_USER_AGENT'], WECHAT_UA_STRING) !== false) {
                typer_show_wechat_page($keyword);
            } else {
                typer_show_countdown_page($keyword);
            }
            die();
        }
    }
}

function typer_show_wechat_page($keyword) {
    // CSS and HTML for WeChat warning, fully based on your provided document
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>请在浏览器中打开</title>
    <style type="text/css">
        body { font:normal 14px/1.5 Arial, Microsoft Yahei; color:#333; background-color: #f4f4f4; }
        .wxtip { background: rgba(0,0,0,0.8); text-align: center; position: fixed; left:0; top: 0; width: 100%; height: 100%; z-index: 998; display: block; }
        .wxtip-icon { width: 104px; height: 134px; background: url('weixin-tip.png') no-repeat; display: block; position: fixed; right: 60px; top: 20px;background-size: contain; }
        .wxtip-txt { padding-top: 107px; color: #fff; font-size: 60px; line-height: 1.5; }
    </style>
</head>
<body>
<div class="wxtip" id="JweixinTip">
    <span class="wxtip-icon"></span>
    <p class="wxtip-txt">点击右上角<br/>选择在默认浏览器中打开</p>
</div>
</body>
</html>
HTML;
}

function typer_show_countdown_page($keyword) {
    $redirectUrl = YOURLS_SITE . '/' . $keyword;
    // HTML and JavaScript for dynamic countdown
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>跳转中……</title>
    <style type="text/css">
        body { font:normal 14px/1.5 Arial, Microsoft Yahei; color:#333; text-align: center; padding-top: 20px; background-color: #f4f4f4; }
        .countdown-text { font-size: 48px; }
    </style>
</head>
<body>
<div class="countdown">
    <p class="countdown-text">将在 <span id="countdown">3</span> 秒后自动跳转……</p>
</div>
<script type="text/javascript">
    var seconds = 3;
    var countdown = document.getElementById('countdown');
    var interval = setInterval(function() {
        seconds--;
        countdown.innerHTML = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = "$redirectUrl";
        }
    }, 1000);
</script>
</body>
</html>
HTML;
}
?>
