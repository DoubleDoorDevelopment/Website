<?php header("Location: /?p=linking"); die("Moved to ?p=linking"); ?>
<h2>Twitch.tv</h2>
<?
    include "twitch.inc.php";
    include "mysql.php";
    define("SCOPE", "channel_subscriptions+channel_check_subscription+user_read+user_subscriptions");
    
    if (isset($_POST["username"]))
    {
        $username = strip_tags($_POST["username"]);
        $token = strip_tags($_POST["token"]);
        if (!preg_match("/^\\w+$/", $username) || !preg_match("/\w+/i", $token)) die("Fraud attempt.");
        $json = @json_decode(file_get_contents("https://api.mojang.com/users/profiles/minecraft/$username"), true);
        if ($json == NULL) echo "<p>An error occured. Please try again. Error 1</p><p>Did you spell your IGN correctly? This is what we got: $username</p>";
        else 
        {
            $uuid = $json["id"];
            $json = @json_decode(file_get_contents("https://api.twitch.tv/kraken/user?oauth_token=$token"), true);
            $twitchName = $json["name"];
            if ($json == NULL) echo "<p>An error occured. Please try again. Error 2</p>";

            $db = makeDBConnection();
            $stmt = $db->prepare("INSERT INTO minecraft SET Twitch = ?, UUID = ? ON DUPLICATE KEY UPDATE Twitch = ?");
            $stmt->execute(array($twitchName, $uuid, $twitchName));
            
            echo "<p>All done.</p>";
        }
    }
    else 
    {
?>
<p id="pre">
    If you want to join a server that uses ForgeTwitchSubWhitelist, you will have to link your Minecraft UUID and your Twitch username.<br>
    If you are a streamer with a sub button, you will be asked for a token for the Pay2Spawn and/or ForgeTwitchSubWhitelist config file.<br>
    Click the button below to get started.<br>
    <br>
	<b>Be warned: If you get a new token, the old one expires!</b>
	<br>
	<br>
    <button class="btn btn-primary btn-lg" onclick="location.href='https://api.twitch.tv/kraken/oauth2/authorize?response_type=token&client_id=<? echo TWITCH_CLIENTID ?>&redirect_uri=http://www.doubledoordev.net/?p=twitch&scope=<? echo SCOPE ?>'">Authenticate with Twitch.tv!</button>
</p>
<div id="post">
    <form class="form-inline" method="post" action="?p=twitch">
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">Minecraft Username:</div>
                <input type="text" class="form-control" name="username" placeholder="steve">
            </div>
        </div>
        <input type="text" id="token1" name="token" hidden="hidden">
        <button type="submit" class="btn btn-primary">Link!</button>
    </form>
    <p>
        If you need the token for a config file, here it is:<br>
        <b><span id="token2"></span></b><br>
    </p>
</div>
<script type="text/javascript">
  function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
  }
  
  var hash = window.location.hash;
  if (hash.indexOf("access_token") > -1)
  {
    document.getElementById("pre").setAttribute("hidden", "hidden")
    var token = hash.replace("#access_token=", "").replace("&scope=<? echo SCOPE ?>", "");
    document.getElementById("token1").setAttribute("value", token);
    document.getElementById("token2").innerHTML = token;
  }
  else
  {
    document.getElementById("post").setAttribute("hidden", "hidden");
  }
</script>
<?
}
?>