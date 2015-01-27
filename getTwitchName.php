<?
    $json = is_file("twitch.json") ? json_decode(file_get_contents("twitch.json"), true) : array();
    if (isset($_GET["uuid"]))
    {
        $key = str_replace("-", "", $_GET["uuid"]);
        echo($json[$key]);
    }
?>

<html>
    <body>
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-59021772-1', 'auto');
          ga('send', 'pageview');

        </script>
    </body>
</html>