<?php

header('Content-Type:text/plain');

if (!isset($_GET["token"]))
{
    http_response_code(400);
    echo "Endpoint to check if the user is allowed to join a server.\n";
    echo "Usage:\n";
    echo "\tNormal operation:\n\t\t?token='apiToken'&twitch='true OR false'$&gamewisp='-1 OR tier level'$&uuid='MC UUID'\n";
    echo "\tToken check:\n\t\t?token='apiToken'\n";
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

try
{
    include "mysql.php";
    $db = makeDBConnection();

    $stmt = $db->prepare('SELECT UUID, Twitch, TwitchToken, GameWispAccessToken FROM minecraft WHERE APIToken=?');
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

    if (isset($_GET['twitch'], $_GET['gamewisp'], $_GET['uuid']))
    {
        $twitch = filter_var($_GET['twitch'], FILTER_VALIDATE_BOOLEAN);
        $gamewisp = filter_var($_GET['gamewisp'], FILTER_VALIDATE_INT);
        $uuid = checkUUID($_GET['uuid']);

        if ($channelOwner === $uuid)
        {
            header('X-Service:Owner');
            die("true");
        }

        $stmt = $db->prepare('SELECT * FROM minecraft WHERE UUID=?');
        $stmt->execute([$uuid]);
        $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tmp === false) die("false");

        if ($twitch)
        {
            try
            {
                $url = "https://api.twitch.tv/kraken/channels/$channel/subscriptions/$tmp[Twitch]?oauth_token=$twitchToken";
                if (file_get_contents($url) !== FALSE)
                {
                    header('X-Service:Twitch');
                    die("true");
                }
            }
            catch (Exception $e)
            {

            }
        }
        if ($gamewisp != -1)
        {
            try
            {
                $json = json_decode(file_get_contents("https://api.gamewisp.com/pub/v1/channel/subscriber-for-channel?type=gamewisp&access_token=$gamewispToken&include=tier&user_name=$tmp[GameWisp]"), true);

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
        die("false");
    }
}
catch (Exception $e)
{
    http_response_code(500);
    error_log($e->getMessage());
    die("The server *may* be on fire. Please contact admin dries007 net (fill in an at and period) with some information about what happened.");
}
