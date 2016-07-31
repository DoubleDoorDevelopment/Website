<?

class mod 
{
  public $displayName;
  public $name;
  public $url;
  public $description;
  public $buildurl;
  public $timestamp;
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
  }
  
  function printBox()
  {
    echo '
    <div class="panel panel-info">
      <div class="panel-heading"><h2 class="panel-title-custom hiddenlink"><a href="?p=projects&project=' . $this->name . '">' . $this->displayName . '</a></h2></div>
      <div class="panel-body">
        <p>' . $this->description . '</p>
        <h3>Last successful build</h3>
        <p>
          ' . date("Y-m-d H:i", $this->timestamp / 1000) . '<br>
          By using this project you agree to its licence (see github).
        </p>
          <a type="button" class="btn btn-primary" href="http://jenkins.dries007.net/job/' . $this->name . '/">To Jenkins</a>
      </div>
    </div>';
  }
}
