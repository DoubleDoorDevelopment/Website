<?
 if (isset($_POST['submit']) && $_POST['submit'] === "Login")
  {
    $db   = makeDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute(array($_POST['username']));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (crypt($_POST['password'], $user['hash']) === $user['hash'])
    {
      $message = "Logged on successfully.";
      $_SESSION["admin"] = true;
    }
    else
    {
      $message = "Nope.";
    }
  }
  ?>
<table width=100% height=85%>
  <tr>
    <td style="text-align: center; vertical-align: middle;">
      <div class="col-md-offset-4 col-md-4">
        <?if (isset($message)) { ?>
  <div class="alert alert-success fade in">
    <h4><? echo $message ?></h4>
  </div>
  <? } ?>
        <form class="form" role="form" action="?p=login" method="post">
          <input name="username" type="username" class="form-control" placeholder="Username" required autofocus><br>
          <input name="password" type="password" class="form-control" placeholder="Password" required><br>
          <input class="btn btn-lg btn-success btn-block" type="submit" name="submit" value="Login">
        </form>
      </div>
    </td>
  </tr>
</table>