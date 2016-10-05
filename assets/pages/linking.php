<h2>Account linking service</h2>
<p>If you linked before July 14th 2016, you may have to re-link your accounts. If you do, remember that API keys are also invalidated!</p>
<?php
include "twitch.inc.php";
include "gamewisp.inc.php";
include "beam.inc.php";

$token = null;

$SERVICES = [
    "Twitch" => [
        "name-column" => "Twitch",
        "verified-column"=> "TwitchVerified",
        "redirect" => function() {
            $query = http_build_query([
                'response_type' => 'token',
                'client_id' => TWITCH_CLIENTID,
                'redirect_uri' => 'http://doubledoordev.net/?p=linking',
                'state' => base64_encode(json_encode(['token' => $_GET['token'], 'service' => $_GET['service'], 'uuid' => $_GET['uuid']]))
            ]);
            header("Location: https://api.twitch.tv/kraken/oauth2/authorize?scope=channel_subscriptions+channel_check_subscription+user_read+user_subscriptions&$query");
        },
        "catch" => function($nameCol, $verifiedCol, $state, $db) {
            $token = $_GET['access_token'];
            $json = @json_decode(file_get_contents("https://api.twitch.tv/kraken/user?oauth_token=$token"), true);
            if ($json == NULL) die("The Twitch API seems to be down.");
            $name = $json["name"];

            if ($name == null) die("Error");

            $stmt = $db->prepare("UPDATE minecraft SET $nameCol=?, $verifiedCol=1, TwitchToken=? WHERE UUID=? AND $nameCol=?");
            $stmt->execute([$name, $token, $state['uuid'], $state['token']]);
            if ($stmt->rowCount() != 1) die ("Token expired. Please try again.");
        }
    ],
    "GameWisp" => [
        "name-column"=> "GameWisp",
        "verified-column"=> "GameWispVerified",
        "redirect" => function() {
            $query = http_build_query([
                'response_type' => 'code',
                'client_id' => GAMEWISP_CLIENTID,
                'redirect_uri' => 'http://doubledoordev.net/?p=linking',
                'scope' => 'user_read,read_only',
                'state' => base64_encode(json_encode(['token' => $_GET['token'], 'service' => $_GET['service'], 'uuid' => $_GET['uuid']]))
            ]);
            header("Location: https://api.gamewisp.com/pub/v1/oauth/authorize?$query");
        },
        "catch" => function($nameCol, $verifiedCol, $state, $db) {
            $url = 'https://api.gamewisp.com/pub/v1/oauth/token';
            $data = [
                'grant_type' => 'authorization_code',
                'client_id' => GAMEWISP_CLIENTID,
                'client_secret' => GAMEWISP_CLIENTSECRET,
                'redirect_uri' => 'http://doubledoordev.net/?p=linking',
                'code'          => $_GET['code']
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ]
            ];
            $context  = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            if ($result === FALSE) die("Error getting token from GameWisp API.");

            $result = json_decode($result, true);
            // To compensate for the format change.
            if (!isset($result['access_token'])) $result = $result['data'];
            $token = $result['access_token'];
            $expire = $result['expires_in'];

            $json = @json_decode(file_get_contents("https://api.gamewisp.com/pub/v1/user/information?include=profile&access_token=$token"), true);
            if ($json == NULL) die("The GameWisp API seems to be down.");
            $name = $json['data']['username'];

            if ($name == null) die("Error");

            $stmt = $db->prepare("UPDATE minecraft SET $nameCol=?, $verifiedCol=1, GameWispAccessToken=?, GameWispRefreshToken=?, GameWispExpire=FROM_UNIXTIME(UNIX_TIMESTAMP() + $expire) WHERE UUID=? AND $nameCol=?");
            $stmt->execute([$name, $token, $result['refresh_token'], $state['uuid'], $state['token']]);
            if ($stmt->rowCount() != 1) die ("Token expired. Please try again.");
        }
    ],
    "Beam" => [
        "name-column"=> "Beam",
        "verified-column"=> "BeamVerified",
        "redirect" => function() {
            $query = http_build_query([
                'response_type' => 'code',
                'client_id' => BEAM_CLIENTID,
                'redirect_uri' => 'http://doubledoordev.net/linking',
                'scope' => 'user:details:self',
                'state' => base64_encode(json_encode(['token' => $_GET['token'], 'service' => $_GET['service'], 'uuid' => $_GET['uuid']]))
            ]);
            header("Location: https://beam.pro/oauth/authorize?$query");
        },
        "catch" => function($nameCol, $verifiedCol, $state, $db) {
            $url = 'https://beam.pro/api/v1/oauth/token';
            $data = [
                'grant_type' => 'authorization_code',
                'client_id' => BEAM_CLIENTID,
                'client_secret' => BEAM_CLIENTSECRET,
                'redirect_uri' => 'http://doubledoordev.net/linking',
                'code' => $_GET['code']
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === FALSE) die("Error getting token from Beam API.");

            $result = json_decode($result, true);
            $token = $result['access_token'];
            $expire = $result['expires_in'];

            $opts = [
                'http' => [
                    'method'  => 'GET',
                    'header' => [
                        "Authorization: Bearer $token"
                    ],
                ]
            ];
            $context = stream_context_create($opts);
            $json = @json_decode(file_get_contents("https://beam.pro/api/v1/users/current", false, $context), true);
            if ($json == NULL) die("The GameWisp API seems to be down.");
            $name = $json['username'];

            if ($name == null) die("Error");

            $stmt = $db->prepare("UPDATE minecraft SET $nameCol=?, $verifiedCol=1, BeamAccessToken=?, BeamRefreshToken=?, BeamId=?, BeamChannel=?, BeamExpire=FROM_UNIXTIME(UNIX_TIMESTAMP() + $expire) WHERE UUID=? AND $nameCol=?");
            $stmt->execute([$name, $token, $result['refresh_token'], $json['id'], $json['channel']['id'], $state['uuid'], $state['token']]);
            if ($stmt->rowCount() != 1) die ("Token expired. Please try again.");
        }
    ]
];

if (isset($_GET['service'], $_GET['token'], $_GET['uuid']))
{
    if (!isset($SERVICES[$_GET['service']])) die ("Unknown service.");
    if (strlen($_GET['token']) != 32 || strlen($_GET['uuid']) != 32) die ("URL invalid");

    $nameCol = $SERVICES[$_GET['service']]['name-column'];
    $verifiedCol = $SERVICES[$_GET['service']]['verified-column'];

    $db = makeDBConnection();
    $stmt = $db->prepare("SELECT $nameCol FROM minecraft WHERE UUID=? AND $nameCol=?");
    $stmt->execute([$_GET['uuid'], $_GET['token']]);
    $tmp = $stmt->fetch(PDO::FETCH_NUM);
    if ($tmp == null) die ("Something went wrong. Your token might have been reset. Please try again.");

    $SERVICES[$_GET['service']]['redirect']();

    exit;
}
elseif (isset($_GET['state']))
{
    $state = json_decode(base64_decode($_GET['state']), true);
    if (!isset($state['token'], $state['service'], $state['uuid'])) die("Invalid state.");
    if (isset($_GET['error'])) die ("Something went wrong. Contact us with the following information: " . $_GET['error']);

    $db = makeDBConnection();

    $nameCol = $SERVICES[$state['service']]['name-column'];
    $verifiedCol = $SERVICES[$state['service']]['verified-column'];

    $stmt = $db->prepare("SELECT BIN($verifiedCol) FROM minecraft WHERE UUID=?");
    $stmt->execute([$state['uuid']]);
    $tmp = $stmt->fetch(PDO::FETCH_NUM);
    if ($tmp[0] == 1) die("This service is already verified.");
    if ($stmt->rowCount() != 1) die ("Token expired. Please try again.");

    $SERVICES[$state['service']]['catch']($nameCol, $verifiedCol, $state, $db);
?>
<div>
    <h3>Linked up <? echo $state['service'] ?></h3>
    <p>You should be getting a message in your Minecraft client within 10 seconds to confirm.</p>
    <p>You can now close this page, and if you have linked up the relevant services, you can also disconnect from the authentication server.</p>
    <p>There is no waiting period on joining any sub server. Go ahead and have fun!</p>
</div>
<?php
}
else
{
?>
<div id="main">
    <h3><b>For players</b></h3>
    <p style="font-size: 1.1em">If you want to log onto a server that uses automatic subscriber whitelisting you need to link your Twitch, GameWisp, Beam... and Minecraft accounts!</p>
    <p style="font-size: 1.1em">To link your accounts, you have to make a Vanilla <b>1.10 Client</b> and connect to this server address: <code>doubledoordev.net</code> and follow the instructions on screen.</p>
    <p>You only ever have to do this once. The service is independent of streamer, and it should remember your account link forever.</p>
    <h3>For (future) server owners</h3>
    <p>The Patreon API is lacking essentials features at the moment.</p>
    <p>
        Follow the instructions for players.<br/>
        If you also want to use this service for your servers, look on our projects page!<br/>
        To get your token for the config file, type 'apitoken' in chat.
    </p>
    <h3>For developers</h3>
    <p>
        You too can have access to the linking database.<br/>
        Look at the REST interface <a href="https://github.com/DoubleDoorDevelopment/Website">here</a>.<br/>
        Please contact us when using it in public projects.
    </p>
    <p class="small">
        If you want your information removed, log into the server and type 'removeme' in chat.
    </p>
</div>
<div id="loading" style="display: none;">
    <p>Please wait a second while you are redirected.</p>
</div>
<script type="text/javascript">
    var hash = window.location.hash;
    if (hash.indexOf("access_token") > -1)
    {
        document.getElementById("main").style.display = 'none';
        document.getElementById("loading").style.display = 'block';
        location.href=location.href.replace('#', '&');
    }
</script>
<?php
}
?>
