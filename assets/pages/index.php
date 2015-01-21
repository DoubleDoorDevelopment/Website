<table width=100% height=85%>
  <tr>
    <td style="text-align: center; vertical-align: middle;">
      <a href="?p=home">
        <img src="assets/img/DoubleDoorDevNoBg.png" onmouseover="on()" onmouseout="off()" height=300px />
      </a>
    </td>
  </tr>
</table>

<noscript><a href="?p=home">No script :( Click here.</a></noscript>
<audio><source src="assets/mp3/door_open.mp3" /></audio>
<audio><source src="assets/mp3/door_close.mp3" /></audio>
<script>
  function on()
  {
    document.getElementsByTagName('img')[0].src = 'assets/img/DoubleDoorDevOpenNoBg.png';
    document.getElementsByTagName('audio')[0].play();
  }
  
  function off()
  {
    document.getElementsByTagName('img')[0].src = 'assets/img/DoubleDoorDevNoBg.png';
    document.getElementsByTagName('audio')[1].play();
  }
</script>
