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
$path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
include_once($path . 'config.php');
include_once('util.php');

echo '<a href="index.php">Back to main page</a><Br><br>';
$output = '';

//print_r($_POST);

// First need to retrieve the row that the user wants to edit:

if (isset($_POST['submit']) && isset($_POST['direction'])) {

  //print_r($_POST);
  $key_arr = array_keys($_POST);  //this is because $_POST is an associative array and I don't know the keys
  $input = $key_arr[0];  //this will either be cause_id, cause_set_id, cause_set_version_id, location_id, etc etc.
  $selection = $_POST[$input];
  $element = $_POST['element'];
  $table_name = $_POST['table'];
  $output = set_up_edit($output, $input, $selection, $element, $table_name, $dbs);
  echo $output;
}  


// Second need to run the update query in the selected shared table:

if (isset($_POST['submit']) && isset($_POST['edit_xxxx'])) {
  //print_r($_POST);
  $post_array = $_POST;
  $input_array = array();
  $all_input_fields = array();
  $all_input_rows = array();

  foreach ($post_array as $key => $value) {       
        if ($key != 'submit') {
           $input_array[$key] = strip_from_input($value); 
           $all_input_fields[] = $key;    //this array will store the fields, i.e. location_name, location_name_ascii
           $all_input_rows[] = strip_from_input($value);    //this array will store the values submitted by the user for the fields
         }
      } 

  $element = strtok($all_input_fields[1], '_');   //strtok function returns first word before the '_ ', so cause or location or rei
  
  if (!empty($all_input_rows)) {
     add_element_to_table($input_array, $element, $dbs);  //If the user entered values, add them to the table
  }
  else {
     echo 'error';
  }
  

} // end if submit


function set_up_edit($output, $input, $selection, $element, $table_name, $dbs) {
 
  $sql = <<<SQL
    SELECT * FROM $table_name WHERE $input = $selection
SQL;

//   $sql = <<<SQL
//     SELECT * FROM $table_name
// SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  //$rows_num = $query->fetchColumn();

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
   for($j = 0; $j < 1; $j++) {
      $output .= "<tr>";

      for($k = 0; $k < $fields_num; $k++) {
         $varprint = $print_array[0][$all_fields[$k]];
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


function add_element_to_table($input_array, $element, $dbs) {
  
  $table_name = $element;
  
  //Retrieve information from the table
  $result_array = array();
  $result_array = retrieve_table_info($table_name, $dbs, $result_array);

  //Get values from the array that was returned from the function call
  $result_row_count = $result_array['result_row_count']; 
  $all_table_rows = $result_array['all_table_rows'];


 if ($result_row_count > 0) {  //if the table is NOT empty

      //Generate the id for the next element to be added; slightly complicated because Forecasting already created cause_id 1001
      if ($element == 'cause') {
        $next_id = $all_table_rows[$result_row_count-2][0] + 1;  //has to use minus 2 because of cause_id 1001
           if ($next_id == 1001) {
             $next_id = 1002;
            }
       }
      else {
        $next_id = $all_table_rows[$result_row_count-1][0] + 1;  //if not cause use result row count - 1
      }

  }

  elseif ($result_row_count == 0) {  //if the table is empty
     $next_id = 1;
  } 

  //Now time to build the query to insert the input values into the table
  $exec_q = "INSERT INTO shared.$table_name ($element" . "_id, ";
   
 //Load up the fields to be added to the table
  foreach($input_array as $key => $value) {
       if (!empty($value)) {
          $exec_q .= "$key, ";
        } 
  } 

 //Remove trailing comma if there is one
 $exec_q = rtrim($exec_q);
 $exec_q = rtrim($exec_q, ',');
 $exec_q .= ") VALUES ($next_id, ";

 //Add the input values to the insert query so long as the value is not null
 foreach($input_array as $key => $value) {    
       if (!empty($value)) {
               if ((strpos($key, '_id') !== FALSE) || (strpos($key, 'level') !== FALSE)) {  //for string values add quotes
                       $exec_q .= "$value, "; 
                    }
               else {
                  $exec_q .= '"'. $value . '", ';   //for numeric values no quotes needed
                } 
        } 
  } 

 //Remove trailing comma if there is one
 $exec_q = rtrim($exec_q);
 $exec_q = rtrim($exec_q, ',');
 $exec_q .= ");";

  //echo '<br><br>'. $exec_q;   //To display the INSERT query command

 //Execute the query to add the new entry into the table
 try {
    //$sql = "INSERT INTO USERS (userName, password) VALUES ('test', 'testy')";
    $insert_query = $dbs['shared']['conn']->prepare($exec_q);
    $insert_query -> execute();
    echo '<h3>'. $input_array[$element .'_name'] . ' has been added to the ' . $element . ' table</h3>';
 } 
 catch(PDOException $e) {
    echo $e->getMessage();
  }

}


?>


  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
  
  </div>
</body>
</html>






