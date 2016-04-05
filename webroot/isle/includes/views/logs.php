<div class="listRightCont">
  <div class="listRight">
    <div class="scroll-pane">
      <h2><a name="error"></a>Error Log</h2>
        <?= nl2br($errorLog) ?>
        <?= nl2br($errorLog1) ?>
        <br />
        <h2><a name="security"></a>Security Log</h2>
        <?= nl2br($securityLog) ?>
        <?= nl2br($securityLog1) ?>
        <br />
        <h2><a name="fourofour"></a>404 Log</h2>
        <?= nl2br($fourofourLog) ?>
        <?= nl2br($fourofourLog1) ?>
    </div>
  </div>
</div>
<div class="listLeft center">
  <ul id="VerColMenu">
    <li><a href="#error">Error Log</a></li>
    <li><a href="#security">Security Log</a></li>
    <li><a href="#fourofour">404 Log</a></li>
  </ul>
</div>