<?
  include "assets/php/project.class.php";
  $mods = array();
  $jobs = json_decode(file_get_contents("http://jenkins.dries007.net/view/D3_misc/api/json?tree=jobs[url,description,name,displayName,lastStableBuild[url,artifacts[*],timestamp]]"), true)["jobs"];
  foreach ($jobs as $job)
  {
    $mod = new mod($job);
    $mods[$mod->name] = $mod;
  }
  
  $i = 0;
  foreach ($mods as $mod)
  {
    if (isset($_GET["project"]) && $mod->name !== $_GET["project"]) continue;
    $newRow = $i % 2 == 0;
    
    if ($newRow) echo '<div class="row">';
    echo '<div class="col-md-6">';
    
    $mod->printBox();
    
    echo '</div>';
    if (!$newRow) echo '</div>';
    
    $i++;
  }
  if (count($mods) % 2 != 0) echo '</div>';
?>
