<?php

if (isset($_GET["ts"])) header("Location: ts3server://ts.doubledoordev.net/");

?>
<!DOCTYPE html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, minimum-scale=0.1">
    <title>Double Door Development</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type='text/css'>
    <link href='//fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>
    <link href="assets/css/style.css" rel="stylesheet" type='text/css'>
    <meta name="google-site-verification" content="pVuXOM2EbuoENke7yMvrXbC7NUNzJUNLQ3hC7XGYnrs" />
  </head>
  <body>
    <div class="container container-narrow" id="wrap">
      <div class="row">
        <h1 class="hiddenlink"><a href="?p=home">Double Door Development</a></h1>
        <h2>Broken link?</h2>
      </div>
    </div>
    <!-- Footer -->
    <div id="footer" class="hiddenlink">
        <div class="container">
            <p class="muted credit">
                <a href="?p=about">&copy; Double Door Development 2014</a><br>
      Built in <? $end = microtime(true); echo round(($end - $start), 4);?> sec.
            </p>
        </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
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