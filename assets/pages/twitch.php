<h2>Twitch.tv</h2>
<?
    include "twitch.inc.php";
    define("SCOPE", "channel_subscriptions+channel_check_subscription+user_read");
    
    if (isset($_POST["username"]))
    {
        $json = @json_decode(file_get_contents("https://api.mojang.com/users/profiles/minecraft/" . $_POST["username"]), true);
        if ($json == NULL) echo "<p>An error occured. Please try again.</p>";
        else 
        {
            $uuid = $json["id"];
            $json = @json_decode(file_get_contents("https://api.twitch.tv/kraken/user?oauth_token=" . $_POST["token"]), true);
            $twitchName = $json["name"];
            if ($json == NULL) echo "<p>An error occured. Please try again.</p>";
            var_dump($json);
            $json = is_file("twitch.json") ? json_decode(file_get_contents("twitch.json"), true) : array();
            $json[$uuid] = $twitchName;
            file_put_contents("twitch.json", json_encode($json));
            
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
        Be warned: If you get a new token, the old one expires!
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