<?php


function strip_from_input($input) {

  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  return $input;

}


/*
* Function to retrieve all fields and all rows from a table, given the $table_name
*
* Uses database info from config.php
*
*/
function retrieve_table_info($table_name, $dbs, $result_array) {

  $all_table_fields = array();
  $all_table_rows = array();
  $one_row = array();

  $sql = <<<SQL
    SELECT * FROM $table_name
SQL;

  $query = $dbs['shared']['conn']->prepare($sql);
  $query -> execute();

  $result_field_count = $query->columnCount();
  $result = $query->fetchAll();

  //Add table fields to an array
  $q = $dbs['shared']['conn']->prepare("DESCRIBE $table_name");
  $q->execute();
  $all_table_fields = $q->fetchAll(PDO::FETCH_COLUMN);

  $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name
SQL;

  $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
  $query_rows->execute();
  $result_row_count = $query_rows->fetchColumn();
   
  //Fetches all table rows and adds them to an array
    for($k = 0; $k < $result_row_count; $k++) {
      $row = $result[$k];
      for($j = 0; $j < $result_field_count; $j++) {
         $one_row[$j] = $row[$j];
       }
      $all_table_rows[$k] = $one_row;
    }

   //Send the results back to the function call:
  
   $result_array = array(
      'result_row_count' => $result_row_count,
      'result_field_count' => $result_field_count,
      'all_table_rows' => $all_table_rows,
      'all_table_fields' => $all_table_fields,
      );

   return $result_array;
}



/*
* This function queries the tmp_cause or tmp_location hierarchy history table to find the next available set id and returns it.
*
*/
function get_next_tmp_id($table, $dbs) {

  $result_array = array();
  $result_array = retrieve_table_info($table, $dbs, $result_array);

  //Get values from the array that was returned from the function call
  $row_count_retrieved = $result_array['result_row_count']; 
  $all_rows_retrieved = $result_array['all_table_rows'];

  if ($row_count_retrieved > 0) {
      //Generate the id for the next element_set row to be added
      $next_tmp_id = $all_rows_retrieved[$row_count_retrieved-1][0] + 1;  
  }

  elseif ($row_count_retrieved == 0) {
     $next_tmp_id = 1;
  } 

 return $next_tmp_id;

}


/*
* This function queries the cause or location hierarchy history table to find the set id and returns it. Not used anymore.
*
*/
// function get_set_id($table_values, $dbs) {

//   $con = mysqli_connect($dbs['test']['SERVER_NAME'],$dbs['test']['USER'],$dbs['test']['PWD'],$dbs['test']['DB_NAME']);

//    if (mysqli_connect_errno()) {
//      echo "Failed to connect to MySQL: " . mysqli_connect_error();
//    }   

//    $table_name = $table_values['history_table'];
//    $version_id = $table_values['version_id'];
//    $selection = $table_values['selection'];

//    $query = "SELECT * FROM $table_name WHERE $version_id = $selection";
   
//    $result = mysqli_query($con, $query);
   
//    $first_row = mysqli_fetch_row($result);
   
//    $set_id = $first_row[1];

//    mysqli_close($con);

//    return $set_id;
//  } 

?>


