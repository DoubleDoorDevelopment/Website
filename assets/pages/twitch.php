<?
    include "twitch.inc.php";
    define("SCOPE", "channel_subscriptions+channel_check_subscriptions");
?>
<h2>Twitch token generator</h2>
<p>
    You might need this token for Pay2Spawn and ForgeTwitchSubWhitelist.<br>
    If you get authentication errors, try requesting a new one.
</p>
<p id="twitch"><a class="btn btn-default" href="https://api.twitch.tv/kraken/oauth2/authorize?response_type=token&client_id=<? echo TWITCH_CLIENTID ?>&redirect_uri=http://www.doubledoordev.net/?p=twitch&scope=<? echo SCOPE ?>">Push the button</a></p>
<script type="text/javascript">
  function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
  }
  
  var hash = window.location.hash;
  if (hash.indexOf("access_token") > -1)
  {
    document.getElementById("twitch").innerHTML = "Here is your token: <b>" + hash.replace("#access_token=", "").replace("&scope=<? echo SCOPE ?>", "") + "</b>"
  }
  
</script>
