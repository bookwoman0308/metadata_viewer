<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <title>Shared Database User Interface</title>

  <script src="jquery1.11.2.min.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
  //$(function(){ 
  $('#tblData').Tabledit({
    url: 'handle_edit_hierarchy.php',
    columns: {
        identifier: [1, 'id'],
        editable: [[2, 'parent_id'], [3, 'level'], [4, 'is_estimate']]
    },
     onDraw: function() {
        //console.log('onDraw()');
    },
    onSuccess: function(data, textStatus, jqXHR) {
        console.log('onSuccess(data, textStatus, jqXHR)');
    },
    onFail: function(jqXHR, textStatus, errorThrown) {
        console.log('onFail(jqXHR, textStatus, errorThrown)');
        console.log("textStatus: " + textStatus);
        console.log("errorThrown: " + errorThrown);
    },
    onAlways: function() {
        //console.log('onAlways()');
    },
    onAjax: function(action, serialize) {
        //console.log('onAjax(action, serialize)');
        //console.log("action: "+action);
        console.log("serialize: " + serialize);
    }
});
});
 </script>


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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<a href="index.php">Back to main page</a><Br><br>';

//JQuery-Tabledit from this website: www.jqueryscript.net/table/Creating-A-Live-Editable-Table-with-jQuery-Tabledit.html

//print_r($_POST);

if (isset($_POST['submit']) && ($_POST['submit'] == 'continue to edit')) {

  echo 'This case has not been built out yet.';

}

elseif (isset($_POST['submit']) && ($_POST['direction'] == 'existing')) {
  //print_r($_POST);
  $output = '';  
  $element = $_POST['element'];
  $selection = $_POST[$element . '_set_version_id'];
 
  //If the user chose a hierarchy to edit, bring up that set version for editing
  if (!empty($selection)) {

    //Build an array of important values so don't have to carry so many from function to function
    $table_values = array();
    $table_values = array(
        'element' => $element,
        'selection' => $selection,
        'element_set_id_label' => $element . "_set_id",
        'element_id_label' => $element . "_id",
        'version_id_label' => $element . "_set_version_id",
        'history_table' => $element . "_hierarchy_history",
        'hierarchy_table' => $element . "_hierarchy",
        'set_id_value' => $_POST[$element . '_set_id'],
        );

    //Load the values from the hierarchy table into the corresponding tmp_hierarchy table for whichever selected element
    $new_set_id = copy_from_history_to_tmp($table_values, $dbs);

    $table_values['set_id_value'] = $new_set_id;
       
    //Now display the table for editing
    $output = show_hierarchy_table_for_editing($table_values, $output, $dbs);
    echo $output;

	} //end of if not empty

  else { 
    echo 'problem - did not get selection data'; 
  }

} // end if



/*
* This function copies the hierarchy from the cause or location hierarchy history table into a tmp hierarchy table with a new temp set id.
*
*/
function copy_from_history_to_tmp($table_values, $dbs) {

   $element = $table_values['element'];
   
  //Populate an array with some of the field values from the user's selected set version table
   $all_field_rows = array();
   $all_field_rows = put_requested_set_version_into_array($all_field_rows, $table_values, $dbs); 
   //print_r($all_field_rows);

  //Find next available set_id from the tmp_hierarchy table
  $table = 'tmp_' . $element . '_hierarchy';
  $next_tmp_id = get_next_tmp_id($table, $dbs);

  //Are there null values for the cause_outline field??
   $bool_val = 0;
   $bool_val = has_empty_cause_outline($bool_val, $all_field_rows);
   //echo $bool_val;
   
   //If the table has even one row with a NULL cause_outline value, it has to be handled differently in the section below
   if ($bool_val) {

      $outline = fill_array_with_without_cause_outline($all_field_rows);
      $yes_outline = $outline['yes_outline'];
      $no_outline = $outline['no_outline'];
      //print_r($no_outline);

      //First generate the segment of the insert command for the array that has cause_outline values. 
      insert_yes_outline($table_values, $next_tmp_id, $yes_outline, $dbs);

      //Next for the array WITHOUT cause_outline values.
      insert_no_outline($table_values, $next_tmp_id, $no_outline, $dbs);
   }

   else {   //No rows in the table have NULL values for the cause_outline field

      insert_all_outline($table_values, $next_tmp_id, $all_field_rows, $dbs);
   } 

   //Send back the set id number for the tmp_element_hierarchy table. Will have to find a way to preserve the original set id. Also USER ID!
   return $next_tmp_id; 
 } 


/*
* This function determines whether or not the cause_set_version hierarchy contain NULL cause_outline values.
*
*/
function has_empty_cause_outline($bool_val, $all_field_rows) {

    $key = 'cause_outline';

    foreach($all_field_rows as $arow) { 
          if(array_key_exists($key, $arow) && $arow[$key] == 'NULL') {
              $bool_val = 1;
              return $bool_val;
              break;
          }
     }

    return $bool_val;
}


/*
* This function creates an array of values with and without the cause_outline key.
*
*/
function fill_array_with_without_cause_outline($all_field_rows) {

    $key = 'cause_outline';
    $all_field_rows_no_cause_outline = array();
    $all_field_rows_with_cause_outline = array();
    $matchup_cause_outline = array();

    $row_count = count($all_field_rows);

    //First populate an array of rows that have a cause_outline value
    $counter = 0;
    for($i=0; $i<$row_count; $i++) {
          if(array_key_exists($key, $all_field_rows[$i]) && $all_field_rows[$i][$key] != 'NULL') { //if cause_outline value is not NULL
              $all_field_rows_with_cause_outline[$counter] = $all_field_rows[$i];    //this array will have all fields and all values
              $matchup_cause_outline[$i] = $all_field_rows[$i]; //this array is purely for matching purposes; keeps the same key as full array
              $counter++;
          }       
     } 

    //Then populate an array of rows that do not have cause_outline
    $counter = 0;
    for($i=0; $i<$row_count; $i++) {
         if (array_key_exists($i, $matchup_cause_outline)) {  //check to see if the row is in the array with cause outline
         }
         else {
          $chunk = array_slice($all_field_rows[$i], 0, 5);      //if not, take all the values except the last one, so fields 0 through 5
          $all_field_rows_no_cause_outline[$counter] = $chunk;  //use the $all_field_rows because that has the field names
          $counter++;
         }
     }
 
 //echo count($all_field_rows_no_cause_outline) . ' ' .  count($all_field_rows_with_cause_outline);
 //print_r($all_field_rows_no_cause_outline);
 //print_r($all_field_rows_with_cause_outline);

// row looks like this:
// Array ( [0] => Array ( [cause_id] => 294 [parent_id] => 294 [level] => 0 [is_estimate] => 0 [sort_order] => 1 [cause_outline] => Total ) 
//   [1] => Array ( [cause_id] => 295 [parent_id] => 294 [level] => 1 [is_estimate] => 0 [sort_order] => 2 [cause_outline] => A ) 

   $outline = array();
   $outline['no_outline'] = $all_field_rows_no_cause_outline;
   $outline['yes_outline'] = $all_field_rows_with_cause_outline;

   return $outline;
}


/*
* This function populates an array with the values from a user's requested set version. Useful for moving part of a table's contents to another table.
*
*/
function put_requested_set_version_into_array($all_field_rows, $table_values, $dbs) {

   $table_name = $table_values['history_table'];
   $element = $table_values['element'];
   $element_set_id_label = $table_values['element_set_id_label'];
   $element_id_label = $table_values['element_id_label'];
   $version_id_label = $table_values['version_id_label'];
   $selection = $table_values['selection'];
    

   $get_query = "SELECT $element_id_label, parent_id, level, is_estimate, sort_order";

   if($element == 'location' || $element == 'rei') { 
       $all_fields = array($element_id_label, 'parent_id', 'level', 'is_estimate', 'sort_order');
   }
   elseif ($element == 'cause') {
       $get_query .= ", cause_outline";
       array_push($all_fields, 'cause_outline');
   }
   else { //no other action required
   }

   $get_query .= " FROM $table_name WHERE $version_id_label = $selection";

   //echo $get_query;

   try {
    $move_query = $dbs['shared']['conn']->prepare($get_query);
    $move_query -> execute();
   } 
   catch(PDOException $e) {
    echo $e->getMessage();
   }  

  $fields_num = $move_query->columnCount();
  $result = $move_query->fetchAll(PDO::FETCH_ASSOC);

  //print_r($result);

  $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name WHERE $version_id_label = $selection
SQL;

  $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
  $query_rows->execute();
  $rows_num = $query_rows->fetchColumn();

  //Add table values to an array
  $print_array = array();
  foreach($result as $key) {
      $print_array[] = $key;
  }

 //print_r($print_array);
 $all_field_rows = $print_array;
 //print_r($all_field_rows);
    //all fields_rows:  
   // Array ( [0] => Array ( [location_id] => 13 [parent_id] => 44536 [level] => 2 [is_estimate] => 1 [sort_order] => 28 )


  return $all_field_rows;
}


/*
* This function displays the tmp_hierarchy for editing. Uses jquery and ajax calls; processed in handle_edit_hierarchy.php file.
*
*/
function show_hierarchy_table_for_editing($table_values, $output, $dbs) {
  
   $table_name = "tmp_" . $table_values['hierarchy_table'];
   $element = $table_values['element'];
   $element_set_id_label = $table_values['element_set_id_label'];
   $element_id_label = $table_values['element_id_label'];
   $set_id_value = $table_values['set_id_value'];

   //Get the number of rows  
  $sql_row_count = <<<SQL
    SELECT count(*) FROM $table_name where $element_set_id_label = $set_id_value
SQL;

   $query_rows = $dbs['shared']['conn']->prepare($sql_row_count);
   $query_rows->execute();
   $rows_num = $query_rows->fetchColumn();
   

   //Display the tmp hierarchy table for editing:

   $sql = <<<SQL
    SELECT * FROM $table_name where $element_set_id_label = $set_id_value ORDER BY sort_order
SQL;
 //echo $sql;
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

//print_r($all_fields);
 // Array ( [0] => location_set_id [1] => location_id [2] => parent_id [3] => level [4] => is_estimate [5] => sort_order ) 

  //Put table values into an array
  $print_array = array();
  foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $print_array[] = $row;
  }


  //print_r($print_array);
  //print_r($print_array[2][1]);
  //echo $print_array[0][$element . 'id'];


   //start building the table
   $output .= "<div class='table-responsive'>";
   $output .= "<table class='table table-striped table-bordered' id='tblData'>";

   // print table headers
   $output .= "<thead><tr>";
   for($l = 0; $l < $fields_num; $l++) {
       $output .= "<th>{$all_fields[$l]}</th>";
    }
    $output .= "<th class='tabledit-toolbar-column'></th></tr></thead>\n";


   // print table rows - table body
   $output .= "<tbody>";
   $output .= "<input type='hidden' name='element' value=$element disabled>";
   for($j = 0; $j < $rows_num; $j++) {

      $output .= "<tr id='" . $print_array[$j][$element . '_id'] . "'>";   // this sets tr id to the location_id

     for($k = 0; $k < $fields_num; $k++) {  
          if ($k == 1) {
             $output .= "<td>";
             $output .= "<span class='tabledit-span tabledit-identifier'>";
             $element_id_label = $print_array[$j][$element . '_id'];
             $output .= $element_id_label . "</span>";
             $output .= "<input class='tabledit-input tabledit-identifier' type='hidden' name='id' value=$element_id_label disabled>";
             $output .= "</td>";             
          }
          else {
             $output .= "<td class='tabledit-view-mode'>";
             $output .= "<span class='tabledit-span'>";
             $output .= $print_array[$j][$all_fields[$k]] . "</span>";
             $row_value = $print_array[$j][$all_fields[$k]];
             $output .=   "<input class='tabledit-input form-control input-sm' type='text' name=$all_fields[$k] value=$row_value style='display: none;' disabled>";           
             $output .= "</td>";            
           }
      }
    
       $output .= "</tr>\n";
    }

  
  $output .= "</tbody></table>";
  $output .= "</div>";

 return $output;

}

/*
* This function generates a large portion of the INSERT command for a table that has no NULL values for the cause_outline field.
*
*/
function insert_all_outline($table_values, $next_tmp_id, $all_field_rows, $dbs) {

  //Retrieve stored values from array
   $to_table_name = "tmp_" . $table_values['hierarchy_table'];
   $element = $table_values['element'];
   $element_set_id_label = $table_values['element_set_id_label'];
   $element_id_label = $table_values['element_id_label'];

   $insert_query = "INSERT INTO $to_table_name ($element_set_id_label, $element_id_label, parent_id, level, is_estimate, sort_order";
   
   if ($element == 'cause') {
       $insert_query .= ", cause_outline";
    }

   $insert_query .= ") VALUES ";

//print_r($all_field_rows);

  foreach($all_field_rows as $arow) {     
      $insert_query .= "($next_tmp_id, ";
      foreach($arow as $key => $value) {   
     
              if ((strpos($key, '_outline') !== FALSE) || (strpos($key, 'path_to_top') !== FALSE)) {  //for string values, need to add quotes
                   if((($element == 'cause') && ($key == 'cause_outline')) || (($element != 'cause') && ($key == 'sort_order'))) { //if it is the last row 
                      $insert_query .= '"'. $value . '"';
                   }
                  else {
                     $insert_query .= '"'. $value . '", '; 
                   }  
               } //end if for string values

               else { //it is a numeric value
                   if(($key == 'cause_outline') || (($element != 'cause') && ($key == 'sort_order'))) { //if it is the last row  
                      $insert_query .= "$value";
                    }
                   else {
                      $insert_query .= "$value, ";  //for numeric values no quotes needed
                   }    
                } //end else for numeric values
              
      } //end inner for loop

      $insert_query .= "),";
            
   } //end outer for loop

  //Remove trailing comma if there is one
  $insert_query = rtrim($insert_query);
  $insert_query = rtrim($insert_query, ', ');
  $insert_query .= ";";

  //echo $insert_query;

  $query = $dbs['shared']['conn']->prepare($insert_query);
  $query -> execute();

}


/*
* This function generates a large portion of the INSERT command for a SECTION of a table that has no NULL values for the cause_outline field.
* The element by default will be cause.
*
*/ 
function insert_yes_outline($table_values, $next_tmp_id, $yes_outline, $dbs) {

  //Retrieve stored values from array
   $to_table_name = "tmp_" . $table_values['hierarchy_table'];
   $element = $table_values['element'];
   $element_set_id_label = $table_values['element_set_id_label'];
   $element_id_label = $table_values['element_id_label'];

   $insert_query = "INSERT INTO $to_table_name ($element_set_id_label, $element_id_label, parent_id, level, is_estimate, sort_order, cause_outline";
   $insert_query .= ") VALUES ";

  foreach($yes_outline as $arow) {     
      $insert_query .= "($next_tmp_id, ";
      foreach($arow as $key => $value) {   
     
              if ((strpos($key, '_outline') !== FALSE) || (strpos($key, 'path_to_top') !== FALSE)) {  //for string values, need to add quotes
                   if($key == 'cause_outline') { //if it is the last row 
                      $insert_query .= '"'. $value . '"';
                   }
                  else {
                     $insert_query .= '"'. $value . '", '; 
                   }  
               } //end if for string values

               else { //it is a numeric value
                   if($key == 'cause_outline') { //if it is the last row   
                      $insert_query .= "$value";
                    }
                   else {
                      $insert_query .= "$value, ";  //for numeric values no quotes needed
                   }    
                } //end else for numeric values
              
      } //end inner for loop

      $insert_query .= "),";
            
   } //end outer for loop

  //Remove trailing comma if there is one
  $insert_query = rtrim($insert_query);
  $insert_query = rtrim($insert_query, ',');
  $insert_query .= ";";

  //echo $insert_query . '<br>';

  $query = $dbs['shared']['conn']->prepare($insert_query);
  $query -> execute();

}


/*
* This function generates a large portion of the INSERT command for a SECTION of a table that has all NULL values for the cause_outline field.
*    The input is an array of values except the cause_outline field and associated values.
*    The element by default will be cause.
*
*/
function insert_no_outline($table_values, $next_tmp_id, $no_outline, $dbs) {

  //Retrieve stored values from array
   $to_table_name = "tmp_" . $table_values['hierarchy_table'];
   $element = $table_values['element'];
   $element_set_id_label = $table_values['element_set_id_label'];
   $element_id_label = $table_values['element_id_label'];

   $insert_query = "INSERT INTO $to_table_name ($element_set_id_label, $element_id_label, parent_id, level, is_estimate, sort_order";

   $insert_query .= ") VALUES ";

   foreach($no_outline as $arow) {     
      $insert_query .= "($next_tmp_id, ";
      foreach($arow as $key => $value) {   
     
              if (strpos($key, 'path_to_top') !== FALSE) {  //for string values, need to add quotes
                   if($key == 'sort_order') { //if it is the last row 
                      $insert_query .= '"'. $value . '"';
                   }
                  else {
                     $insert_query .= '"'. $value . '", '; 
                   }  
               } //end if for string values

               else { //it is a numeric value
                   if($key == 'sort_order') { //if it is the last row  
                      $insert_query .= "$value";
                    }
                   else {
                      $insert_query .= "$value, ";  //for numeric values no quotes needed
                   }    
                } //end else for numeric values
              
      } //end inner for loop

      $insert_query .= "),";
            
   } //end outer for loop

  //Remove trailing comma if there is one
  $insert_query = rtrim($insert_query);
  $insert_query = rtrim($insert_query, ',');
  $insert_query .= ";";

  //echo $insert_query;

  $query = $dbs['shared']['conn']->prepare($insert_query);
  $query -> execute();

}


?>


  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
  

  <script src="jquery.tabledit.js"></script>
  </div>
</body>
</html>






