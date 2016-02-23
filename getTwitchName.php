<?php

header('Content-Type:text/plain');

if (!isset($_GET["uuid"]))
{
    http_response_code(400);
    echo "Returns Twitch username associated with a Minecraft UUID.\n";
    echo "Usage:\n\t?uuid='uuid as string'\n";
    echo "Result:\n";
    echo "\tHTTP 200 Plain text twitch username if uuid is in the database, empty response otherwise.\n";
    echo "\tHTTP 4xx If uuid isn't the proper format (32 or 36 char, alphanumerical).\n";
    echo "\tHTTP 5xx If there's fire coming out of the server.\n";
    die("\nSee https://github.com/DoubleDoorDevelopment/Website for more information.");
}

// This amount of fraud / error detection is probably more then strictly required, but better safe then sorry.

switch (strlen($_GET['uuid']))
{
    default:
        http_response_code(400);
        die("Your UUID needs to be in 32 char (no dashes) or 36 char (with dashes) alphanumeric format.");
    //c93ca410800340ef81d7ac88719e2038
    case 32:
        $uuid = $_GET['uuid'];
        break;
    //c93ca410-8003-40ef-81d7-ac88719e2038
    case 36:
        $uuid = str_replace('-', '', $_GET['uuid']);
        break;
}

if (!ctype_alnum($uuid))
{
    http_response_code(400);
    die("Your UUID needs to be in 32 char (no dashes) or 36 char (with dashes) alphanumeric format.");
}

try
{
    include "mysql.php";
    $db = makeDBConnection();
    $stmt = $db->prepare("SELECT Twitch FROM minecraft WHERE UUID=? AND TwitchVerified = 1");
    $stmt->execute(array($uuid));
    echo $stmt->fetch(PDO::FETCH_NUM)[0];
}
catch (Exception $e)
{
    http_response_code(500);
    error_log($e->getMessage());
    die("The server *may* be on fire. Please contact admin dries007 net (fill in an at and period) with some information about what happened.");
}