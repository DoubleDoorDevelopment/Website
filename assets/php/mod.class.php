<?
class mod 
{
  public $displayName;
  public $name;
  public $url;
  public $description;
  public $buildurl;
  public $timestamp;
  public $files = array();
  public $version;
  public $mcVersion;
  
  function __construct($json)
  {
    $this->displayName = $json["displayName"];
    $this->name = $json["name"];
    $this->url = $json["url"];
    $this->description = $json["description"];
    $this->buildurl = $json["lastStableBuild"]["url"];
    $this->timestamp = $json["lastStableBuild"]["timestamp"];
    
    foreach ($json["lastStableBuild"]["artifacts"] as $file)
    {
      if (!contains($file["fileName"], "jar")) continue;
      
      if (contains($file["fileName"], "dev")) $this->files["dev"] = $file;
      else if (contains($file["fileName"], "src")) $this->files["src"] = $file;
      else 
      {
        $this->files["normal"] = $file;
        $versions = explode("-", str_replace(".jar", "", $file["fileName"]));
        $this->version = $versions[2];
        $this->mcVersion = $versions[1];
      }
    }
  }
  
  function isCore()
  {
    return "D3Core" === $this->name;
  }
  
  function isSite()
  {
    return "DoubleDoorDevelopmentWebsite" === $this->name;
  }
  
  function printBox()
  {
    echo '
    <div class="panel panel-' . ($this->isCore() ? "success" : "info") . '">
      <div class="panel-heading"><h2 class="panel-title-custom hiddenlink"><a href="?p=modlist&mod=' . $this->name . '">' . $this->displayName . '</a></h2></div>
      <div class="panel-body">
        <p>' . $this->description . '</p>
        <h3>Last successful build</h3>
        <p>
          ' . date("Y-m-d H:i", $this->timestamp / 1000) . '<br>
          Version: ' . $this->version . '<br>
          Minecraft: ' . $this->mcVersion . '<br>
          By using this mod you agree to its licence (see github).
        </p>
        <div class="btn-group">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            Download <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a href="' . $this->buildurl . "artifact/" . $this->files["normal"]["relativePath"] . '">' . $this->files["normal"]["fileName"] . '</a></li>
            <li class="divider"></li>
            <li><a href="' . $this->buildurl . "artifact/" . $this->files["dev"]["relativePath"] . '">' . $this->files["dev"]["fileName"] . '</a></li>
            <li><a href="' . $this->buildurl . "artifact/" . $this->files["src"]["relativePath"] . '">' . $this->files["src"]["fileName"] . '</a></li>
          </ul>
        </div>
      </div>
    </div>';
  }
}
