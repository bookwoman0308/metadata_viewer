<?php
// PHP script to handle jQuery-Tabledit plug-in.
$path = $_SERVER['DOCUMENT_ROOT'] . '/sharedui/';
include_once($path . 'config.php');
include_once($path . 'util.php');

// session_start();
// $_SESSION['location_id'] = '1';

header('Content-Type: application/json');

//$input = filter_input_array(INPUT_POST);
//die(print_r(INPUT_POST));

echo $INPUT_POST;
$input_array = $_POST;
$input = array();

foreach($input_array as $key => $value) {
	if (strpos($key, 'set_id')) {
           $set_id_label = $key;
           $set_id_value = $value;
    }
	$input[$key] = strip_from_input($value);

}

$element = strtok($set_id_label, '_');


//TO DO: MUST EDIT THE BELOW CODE SO THAT YOU CAN USE ANY ELEMENT, AND ANY ELEMENT SET VERSION!!!!

if ($input['action'] == 'edit') {

    $exec_q = ("UPDATE tmp_" . $element . "_hierarchy SET parent_id='" . $input['parent_id'] . "', level='" . $input['level'] . "', is_estimate='" . $input['is_estimate'] . "' WHERE " . $element . "_id='" . $input['id'] . "' and " . $set_id_label . "='" . $set_id_value . "'");
    //$con->query("UPDATE location_hierarchy SET parent_id='" . $input['parent_id'] . "' WHERE location_id='" . $input['id'] . "' and location_set_id=39");
} else if ($input['action'] == 'delete') {
    $exec_q = ("UPDATE tmp_location SET deleted=1 WHERE id='" . $input['id'] . "' and location_set_id=20");
    
} 

  echo $exec_q;
  $query = $dbs['shared']['conn']->prepare($exec_q);
  $query -> execute();

  echo json_encode($input);

?>
