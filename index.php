<?
  // Error reporting for debug
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
  
  session_start();
  
  $page = "index";
  if (isset($_GET['p'])) $page = $_GET['p'];
  else if (isset($_GET['page'])) $page = $_GET['page'];
  
  include("mysql.php");
  
  if (isset($_GET["logout"])) 
  {
    session_destroy();
    $_SESSION = array();
  }
  
  define ("ADMIN", isset($_SESSION["admin"]));
  
  function contains($keystack, $needle)
  {
    return strpos($keystack, $needle) !== FALSE;
  }
?>
<!-- 
    Copyright (c) 2014, Dries007 & DoubleDoorDevelopment
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this
      list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.

    * Neither the name of DoubleDoorDevelopment nor the names of its
      contributors may be used to endorse or promote products derived from
      this software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
    FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
    DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
    SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
    CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
    OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
    OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
-->
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, minimum-scale=0.1">
    <title>Double Door Development</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css'>
    <link href="assets/css/style.css" rel="stylesheet" type='text/css'>
  </head>
  <body>
    <div class="container container-narrow" id="wrap">
      <div class="row">
        <h1 class="hiddenlink"><a href="?p=home">Double Door Development</a></h1>
        <? if (ADMIN) echo "<p class=\"hiddenlink\">Admin mode <a href=\"?logout&p=$page\">(log out)</a></p>"; ?>
      </div>
      <? 
        if (is_file("assets/pages/$page.php")) include "assets/pages/$page.php";
        else echo "<h1>Oops, 40x'ed</h1><h2>The file you are looking for doesn't exist or you don't have permission to view it.</h2>";
      ?>
    </div>
    <!-- Footer -->
		<div id="footer" class="hiddenlink">
			<div class="container">
				<p class="muted credit">
					<a href="?p=about">&copy; Double Door Development 2014</a>
				</p>
			</div>
		</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  </body>
</html>
