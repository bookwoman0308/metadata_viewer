<!DOCTYPE html>
<html lang="en">
<head>
    
  <title>Shared Database User Interface</title>

 <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
 
</head>
<body>
<div class="container">
  
<?php

 $path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
 include_once($path . 'config.php');
 //include_once('util.php');

echo '<a href="index.php">Back to main page</a><Br><br>';

//print_r($_POST);

if (isset($_POST['submit']) && isset($_POST['view_htype'])) {

     $input = $_POST['view_htype'];
     $input = str_replace('view_', ' ', $input);   //transforms the user input to 'cause_hierarchy' or 'location_hierarchy', e.g.
     $element = strtok($input, '_');
     //echo $element;
     $output = '';
     $output = show_table_to_choose_set($element, $output, $dbs);
     echo $output;

} //end if 

//Next step is to choose a hierarchy to base it off of

$key_arr = array_keys($_POST);  //this is to capture the set version id requested by the user the 2nd time through this file
$hierarch_input = $key_arr[0];  //will either be cause, location, or rei_set_id as the value for hierarch_input

if (isset($_POST['submit']) && strpos($hierarch_input, 'set_id')) {
   //print_r($_POST);
   if (isset($_POST[$hierarch_input])) {   //the user has selected a valid set version to view; hierarch_input is cause_set_version_id, i.e.
    
      $element = $_POST['element'];
      $set_id_value = $_POST[$element . '_set_id'];
      $selection_method = $_POST['selection_method'];
      $output = '';
      $output = show_table_to_choose_hierarchy($element, $set_id_value, $selection_method, $output, $dbs);
      echo $output;
   }
}  



function show_table_to_choose_set($element, $output, $dbs) {

   $table_name = $element . "_set";

   $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();

   //Put all the field names into an array
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

   // Now the $all_fields array looks like this:

   // Array ( 
   //  [0] => Choose 
   //  [1] => location_set_version_id 
   //  [2] => location_set_id 
   //  [3] => location_set_version 
   //  [4] => location_set_version_description 
   //  [5] => location_metadata_version_id 
   //  [6] => gbd_round 
   //    )

   // And the full array of values, $print_array, should look like this:

   // Array ( 
   //  [0] => Array ( 
   //    [location_set_version_id] => 1 [location_set_id] => 1 [location_set_version] => GBD Reporting 2010 
   //    [location_set_version_description] => The set of locations used by GBD Reporting in 2010 [location_metadata_version_id] => 1 
   //    [gbd_round] => 2010 ) 
   //  [1] => Array ( 
   //    [location_set_version_id] => 2 [location_set_id] => 1 [location_set_version] => GBD Reporting 2013 
   //    [location_set_version_description] => The set of locations used by GBD Reporting in 2013 [location_metadata_version_id] => 2 
   //    [gbd_round] => 2013 )

   $output .= '<b>Step 1 (mandatory): choose a SET.</b><br>
   If you want to create a new hierarchy from a set that already exists, select the radio button from the rows in the table below
   and then click on the submit button.<Br>
   If the set does not exist, click on the button below to add it.
   <form action="form_add_set.php" method="post">';
   $output .= "<input type='hidden' value='make_new_set' name='direction'>
   <input type='hidden' value=$element name='element'>
   <input type='submit' value='make new set' name='submit'></form><br><br>";

   $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name
SQL;

   $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
   $query_rows->execute();
   $rows_num = $query_rows->fetchColumn(); 

   //Start printing the form and table
   $output .= '<form method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
   $output .= "<div class='table-responsive'><table class='table table-striped'>";

   // printing table headers
   $output .= "<thead><tr>";

   for($l = 0; $l < $fields_num + 1; $l++) {
       $output .= "<td>{$all_fields[$l]}</td>";
    }
    $output .= "</tr></thead>\n";

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
  
  $output .= "</table>";
  $output .= "<input type='hidden' value=$element name='element'>";
  $output .= "<input type='hidden' value='chosen' name='selection_method'>";
  $output .= '<input type="submit" name="submit"></form>';

 return $output;

}

function show_table_to_choose_hierarchy($element, $set_id_value, $selection_method, $output, $dbs) {

   $table_name = $element . "_set_version";
   $set_id_label = $element . "_set_id"; 

   if ($selection_method == 'chosen') {

   $output .= '<b>Step 2: choose a SET VERSION or create a new one.</b> <br>
   If you would like to create a new hierarchy based on a set version that already exists, select the radio button from the table below.<Br>
   To start completely from scratch, click on the button below to add it.
   <form action="form_add_set.php" method="post">';
   $output .= "<input type='hidden' value='make_new_set_version' name='direction'>
   <input type='hidden' value=$element name='element'>
   <input type='submit' value='make new set version' name='submit'></form><br><br>"; 

   $sql = <<<SQL
    SELECT * FROM $table_name where $set_id_label = $set_id_value
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();

   //Put all the field names into an array
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

  $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name where $set_id_label = $set_id_value
SQL;

   $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
   $query_rows->execute();
   $rows_num = $query_rows->fetchColumn();

       //Add a field at the beginning of the array
       array_unshift($all_fields,'Choose');

       $output .= '<form action="form_add_hierarchy_complete.php" method="post">';
       $output .= "<div class='table-responsive'>";
       $output .= "<table class='table table-striped'>";

    // printing table headers
   $output .= "<thead><tr>";

   for($l = 0; $l < $fields_num + 1; $l++) {
       $output .= "<td>{$all_fields[$l]}</td>";
    }
    $output .= "</tr></thead>\n";

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
  
  $output .= "</table>";
  $output .= "<input type='hidden' value=$element name='element'>";
  $output .= "<input type='hidden' value=$set_id_value name=$set_id_label>";
  $output .= "<input type='hidden' value='existing' name='direction'>";
  $output .= '<input type="submit" value="submit" name="submit"></form>';

} //end if

else {

   $output .= '<b>Step 2: create a SET VERSION.</b> <br>
   Click on the button below to add a new set version to the Shared database.
   <form action="form_add_set_version.php" method="post">';
   $output .= "<input type='hidden' value='make_new_set_version' name='direction'>
   <input type='hidden' value=$element name='element'>
   <input type='hidden' value=$set_id_value name=$set_id_label>
   <input type='submit' value='make new set version' name='submit'></form><br><br>";
}

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






