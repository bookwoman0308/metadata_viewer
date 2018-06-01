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
&nbsp;<h3>Shared Database UI Menu Options</h3>
<br>
<form action="formprocess.php" method="post">


<Input type = 'radio' Name ="checker" value = "view_table">
<b>View a table</b>
<br><br>
<Input type = 'radio' Name ='checker' value ="view_hierarchy">
<b>View a hierarchy</b>
<br><br>
<!-- <Input type = 'radio' Name ='checker' value ="add_table">
Add a new table   (i.e. cause_risk) - - probably should not be on this menu
<br><br>
<Input type = 'radio' Name ='checker' value ="add_field">
Add a new field   (i.e. metadata) - - probably should not be on this menu
<Br><Br> -->
<Input type = 'radio' Name ='checker' value ="add_element">
<b>Add a new element to an existing table</b> (i.e. cause, location)
<br><Br>
<Input type = 'radio' Name ='checker' value ="edit_element">
<b>Edit an existing table</b>
<Br><Br>
<Input type = 'radio' Name ='checker' value ="add_hierarchy">
<b>Add a new hierarchy</b> (i.e. cause, location)
<br><Br>
<Input type = 'radio' Name ='checker' value ="edit_hierarchy">
Edit a new hierarchy that has not yet been pushed
<Br><Br>
<input type="submit" name="submit">
</form>
</div>
  
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script> 
</body>
</html>
