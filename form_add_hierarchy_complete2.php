<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <title>Shared Database User Interface</title>

  <!-- script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js" language="Javascript"></script-->
  <script type="text/javascript" src="jquery-1.7.2.min.js"></script>
  <script type="text/javascript" src="functions.js"></script>

  <script type="text/javascript">
  //$(document).ready(function(){
  $(function(){ 
    //Add, Save, Edit and Delete functions code 
  $(".btnEdit").bind("click", Edit); 
  $(".btnDelete").bind("click", Delete); 
  $("#btnAdd").bind("click", Add); 
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

//LOOKS LIKE THIS IS AN OLD COPY THAT IS NO LONGER BEING USED!!!!

$path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
include_once($path . 'config.php');
 //include_once('util.php');

echo '<a href="index.php">Back to main page</a><Br><br>';

//Using this code:
// http://mrbool.com/how-to-add-edit-and-delete-rows-of-a-html-table-with-jquery/26721
// downloaded jquery file jquery-1.7.2.min.js and js file functions.js

//Also want to try:
// http://www.amitpatil.me/add-edit-delete-rows-dynamically-using-jquery-php
// downloaded 2 zip files: Add-edit-delete-rows-dynamically-using-jquery1 and Ajax-table-with-drop-down1

//Also want to try:
//http://www.jqueryscript.net/table/Creating-A-Live-Editable-Table-with-jQuery-Tabledit.html
//downloaded zip file: Creating-A-Live-Editable-Table-with-jQuery-Tabledit

//Could also try:
// http://www.codingcage.com/2015/06/multiple-insert-update-delete-crud.html
// uses checkboxes cannot download source code

if (isset($_POST['submit'])) {

  $input = '';
  $output = '';
  $element = '';
  $selection = '';

  foreach ($_POST as $key => $value) {
     if (strpos($key, 'set_version_id') !== FALSE) {
        $element = $key;
        $input = $key;
        $selection = $value;
      }
  }
 
  //If the user chose a hierarchy to edit, bring up that set version for editing
  if (!empty($selection)) {
    $element = strtok($element, '_');  // This finds the word before the first instance of '_' , i.e. cause or location
    // echo $element;

    //Now display the table for editing
    $output = show_hierarchy_table_for_editing($element, $selection, $output, $dbs);
    echo $output;

	} //end of if not empty

} //end of if submit



function show_hierarchy_table_for_editing($element, $selection, $output, $dbs) {

  $con = mysqli_connect($dbs['test']['SERVER_NAME'],$dbs['test']['USER'],$dbs['test']['PWD'],$dbs['test']['DB_NAME']);

   if (mysqli_connect_errno()) {
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
   }

   $output .= '<h3>'. $element .' hierarchy to edit to make new hierarchy</h3><Br><Br>';
   $version_id = $element . '_set_version_id';
   $table_name = $element . '_hierarchy_history';

   $query = "SELECT * FROM $table_name WHERE $version_id = $selection";
   //echo $query;
   $result = mysqli_query($con,$query);

    if (!$result) {
       die("Query to show fields from table failed");
    } 

   $fields_num = mysqli_num_fields($result);
   $rows_num = mysqli_num_rows($result);
   $all_fields = array();
   

   for($i = 0; $i < $fields_num; $i++) {
       $field = mysqli_fetch_field($result);
       $all_fields[$i] = $field->name;
      }

   $output .= "<button id='btnAdd'>Add new row</button> <table id='tblData'>";
   $output .= "<thead> <tr> <th>Name</th> <th>Phone</th> <th>Email</th> <th></th> </tr> </thead> <tbody> </tbody> </table>";


   //$output .= "<button id='btnAdd'>Add new row</button>";
   $output .= "<table id='tblData' border='1'>";

   // printing table headers
   $output .= "<tr>";

   for($l = 0; $l < $fields_num; $l++) {
       $output .= "<td>{$all_fields[$l]}</td>";
    }
    $output .= "<td> </td></tr>\n";

   // printing table rows
   for($j = 0; $j < $rows_num; $j++) {
      $row = mysqli_fetch_row($result);
      $output .= "<tr>";

      for($k = 0; $k < $fields_num; $k++) {
          $output .= '<td>'.$row[$k].'</td>';
       }

       $output .= "<td>hello</td></tr>\n";
    }
  
  $output .= "</table>";

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






