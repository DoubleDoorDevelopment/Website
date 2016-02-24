<?php

if (!isset($_GET["uuid"]))
{
    http_response_code(400);
    header('Content-Type:text/plain');
    echo "Returns Twitch / GameWisp / ... usernames associated with a Minecraft UUID.\n";
    echo "Usage:\n";
    echo "\tOne username:\n\t\t?uuid='uuid as string'\n";
    echo "\tMultiple usernames:\n\t\t?uuid[]='uuid1'&uuid[]='uuid2'&uuid[]='uuid3'\n";
    echo "Result:\n";
    echo "\tHTTP 200 JSON, see below.\n";
    echo "\tHTTP 4xx If uuid isn't the proper format (32 or 36 char, alphanumerical).\n";
    echo "\tHTTP 5xx If there's fire coming out of the server.\n";
    echo "JSON:\n";
    echo "\n" . json_encode(["c93ca410-8003-40ef-81d7-ac88719e2038" => ["Twitch" => "dries007", "GameWisp" => "dries007"]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    echo "The real output is not pretty printed, unless you add 'pp' to the GET parameters.\n";
    echo "The key used depends the supplied UUID.\n(If you use 32 char, it'll be 32 char)\n";
    echo "An unknown user will NOT be included in the output object.\n";
    echo "Any service not verified is left out.\n";
    echo "If a user has no services verified, the user is excluded from the output.\n";
    die("\nSee https://github.com/DoubleDoorDevelopment/Website for more information.");
}

// This amount of fraud / error detection is probably more then strictly required, but better safe then sorry.
function checkUUID($uuid)
{
    switch (strlen($uuid))
    {
        default:
            http_response_code(400);
            header('Content-Type:text/plain');
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
        header('Content-Type:text/plain');
        die("Your UUID needs to be in 32 char (no dashes) or 36 char (with dashes) alphanumeric format.");
    }
}

if (is_array($_GET['uuid']))
{
    foreach ($_GET['uuid'] as $UUID)
    {
        checkUUID($UUID);
    }
    $UUIDs = $_GET['uuid'];
}
else
{
    checkUUID($_GET['uuid']);
    $UUIDs = [$_GET['uuid']];
}
// If we got here, its all ok

try
{
    include "mysql.inc.php";
    $db = makeDBConnection();
    $stmt = $db->prepare("SELECT Twitch, BIN(TwitchVerified), GameWisp, BIN(GameWispVerified) FROM minecraft WHERE UUID=?");
    $out = [];
    foreach ($UUIDs as $UUID)
    {
        $stmt->execute(array(strlen($UUID) == 36 ? str_replace('-', '', $UUID) : $UUID));
        $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tmp === false) continue;

        $user = [];

        if ($tmp['BIN(TwitchVerified)'] == 1) $user['Twitch'] = $tmp['Twitch'];
        if ($tmp['BIN(GameWispVerified)'] == 1) $user['GameWisp'] = $tmp['GameWisp'];

        if (!empty($user)) $out[$UUID] = $user;
    }

    header('Content-Type:application/json');
    echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | (isset($_GET['pp']) ? JSON_PRETTY_PRINT : 0));
}
catch (Exception $e)
{
    http_response_code(500);
    header('Content-Type:text/plain');
    error_log($e->getMessage());
    die("The server *may* be on fire. Please contact admin dries007 net (fill in an at and period) with some information about what happened.");
}