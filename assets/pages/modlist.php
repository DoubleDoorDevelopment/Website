<h3 class="hiddenlink"><a href="?p=modpacks">Making a modpack?</a></h3>
<?
  $notshown = array("D3Core", "DoubleDoorDevelopmentWebsite");
  $jobs = json_decode(file_get_contents("http://jenkins.dries007.net/view/DoubleDoorDevelopment/api/json?tree=jobs[url,description,name,displayName,lastStableBuild[url,artifacts[*],timestamp]]"), true)["jobs"];

  $job = array();
  foreach ($jobs as $temp_job)
  {
    $job = $temp_job;
    if ($job["name"] === "D3Core") break;
  }
  
  $lastStableBuild = $job["lastStableBuild"];
  $files = array();
  $version = "?";
  $mcVersion = "?";
  foreach ($lastStableBuild["artifacts"] as $file)
  {
    if (!contains($file["fileName"], "jar")) continue;
    
    if (contains($file["fileName"], "dev")) $files["dev"] = $file;
    else if (contains($file["fileName"], "src")) $files["src"] = $file;
    else 
    {
      $files["normal"] = $file;
      $versions = explode("-", str_replace(".jar", "", $file["fileName"]));
      $version = $versions[2];
      $mcVersion = $versions[1];
    }
  }
  
  if (isset($_GET["mod"])) echo '<div class="col-md-6">';
?>
<div class="panel panel-success">
  <div class="panel-heading"><h2 class="panel-title-custom hiddenlink"><a href="?p=modlist&mod=<? echo $job["name"] ?>"><? echo $job["displayName"] ?></a></h2></div>
  <div class="panel-body">
    <p><? echo $job["description"] ?></p>
    <h3>Last successful build</h3>
    <p>
      <? echo date("Y-m-d H:i", $lastStableBuild["timestamp"] / 1000) ?><br>
      Version: <? echo $version ?><br>
      Minecraft: <? echo $mcVersion ?><br>
      By using this mod you agree to its licence (see github).
    </p>
    <div class="btn-group">
      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
        Download <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["normal"]["relativePath"]?>"><? echo $files["normal"]["fileName"] ?></a></li>
        <li class="divider"></li>
        <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["dev"]["relativePath"]?>"><? echo $files["dev"]["fileName"] ?></a></li>
        <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["src"]["relativePath"]?>"><? echo $files["src"]["fileName"] ?></a></li>
      </ul>
    </div>
  </div>
</div>
<? if (isset($_GET["mod"])) echo '</div>';
   else echo "<hr>"?>
<?
  $amountOfJobs = count($jobs);
  $i = 0;
  foreach ($jobs as $job)
  {
    if (in_array($job["name"], $notshown)) continue;
    $newRow = $i % 2 == 0;
    if (isset($_GET["mod"]) && $job["name"] !== $_GET["mod"]) continue;
    if ($newRow) echo "<div class=\"row\">";
    $lastStableBuild = $job["lastStableBuild"];
    $files = array();
    $version = "?";
    $mcVersion = "?";
    foreach ($lastStableBuild["artifacts"] as $file)
    {
      if (!contains($file["fileName"], "jar")) continue;
      
      if (contains($file["fileName"], "dev")) $files["dev"] = $file;
      else if (contains($file["fileName"], "src")) $files["src"] = $file;
      else 
      {
        $files["normal"] = $file;
        $versions = explode("-", str_replace(".jar", "", $file["fileName"]));
        $version = $versions[2];
        $mcVersion = $versions[1];
      }
    }
    ?>
    <div class="col-md-6">
      <div class="panel panel-info">
        <div class="panel-heading"><h2 class="panel-title-custom hiddenlink"><a href="?p=modlist&mod=<? echo $job["name"] ?>"><? echo $job["displayName"] ?></a></h2></div>
        <div class="panel-body">
          <p><? echo $job["description"] ?></p>
          <h3>Last successful build</h3>
          <p>
            <? echo date("Y-m-d H:i", $lastStableBuild["timestamp"] / 1000) ?><br>
            Version: <? echo $version ?><br>
            Minecraft: <? echo $mcVersion ?><br>
            By using this mod you agree to its licence (see github).
          </p>
          <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
              Download <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
              <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["normal"]["relativePath"]?>"><? echo $files["normal"]["fileName"] ?></a></li>
              <li class="divider"></li>
              <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["dev"]["relativePath"]?>"><? echo $files["dev"]["fileName"] ?></a></li>
              <li><a href="<? echo $lastStableBuild["url"] . "artifact/" . $files["src"]["relativePath"]?>"><? echo $files["src"]["fileName"] ?></a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?
    if (!$newRow) echo "</div>";
    $i++;
  }
  if ($amountOfJobs % 2 != 0) echo "</div>";
?>
