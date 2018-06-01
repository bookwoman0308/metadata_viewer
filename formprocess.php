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

///scripts/select2.min.js
//js/ihme_data_sources_select2.js

if (isset($_POST['submit'])) {

	if (isset($_POST['checker'])) {

 $selection = $_POST['checker'];
 $output = '';
 echo '<br><br>';

switch($selection) {
    case 'view_table':
    
     $output = '<form action="form_view_table.php" method="post">';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_cause_table"><b>Cause</b> (includes ncodes and DEX causes)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_location_table"><b>Location</b> (includes regional divisions and custom locations)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_rei_table">Risk Factors (includes rei, etiologies, and impairments)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_rei_table">Age Groups<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_rei_table">Measures etc. etc.<br><Br>';
     $output .= '<input type="submit" name="submit">';

    echo $output;
    break;

    case 'view_hierarchy':

      $output = '<form action="form_view_hierarchy.php" method="post">';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_cause_hierarchy"><b>Cause</b> (includes ncodes and DEX causes)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_location_hierarchy"><b>Location</b> (includes regional divisions and custom locations)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_rei_hierarchy">Risk Factors (includes rei, etiologies, and impairments)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_rei_table">Age Groups set list<br><Br>';
     $output .= '<input type="submit" name="submit">';
      echo $output;

       break;

  case 'add_element': 

     $output = '<form action="form_add_element.php" method="post">';
     $output .= '<Input type = "radio" Name ="add_element_type" value = "add_cause_element"><b>Add a cause to the cause table</b> (includes ncodes and DEX causes)<br><Br>';
     $output .= '<Input type = "radio" Name ="add_element_type" value = "add_location_element"><b>Add a location to the location table</b> (includes regional divisions and custom locations)<br><Br>';
     $output .= '<Input type = "radio" Name ="add_element_type" value = "add_rei_element">Add a risk to the risk factors table (includes rei, etiologies, and impairments)<br><Br>';
     $output .= '<Input type = "radio" Name ="add_element_type" value = "add_age_group">Add to the age_group table<br><Br>';
     $output .= '<Input type = "radio" Name ="add_element_type" value = "add_measure">Add to the measure table<br><Br>';
     $output .= '<input type="submit" name="submit">';
       echo $output;
       break;
       
  case 'add_hierarchy': 
     $output = '<form action="form_add_hierarchy.php" method="post">';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_cause_hierarchy"><b>Cause</b> (includes ncodes and DEX causes)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_location_hierarchy"><b>Location</b> (includes regional divisions and custom locations)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_htype" value = "view_rei_hierarchy">Risk Factors (includes rei, etiologies, and impairments)<br><Br>';
     $output .= '<Input type = "radio" Name ="view_type" value = "view_rei_table">Age Groups set list<br><Br>';
     $output .= '<input type="submit" name="submit">';
      echo $output;
       break;

  case 'edit_element':
     $output = '<form action="form_edit_element.php" method="post">';
     $output .= '<Input type = "radio" Name ="edit_element_type" value = "cause"><b>Cause</b> (includes ncodes and DEX causes)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_element_type" value = "location"><b>Location</b> (includes regional divisions and custom locations)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_element_type" value = "rei">Risk Factors (includes rei, etiologies, and impairments)<br><Br>';
     $output .= '<Input type = "radio" Name ="edit_element_type" value = "age_group">Age Groups<br><Br>';
     $output .= '<input type="submit" name="submit">';
      echo $output;
       break; 

  case 'edit_hierarchy': 
  //function to display table
       break;
   } //end switch statement

  }

}

?>

<br><br><br><br>
<a href="index.php">Back to main page</a>

  
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
</div>
</body>
</html>
