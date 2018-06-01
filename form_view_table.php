<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <title>Shared Database User Interface</title>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
 
</head>
<body>
<div class="container">

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
include($path . 'config.php');
//echo $path;

echo '<br><a href="index.php">Back to main page</a><br>';

//print_r($_POST);

if (isset($_POST['submit'])) {

	if (isset($_POST['view_type'])) {

    $output = '';
    $input = $_POST['view_type'];
    $input = str_replace('view_', ' ', $input);   //transforms the user input to 'cause_table' or 'location_table', e.g.
    $element = strtok($input, '_');               //retrieves cause or location or rei from the string
    //echo $element;
   
    show_table($element, $output, $dbs);
  }

}


function show_table($element, $output, $dbs) {

  $sql = <<<SQL
    SELECT * FROM $element
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  $result = $query->fetchAll();
  //print_r($result);

  $q = $dbs['shared']['conn']->prepare("DESCRIBE $element");
  $q->execute();
  $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
  
   echo '<h3>'.$element.'</h3><br>';

   echo "<div class='table-responsive'>";
   echo "<table class='table table-striped'><thead><tr>";
    
  // printing table headers
   for($i = 0; $i < $fields_num; $i++) {
       $field = $query->fetchColumn();
       echo "<th>{$table_fields[$i]}</th>";
    }

    echo "</tr></thead>\n";

$sql_row_count = <<<SQL
    SELECT count(*) FROM $element
SQL;

$query_rows = $dbs['shared']['conn']->prepare($sql_row_count);

$query_rows->execute();

$num_rows = $query_rows->fetchColumn(); 

// printing table rows
$print_array = array();

foreach($result as $key) {
  $print_array[] = $key;
}

  for($m = 0; $m < $num_rows; $m++)  {
      echo "<tr>";

      for($j = 0; $j < $fields_num; $j++) {
        $varprint = $print_array[$m][$table_fields[$j]];
        echo "<td>$varprint</td>";
       }

       echo "</tr>\n";
    }


 echo '</table></div>';
}


?>
  
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
</div>
</body>
</html>






