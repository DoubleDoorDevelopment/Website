<?php

// Error reporting for debug
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('track_errors', 1);

header('Content-Type:text/plain');

if (!isset($_GET["token"]))
{
    http_response_code(400);
    echo "Endpoint to check if the user is allowed to join a server.\n";
    echo "Usage (GET parameters):\n";
    echo "\tNormal operation:\n";
    echo "\t\ttoken     => apiToken (Required)\n";
    echo "\t\tuuid      => MC UUID (Required)\n";
    echo "\t\ttwitch    => 'true OR false'\n";
    echo "\t\tbeam      => 'true OR false'\n";
    echo "\t\tgamewisp  => '-1 OR tier level'\n";
    echo "\tToken check:\n";
    echo "\t\ttoken     => apiToken\n";
    echo "Result:\n";
    echo "\tHTTP 200 Boolean. True if the user should be allowed on.\n";
    echo "\tHTTP 200 Empty on token check successful.\n";
    echo "\tHTTP 4xx If the request was not valid.\n";
    echo "\tHTTP 403 If the token check failed.\n";
    echo "\tHTTP 5xx If there's fire coming out of the server.\n";
    die("\nSee https://github.com/DoubleDoorDevelopment/Website for more information.");
}

// This amount of fraud / error detection is probably more then strictly required, but better safe then sorry.
function checkUUID($uuid)
{
    switch (strlen($uuid))
    {
        default:
            http_response_code(400);
            die("Your UUID needs to be in 32 char (no dashes) or 36 char (with dashes) alphanumeric format.");
        //c93ca410-8003-40ef-81d7-ac88719e2038
        case 36:
            $uuid = str_replace('-', '', $uuid);
            break;
        //c93ca410800340ef81d7ac88719e2038
        case 32:
            break;
    }

    if (!ctype_alnum($uuid))
    {
        http_response_code(400);
        die("Your UUID needs to be in 32 char (no dashes) or 36 char (with dashes) alphanumeric format.");
    }
    return $uuid;
}

include "gamewisp.inc.php";
include "beam.inc.php";

function updateBeamToken($db, $refreshToken, $uuid, $beamId)
{
    $params = ['http' => [
        'method' => 'POST',
        'content' => [
            'grant_type' => 'from_refresh',
            'client_id' => BEAM_CLIENTID,
            'client_secret' => BEAM_CLIENTSECRET,
            'refresh_token' => $refreshToken,
        ]
    ]];
    $ctx = stream_context_create($params);
    $fp = @fopen('https://beam.pro/api/v1/oauth/token', 'rb', false, $ctx);
    if (!$fp) throw new Exception("BEAM refresh open $php_errormsg");
    $response = @stream_get_contents($fp);
    if ($response === false) throw new Exception("BEAM refresh contents $php_errormsg");
    $response = json_decode($response, true);

    $refreshToken = $response['refresh_token'];
    $accessToken = $response['access_token'];
    $expire = $response['expires_in'];

    $stmt = $db->prepare("UPDATE minecraft SET BeamRefreshToken=?, BeamAccessToken=?, BeamExpire=FROM_UNIXTIME(UNIX_TIMESTAMP() + $expire) WHERE UUID=?");
    $stmt->execute([$refreshToken, $accessToken, $uuid]);
    return $accessToken;
}

function updateGameWispToken($db, $refreshToken, $uuid)
{
    $url = 'https://gamewisp.com/api/v1/oauth/token';
    $data = [
        'grant_type' => 'refresh_token',
        'client_id' => GAMEWISP_CLIENTID,
        'client_secret' => GAMEWISP_CLIENTSECRET,
        'redirect_uri' => 'http://doubledoordev.net/?p=linking',
        'refresh_token' => $refreshToken
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    if ($result === FALSE) throw new Exception("GAMEWISP refresh contents $php_errormsg");
    $result = json_decode($result, true)['data'];

    $refreshToken = $result['refresh_token'];
    $accessToken = $result['access_token'];
    $expire = $result['expires_in'];

    $name = json_decode(file_get_contents("https://api.gamewisp.com/pub/v1/user/information?include=profile&access_token=$accessToken"), true)['data']['username'];

    $stmt = $db->prepare("UPDATE minecraft SET GameWisp=?, GameWispRefreshToken=?, GameWispAccessToken=?, GameWispExpire=FROM_UNIXTIME(UNIX_TIMESTAMP() + $expire) WHERE UUID=?");
    $stmt->execute([$name, $refreshToken, $accessToken, $uuid]);
    return $accessToken;
}

try
{
    include "mysql.inc.php";
    $db = makeDBConnection();

    $stmt = $db->prepare('SELECT UUID, Twitch, TwitchToken, GameWispAccessToken, BeamId, BeamChannel, BeamAccessToken FROM minecraft WHERE APIToken=? LIMIT 1');
    $stmt->execute([$_GET["token"]]);
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($tmp === false)
    {
        http_response_code(403);
        die("Token invalid.");
    }
    $channelOwner = $tmp['UUID'];
    $channel = $tmp['Twitch'];
    $twitchToken = $tmp['TwitchToken'];
    $gamewispToken = $tmp['GameWispAccessToken'];
    $beamId = $tmp['BeamId'];
    $beamChannel = $tmp['BeamChannel'];
    $beamToken = $tmp['BeamAccessToken'];

    if (isset($_GET['uuid']))
    {
        $twitch = isset($_GET['twitch']);
        $gamewisp = isset($_GET['gamewisp']) ? filter_var($_GET['gamewisp'], FILTER_VALIDATE_INT) : null;
        $beam = isset($_GET['beam']);
        $uuid = checkUUID($_GET['uuid']);

//        if ($channelOwner === $uuid)
//        {
//            header('X-Service:Owner');
//            die("true");
//        }

        $stmt = $db->prepare('SELECT
  Twitch, BIN(TwitchVerified) AS TwitchVerified,
  GameWisp, BIN(GameWispVerified) AS GameWispVerified, GameWispExpire < NOW() AS GameWispExpired, GameWispRefreshToken,
  BeamId, BIN(BeamVerified) AS BeamVerified, BeamExpire < NOW() AS BeamExpired, BeamRefreshToken
FROM minecraft WHERE UUID=? LIMIT 1');
        $stmt->execute([$uuid]);
        $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tmp === false) die("false");

        if ($tmp['TwitchVerified'])
        {
            if ($twitch)
            {
                try
                {
                    $url = "https://api.twitch.tv/kraken/channels/$channel/subscriptions/$tmp[Twitch]?oauth_token=$twitchToken";
                    if (@file_get_contents($url) !== FALSE)
                    {
                        header('X-Service:Twitch');
                        die("true");
                    }
                }
                catch (Exception $e)
                {

                }
            }
        }
        if ($tmp['GameWispVerified'])
        {
            if ($tmp['GameWispExpired']) $gamewispToken = updateGameWispToken($db, $tmp['GameWispRefreshToken'], $uuid);
            if ($gamewisp != null && $gamewisp != -1)
            {
                try
                {
                    $url = "https://api.gamewisp.com/pub/v1/channel/subscriber-for-channel?type=gamewisp&access_token=$gamewispToken&include=tier&user_name=$tmp[GameWisp]";
                    $json = json_decode(@file_get_contents($url), true);
                    foreach ($json['data'] as $data)
                    {
                        if ($data['tier']['data']['level'] == 0) continue;
                        if ($data['tier']['data']['level'] >= $gamewisp)
                        {
                            header('X-Service:GameWisp');
                            die("true");
                        }
                    }
                }
                catch (Exception $e)
                {

                }
            }
        }

        if ($tmp['BeamVerified'])
        {
            if ($tmp['BeamExpired']) $beamToken = updateBeamToken($db, $tmp['BeamRefreshToken'], $uuid, $beamId);
            if ($beam)
            {
                try
                {
                    $opts = [
                        'http' => [
                            'method'  => 'GET',
                            'header' => [
                                "Authorization: Bearer $beamToken"
                            ],
                        ]
                    ];
                    $context = stream_context_create($opts);
                    $result = json_decode(file_get_contents("https://beam.pro/api/v1/channels/$beamChannel/relationship?user=$tmp[BeamId]", false, $context), true);
                    if (in_array('Subscriber', $result['status']['roles']))
                    {
                        header('X-Service:Beam');
                        die("true");
                    }
                }
                catch (Exception $e)
                {

                }
            }
        }
        die("false");
    }
}
catch (Exception $e)
{
    http_response_code(500);
    error_log($e->getMessage());
    echo $e->getMessage();
    die("The server *may* be on fire. Please contact admin dries007 net (fill in an at and period) with some information about what happened.");
}
