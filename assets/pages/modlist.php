<h3 class="hiddenlink"><a href="?p=modpacks">Making a modpack?</a></h3>
<?
  include "assets/php/mod.class.php";
  $mods = array();
  $jobs = json_decode(file_get_contents("http://jenkins.dries007.net/view/D3_mods/api/json?tree=jobs[url,description,name,displayName,lastStableBuild[url,artifacts[*],timestamp]]"), true)["jobs"];
  foreach ($jobs as $job)
  {
    $mod = new mod($job);
    $mods[$mod->name] = $mod;
  }
  
  if (isset($_GET["mod"]) && $_GET["mod"] !== CORE) echo '<div class="col-md-6">';
  
  $mods[CORE]->printBox();
  
  if (isset($_GET["mod"]) && $_GET["mod"] !== CORE) echo '</div>';
  
  $i = 0;
  foreach ($mods as $mod)
  {
    if ($mod->isCore()) continue;
    if (isset($_GET["mod"]) && $mod->name !== $_GET["mod"]) continue;
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
