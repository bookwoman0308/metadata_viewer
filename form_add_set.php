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
//print_r($_POST);


//This first part is to display all set options and provide a form for the user to enter new values

if (isset($_POST['submit']) && $_POST['direction'] == 'make_new_set') {

  $begin_output .= '<b>Here are the first 5 rows of the ' . $element . '_set table values as an example:</b><br><br>';
  echo $begin_output;

  $output = '';
  $element = $_POST['element'];
  $output = show_table_for_edit($element, $output, $dbs);
  echo $output; 

}

//The second part is to add the user's values for the new set into a tmp_element_set table

if (isset($_POST['submit_set_values']) && $_POST['submit_set_values'] == 'add set') {

  //print_r($_POST);
  $post_array = $_POST;
  $input_array = array();
  $table_name = '';
  $element = $_POST['element'];

  $all_input_fields = array();
  $all_input_rows = array();

  foreach ($post_array as $key => $value) {       
        if (($key != 'submit_set_values') && ($key != 'element')) {
           $input_array[$key] = $value; 
           $all_input_fields[] = $key;     //this will be an array of the fields, i.e. location_set_name, location_set_description
           $all_input_rows[] = $value;     //this will be the user's values for the fields, i.e. Forecasting
         }
      } 

  if (!empty($all_input_rows)) {
     //print_r($post_array);
     //print_r($all_input_rows);
     add_set_to_table($input_array, $element, $dbs);  //If the user entered values, add them to the table
  }
  else {
     echo 'error';
  }
  
} // end if submit


function add_set_to_table($input_array, $element, $dbs) {
  
  //Retrieve information from the table
  $table_name = 'tmp_' . $element . '_set';
  $result_array = array();
  $result_array = retrieve_table_info($table_name, $dbs, $result_array);

  //Get values from the array that was returned from the function call
  $result_row_count = $result_array['result_row_count']; 
  $all_table_rows = $result_array['all_table_rows'];

  if ($result_row_count > 0) {

      //Generate the id for the next element_set row to be added
      $next_id = $all_table_rows[$result_row_count-1][0] + 1;  
  }

  elseif ($result_row_count == 0) {
     $next_id = 1;
  }    

 
 //Now time to build the query to insert the input values into the table
  $exec_q = "INSERT INTO shared.$table_name ($element" . "_set_id, ";
  
 // $keys = array_keys($input_array);
 // $last_row = end($keys);
 
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

//      $sql = <<<SQL
//     SELECT * FROM $table_name
// SQL;

  //Execute the query to add the new entry into the table
  $query = $dbs['shared']['conn']->prepare($exec_q);
  $query -> execute();

  if($query->errorCode() == 0) {

    //if the execute is successful
     echo '<h3>The new set was successfully added to the tmp_'. $element . '_set table</h3>';
     $set_id_label = $element . '_set_id';

    $next_step_output .= "<br><br><form action='form_add_hierarchy.php' method='post'>
   <input type='hidden' value=$next_id name=$set_id_label>
   <input type='hidden' value=$element name='element'>
   <input type='hidden' value='created' name='selection_method'>
   <input type='submit' value='continue' name='submit'></form><br><br>";
    echo $next_step_output;
  }

  else {
    $errors = $query->errorInfo();
    echo($errors[2]);
  }

}

function show_table_for_edit($element, $output, $dbs) {

   $table_name = $element . '_set';

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

     $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name
SQL;

   $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
   $query_rows->execute();
   $rows_num = $query_rows->fetchColumn(); 


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

   $output .= '<br><b>Type in your values in place of the existing labels:</b>';
   $output .= '<br><br><i>(note -- you do not type in the <b>id</b> as it is an auto-increment field)</i><br>';
   $output .= '<br><form method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
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
         //$output .= '<td><Input type = "text" Name ="' . $all_fields[$p] . '" value = "'. 'what' . '"></td>';
       }

    $output .= "</tr></table></div>";
    $output .= "<input type='hidden' value=$element name='element'>";
    $output .= '<input type="submit" value="add set" name="submit_set_values"></form>';

 mysqli_close($con);
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






