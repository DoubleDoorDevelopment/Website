<?
    $json = is_file("twitch.json") ? json_decode(file_get_contents("twitch.json"), true) : array();
    if (isset($_GET["uuid"]))
    {
        $key = str_replace("-", "", $_GET["uuid"]);
        echo($json[$key]);
    }
?>