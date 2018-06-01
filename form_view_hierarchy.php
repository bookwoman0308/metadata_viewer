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

 ini_set('display_errors',1);
 error_reporting(E_ALL);
 $path = $_SERVER['DOCUMENT_ROOT'];
 require($path . "/sharedui/config.php");
 //require($path . "/sharedui/util.php");
 //print_r($_POST);

echo '<a href="index.php">Back to main page</a><Br><br>';

//The first if statement displays all the set versions for the user to choose.  The second displays the chosen hierarchy.

if (isset($_POST['submit']) && isset($_POST['view_htype'])) {
  
 $input = $_POST['view_htype'];                 //view_cause_hierarchy, view_location_hierarchy, etc.
 $input = str_replace('view_', ' ', $input);   //transforms the user input to 'cause_hierarchy' or 'location_hierarchy', e.g.
 $element = strtok($input, '_');               //retrieves cause or location or rei from the string

 $output = '';
 $output = show_table($element, $output, $dbs);  //this shows all the set versions with ids and names from which the user can select
 echo $output;
}


//To show the particular hierarchy chosen by the user

$key_arr = array_keys($_POST);  //this is to capture the set version id requested by the user the 2nd time through this file
$hierarch_input = $key_arr[0];  //will either be cause, location, or rei_set_version_id as the value for hierarch_input


if (isset($_POST['submit']) && strpos($hierarch_input, 'set_version_id')) {

   if (isset($_POST[$hierarch_input])) {   //the user has selected a valid set version to view; hierarch_input is cause_set_version_id, i.e.
    
      $selection = $_POST[$hierarch_input];

      $element = strtok($hierarch_input, '_');  //retrieves cause, or location, or rei
  
      $output = '';
      $output = show_hierarchy($element, $selection, $output, $dbs);
      echo $output;
   }
}  

//This displays all the set versions, presented for the user to choose one.

function show_table($element, $output, $dbs) {

    $table_name =  $element . "_set_version";
    $output .= '<b>Here are the available sets for viewing.</b> Select a radio button from the table below:<Br><Br>';

    $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  $rows_num = $query->rowCount();
  
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


   //Start printing the form and table
   $output .= '<form method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
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
  $output .= '<input type="submit" name="submit"></form>';

 return $output;

}


//This displays the specified set version selected by the user

function show_hierarchy($element, $selection, $output, $dbs) {

  $table_name = $element . "_hierarchy_history";
  $version_id = $element . '_set_version_id';
  $output .= 'select * from ' . $table_name . ' where ' . $version_id . '=' . $selection .'<Br><Br>';
 
  $sql = <<<SQL
    SELECT * FROM $table_name WHERE $version_id = $selection
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
    SELECT count(*) FROM $table_name WHERE $version_id = $selection
SQL;

  $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
  $query_rows->execute();
  $rows_num = $query_rows->fetchColumn();

  //Add table values to an array
  $print_array = array();
  foreach($result as $key) {
      $print_array[] = $key;
  }

   $output .= "<div class='table-responsive'><table class='table table-striped'>";

   // printing table headers
   $output .= "</thead><tr>";

   for($l = 0; $l < $fields_num; $l++) {
       $output .= "<td>{$all_fields[$l]}</td>";
    }
    $output .= "</thead></tr>\n";

   // printing table rows
   for($j = 0; $j < $rows_num; $j++) {
      $output .= "<tr>";

      for($k = 0; $k < $fields_num; $k++) {
          $output .= '<td>' . $print_array[$j][$all_fields[$k]] . '</td>';
       }

       $output .= "</tr>\n";
    }
  
  $output .= "</table></div>";

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






