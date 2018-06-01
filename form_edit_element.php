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

   ini_set('display_errors',1);
   error_reporting(E_ALL);
   $path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
   include($path . 'config.php');
   //include($path . 'util.php');

if (isset($_POST['submit']) && isset($_POST['edit_element_type'])) {

   $begin_output = '<a href="index.php">Back to main page</a><Br><br>';
   $begin_output .= '<b>Select the element you wish to edit:</b><br><br>';
   echo $begin_output;

  $input = $_POST['edit_element_type'];
  $element = strtolower($input);
  $output = '';
  $output = choose_edit($element);
  echo $output; 
   
} //end first if


if (isset($_POST['submit']) && isset($_POST['edit_type'])) { 

  $element = $_POST['element'];
  $input = $_POST['edit_type'];
  $selection = str_replace('edit_', '', $input);
  $output = '';
  $output = show_table_for_edit($selection, $element, $output, $dbs);
  echo $output;

} //end second if


function choose_edit($element) {

     $output = '<form method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
     $output .= '<Input type = "radio" Name ="edit_type" value = "edit_element"><b>Cause, Location, Risk Factors, Age Groups</b> (the major tables, i.e. cause)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_type" value = "edit_set"><b>Sets of the element</b> (i.e. cause_set table)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_type" value = "edit_set_version"><b>Set versions of the element</b> (i.e. cause_set_version table)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_type" value = "edit_set_version_active"><b>Active set versions of the element</b> (i.e. cause_set_version_active table)<br><Br>';
     $output .= "<input type='hidden' value=$element name='element'>";
     $output .= '<input type="submit" name="submit">';

     return $output;
}

function show_table_for_edit($selection, $element, $output, $dbs) {

  switch($selection) {
      case 'element':
          $table_name = $element;
          break;    
      case 'set':
          $table_name = $element . '_set';    
          break;
      case 'set_version':
          $table_name = $element . '_set_version';    
          break;
      case 'set_version_active':
          $table_name = $element . '_set_version_active';    
          break;
  } //end switch statement

  $output = '';
  $output .= '<b>Select a radio button from the table below to select a row to edit:</b><Br><Br>';

  $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  $rows_num = $query->rowCount();

  //Add table fields to an array
  $q = $dbs['shared']['conn']->prepare("DESCRIBE $table_name");
  $q->execute();
  $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);

  foreach($table_fields as $key => $value) {
      $all_fields[] = $value; 
  } 

  //Put table values into an array
  $print_array = array();
  foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $print_array[] = $row;
  }

   //Add a field to the beginning of the array called Choose, which will be for displaying to the user
   array_unshift($all_fields,'Choose');

   //Start printing the form and table
   $output .= '<form method="post" action="form_edit_element_complete.php">';
   $output .= "<div class='table-responsive'><table class='table table-striped'>";

   // printing table headers
   $output .= "<tr><thead>";

   for($l = 0; $l < $fields_num + 1; $l++) {
       $output .= "<td>{$all_fields[$l]}</td>";
    }
    $output .= "</thead></tr>\n";

   // printing table rows
   for($j = 0; $j < $rows_num; $j++) {
      $output .= "<tr>";

      for($k = 0; $k < $fields_num + 1; $k++) {
          if ($k==0) {
             $varprint = $print_array[$j][$all_fields[$k+1]];
             $output .= '<td><Input type = "radio" Name ="'. $all_fields[$k+1] . '" value = "' . $varprint . '"></td>';
          }
          else {
             $varprint = $print_array[$j][$all_fields[$k]];
             $output .= '<td>' . $varprint . '</td>';
          }
       }

       $output .= "</tr>\n";
    }
  
  $output .= "</table></div>";
  $output .= "<input type='hidden' value=$element name='element'>";
  $output .= "<input type='hidden' value='get_row' name='direction'>";
  $output .= "<input type='hidden' value=$table_name name='table'>";
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






