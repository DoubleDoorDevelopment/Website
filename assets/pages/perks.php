<?
  function getItem($thing)
  {
    if (!is_array($thing)) return "Not set.";
    $out = "Name: " . $thing["name"];
    if (isset($thing["meta"])) $out .= "<br>Meta: " . $thing["meta"];
    if (isset($thing["size"])) $out .= "<br>Size: " . $thing["size"];
	if (isset($thing["display"])) $out .= "<br>Display: " . $thing["display"];
	if (isset($thing["color"])) $out .= "<br>Color: #" . dechex($thing["color"]);
	if (isset($thing["lore"])) $out .= "<br>Lore: " . implode(" - ", $thing["lore"]);
    return $out;
  }
  
  function utf8ize($d) {
    if (is_array($d)) {
      foreach ($d as $k => $v) {
        $d[$k] = utf8ize($v);
      }
    } else if (is_string ($d)) {
      return utf8_encode($d);
    }
    return $d;
  }
  
  $json = json_decode(file_get_contents("perks.json"), true);
  
  if (ADMIN && isset($_GET["delete"]))
  {
    unset($json[$_GET["delete"]]);
    file_put_contents("perks.json", json_encode($json, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
  }
  
  if (ADMIN && isset($_POST["name"]))
  {
    $data = array(
    "displayname" => $_POST["displayname"],
    "hat" => array(
      "name" => $_POST["hatName"],
      "meta" => $_POST["hatMeta"],
	  "display" => $_POST["hatDisplay"],
	  "color" => @$json[$_POST["name"]]["hat"]["color"],
	  "lore" => @$json[$_POST["name"]]["hat"]["lore"]),
    "drop" => array(
      "name" => $_POST["dropName"],
      "meta" => $_POST["dropMeta"],
      "size" => $_POST["dropSize"],
	  "display" => $_POST["dropDisplay"],
	  "color" => @$json[$_POST["name"]]["drop"]["lore"],
	  "lore" => @$json[$_POST["name"]]["drop"]["lore"])
    );
    if ($data["displayname"] === "") unset($data["displayname"]);
    if ($data["hat"]["name"] === "") unset($data["hat"]["name"]);
    if ($data["hat"]["meta"] === "") unset($data["hat"]["meta"]);
	//if ($data["hat"]["size"] === "") unset($data["hat"]["size"]); because 0
    if ($data["hat"]["display"] === "") unset($data["hat"]["display"]);
    if ($data["hat"]["color"] === 0 || $data["hat"]["color"] == null) unset($data["hat"]["color"]);
    if (empty ($data["hat"]["lore"]) || $data["hat"]["lore"][0] === "") unset($data["hat"]["lore"]);
    if (empty($data["hat"]))         unset($data["hat"]);
    
    if ($data["drop"]["name"] === "") unset($data["drop"]["name"]);
    if ($data["drop"]["meta"] === "") unset($data["drop"]["meta"]);
    if ($data["drop"]["size"] === "") unset($data["drop"]["size"]);
	if ($data["drop"]["display"] === "") unset($data["drop"]["display"]);
    if ($data["drop"]["color"] === 0 || $data["drop"]["color"] == null) unset($data["drop"]["color"]);
	if (empty ($data["drop"]["lore"]) || $data["drop"]["lore"][0] === "") unset($data["drop"]["lore"]);
    if (empty($data["drop"]))         unset($data["drop"]);
    
    $json[$_POST["name"]] = $data;
    file_put_contents("perks.json", json_encode($json, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
  }
  
  if (ADMIN && (isset($_GET["new"]) || isset($_GET["edit"])))
  {
    $isnew = isset($_GET["new"]);
    $name = @$_GET["edit"];
    $data = isset($json[$name]) ? $json[$name] : array();
?>
<h2>Edit <? echo $name; ?>'s dev perks</h2>
<p class="hiddenlink"><a href="http://minecraft-ids.grahamedgecombe.com/">Minecraft id list</a></p>
<p><b>No offensive stuff in here!</b></p>
<div class="col-md-4">
  <? include "assets/php/colors.php"; ?>
</div>
<div class="col-md-4">
  <form role="form" method="post" action="?p=perks">
    <? if ($isnew) { ?>
    <!-- Username -->
    <div class="form-group">
      <label for="name">Username name</label>
      <input class="form-control" name="name" id="name" placeholder="Username (case sensitive!)">
    </div>
    <? } ?>
    <!-- Display Name -->
    <div class="form-group">
      <label for="displayname">Display name</label>
      <input class="form-control" name="displayname" id="displayname" placeholder="Display name" value="<? echo @$data["displayname"] ?>">
    </div>
    <!-- Hat Stuff -->
    <div class="form-group">
      <label for="hatName">Hat Item</label>
      <div class="input-group">
        <div class="input-group-addon">Name</div>
        <input class="form-control" name="hatName" id="hatName" placeholder="Block name" value="<? echo @$data["hat"]["name"] ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Meta</div>
        <input class="form-control" name="hatMeta" id="hatMeta" placeholder="0 by default" value="<? echo @$data["hat"]["meta"] ?>">
      </div>
    </div>
	<div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Display name</div>
        <input class="form-control" name="hatDisplay" id="hatDisplay" placeholder="" value="<? echo @$data["hat"]["display"] ?>">
      </div>
    </div>
    <!-- Drop Stuff -->
    <div class="form-group">
      <label for="dropName">Drop Item</label>
      <div class="input-group">
        <div class="input-group-addon">Name</div>
        <input class="form-control" name="dropName" id="dropName" placeholder="Block name" value="<? echo @$data["drop"]["name"] ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Meta</div>
        <input class="form-control" name="dropMeta" id="dropMeta" placeholder="0 by default" value="<? echo @$data["drop"]["meta"] ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Size</div>
        <input class="form-control" name="dropSize" id="dropSize" placeholder="1 by default" value="<? echo @$data["drop"]["size"] ?>">
      </div>
    </div>
	<div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Display name</div>
        <input class="form-control" name="dropDisplay" id="dropDisplay" placeholder="" value="<? echo @$data["drop"]["display"] ?>">
      </div>
    </div>
    <!-- Btn -->
    <? if (!$isnew) { ?><input type="hidden" name="name" value="<? echo $name;?>"><? } ?>
    <button type="submit" class="btn btn-default">Submit</button>
  </form>
</div>
<? } else { ?>
<h2>Dev perk list</h2>
<table class="table table-hover">
  <thead>
    <tr>
      <th class="col-md-2">Username</th>
      <th class="col-md-2">Displayname</th>
      <th class="col-md-3">Hat</th>
      <th class="col-md-3">Drop</th>
      <th class="col-md-2"></th>
    </tr>
  </thead>
  <tbody>
    <? foreach ($json as $name => $row) { ?>
    <tr>
      <td><? echo $name; ?></td>
      <td><? echo $row["displayname"]; ?></td>
      <td><? echo getItem(@$row["hat"]); ?></td>
      <td><? echo getItem(@$row["drop"]); ?></td>
      <td>
        <? if (ADMIN) { ?>
        <div class="btn-group">
          <a href="?p=perks&edit=<? echo $name; ?>" class="btn btn-default">Edit</a>
          <a href="?p=perks&delete=<? echo $name; ?>" class="btn btn-danger">Delete</a>
        </div>
        <? } ?>
      </td>
    </tr>
    <? } ?>
  </tbody>
</table>
<? if (ADMIN) { ?>
<a href="?p=perks&new" class="btn btn-default">Add new</a>
<? }
  } ?>
<? if (!ADMIN) { ?><p><a href="?p=login">Log in for edit mode</a></p><? } ?>
