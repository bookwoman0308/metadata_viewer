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


$path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
include_once($path . 'config.php');
include_once('util.php');

echo '<a href="index.php">Back to main page</a><Br><br>';
$output = '';

if (isset($_POST['submit'])) {
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






