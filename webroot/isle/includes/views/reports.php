<?php
  if (count($reportData))
  {
?>
<table class="table table-striped">
  <thead>
  <tr>
<?php
    foreach ($reportData[0] as $col => $value)
    {
      echo '
    <th>'.$col.'</th>';
    }
?>
  </tr>
  </thead>
  <tbody>
<?php
    foreach ($reportData as $row)
    {
      echo '
  <tr>';
      foreach ($row as $value)
      {
        echo '
    <td>'.$value.'</td>';
      }
      echo '
  </tr>';
    }
?>
  </tbody>
</table>
<?php
  } else {
    echo '<div id="userMessage" class="alert alert-error" role="alert" aria-label="Report not found or no data available.">Report not found or no data available.</div>';
  }
?>