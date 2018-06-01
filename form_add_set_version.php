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


//This first part is to display all set version fields as an example and provide a form for the user to enter new values

if (isset($_POST['submit']) && $_POST['direction'] == 'make_new_set_version') {

  $element = $_POST['element'];
  $set_id_value = $_POST[$element . '_set_id'];

  $begin_output .= '<b>In the input boxes at the bottom of the screen, type in the new set version information.
  <br>Here are the first 5 rows of the ' . $element . '_set_version table values as an example:</b><br><br>';
  echo $begin_output;

  $output = '';
  $output = show_version_table_for_adding($element, $set_id_value, $output, $dbs);
  echo $output; 

}

//The second part is to add the user's values for the new set version into the tmp_element_set_version table

if (isset($_POST['submit_set_version_values']) && $_POST['submit_set_version_values'] == 'add set version') {

  //echo 'post <br>'; 
  //print_r($_POST);
  $post_array = $_POST;
  $input_array = array();
  $table_name = '';
  $element = $_POST['element'];
  $set_id_value = $_POST[$element . '_set_id'];

  $all_input_fields = array();
  $all_input_rows = array();

  $index = 0;
  foreach ($post_array as $key => $value) {       
        if (($key != 'submit_set_version_values') && ($key != 'element') && !strpos($key, 'set_version_id') && !strpos($key, 'set_id')) {
           $input_array[$key] = $value; 
           $all_input_fields[] = $key;     //this will be an array of the fields, i.e. location_set_name, location_set_description
           $all_input_rows[$index] = $value;     //this will be the user's values for the fields, i.e. Forecasting
           $index++;
         }
      } 
 
 //echo 'input rows <br>'; 

  if (!empty($all_input_rows)) {
     //print_r($post_array);
     //print_r($all_input_rows);
     add_set_version_to_table($input_array, $element, $set_id_value, $dbs);  //If the user entered values, add them to the table
  }
  else {
     echo 'The entry could not be added to the ' . $element . '_set_version table';
  }
  
} // end if submit



function add_set_version_to_table($input_array, $element, $set_id_value, $dbs) {
  
  $set_id_label = $element . "_set_id";

  //Retrieve information from the table
  $table_name = 'tmp_' . $element . '_set_version';
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

  $set_id_label = $element . '_set_id';

  $exec_q = "INSERT INTO shared.$table_name ($element" . "_set_version_id, " . "$element" . "_set_id, ";
  
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
 $exec_q .= ") VALUES ($next_id, $set_id_value, ";

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

//echo '<br><br>'. $exec_q;   //To display the INSERT queryt

 //Execute the query to add the new entry into the table
 $query = $dbs['shared']['conn']->prepare($exec_q);
 $query -> execute();


 //if ($connecter->query($exec_q) === TRUE) {
     echo '<h3>The new set version was successfully added to the tmp_' . $element . '_set_version table</h3>';

    $set_version_id_label = $element . '_set_version_id';

    $next_step_output .= "<br><br><form action='form_add_hierarchy_complete.php' method='post'>";
    $next_step_output .= "<input type='hidden' value=$element name='element'>
   <input type='hidden' value=$next_id name=$set_version_id_label>
   <input type='hidden' value=$set_id_value name=$set_id_label>
   <input type='submit' value='continue to edit' name='submit'></form><br><br>";
    echo $next_step_output;
  // } 

  // else {
  //   echo "Error: " . $connecter->error;
  // }

}

function show_version_table_for_adding($element, $set_id_value, $output, $dbs) {

   $table_name = $element . '_set_version';

   $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();
  $fields_num = $query->columnCount();
  $result = $query->fetchAll();

  //print_r($result);

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

   $output .= '<br><b>Type in your values below the field names:</b><br>';
   $output .= '<br><form method="post" action="'. htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
   $output .= '<div class="table-responsive">';
   $output .= "<table class='table table-striped' border='1'>";
   
   // printing table headers
   $output .= "<tr>";

   for($m = 2; $m < $fields_num; $m++) {
       $output .= "<td>{$all_fields[$m]}</td>";
    }
    $output .= "</tr>";

    //print one row of input text boxes
    $output .= "\n<tr>";

    for($p = 2; $p < $fields_num; $p++) {
         $output .= '<td><Input type = "text" Name ="' . $all_fields[$p] . '"></td>';
         //$output .= '<td><Input type = "text" Name ="' . $all_fields[$p] . '" value = "'. 'what' . '"></td>';
       }

    $set_id_label = $element . '_set_id';

    $output .= "</tr></table></div>";
    $output .= "<input type='hidden' value=$element name='element'>";
    $output .= "<input type='hidden' value=$set_id_value name=$set_id_label>";
    $output .= '<input type="submit" value="add set version" name="submit_set_version_values"></form>';

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






