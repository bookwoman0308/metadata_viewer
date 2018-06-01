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
  <div class="container-fluid">

<?php

  $path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
   include($path . 'config.php');
   //include($path . 'util.php');
     //print_r($_POST);

if (isset($_POST['submit'])) {

	if (isset($_POST['add_element_type'])) {

   $begin_output = '<a href="index.php">Back to main page</a><Br><br>';
   $begin_output .= '<b>Here are the first 5 rows of the table values as an example:</b><br><br>';
   echo $begin_output;

  $input = $_POST['add_element_type'];
  $input = str_replace('add_', ' ', $input);   //transforms the user input to 'cause_element' or 'location_element', e.g.
  $element = strtok($input, '_');               //retrieves cause or location or rei from the string

  $output = '';
  $table_name = $element;
  $output = show_table_for_edit($table_name, $output, $dbs);
  echo $output; 
   
  } //end if

} //end main if



function show_table_for_edit($table_name, $output, $dbs) {

  $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  $result = $query->fetchAll();

  //Add table fields to an array
  $q = $dbs['shared']['conn']->prepare("DESCRIBE $table_name");
  $q->execute();
  $all_fields = $q->fetchAll(PDO::FETCH_COLUMN);

  $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name
SQL;

  $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
  $query_rows->execute();
  $rows_num = $query_rows->fetchColumn();

  //Add table values to an array
  $print_array = array();
  foreach($result as $key) {
      $print_array[] = $key;
  }

   //Start printing table
   $output .= '<div class="table-responsive">';
   $output .= "<table class='table table-striped'>";

   // printing table headers
   $output .= "<thead><tr>";

   for($l = 0; $l < $fields_num; $l++) {
       $output .= "<th>{$all_fields[$l]}</th>";
    }
    $output .= "</tr></thead>\n";

   // printing table rows
   for($j = 0; $j < 5; $j++) {
      $output .= "<tr>";

      for($k = 0; $k < $fields_num; $k++) {
         $varprint = $print_array[$j][$all_fields[$k]];
         $output .= "<td>$varprint</td>";
       }

       $output .= "</tr>\n";
    }
     $output .= "</table></div>";

// - - - - - - - - - - - 

   $output .= '<br><br><b>Type in your values in place of the existing labels:</b>';
   $output .= '<br><br><i>(note -- you do not type in the <b>id</b> as it is an auto-increment field)</i><br>';
   $output .= '<br><br><form action="form_add_element_complete.php" method="post">';
   $output .= '<div class="table-responsive">';
   $output .= "<table class='table table-striped' border='1'>";
   
   // printing table headers
   $output .= "<tr>";

   for($m = 1; $m < $fields_num; $m++) {
       $output .= "<td>{$all_fields[$m]}</td>";
    }
    $output .= "</tr>";

    //print one row of input text boxes
    $output .= "\n<tr>";

    for($p = 1; $p < $fields_num; $p++) {
         $output .= '<td><Input type = "text" Name ="' . $all_fields[$p] . '"></td>';
       }

    $output .= "</tr></table></div>";
    $output .= '<input type="submit" name="submit"></form>';

 return $output;

}


?>

 
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
  
 </div>
</body>
</html>






