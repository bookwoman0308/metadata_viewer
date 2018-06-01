<?php

//This file stores the database information

// The old non-PDO way of configuration
// $dbs = array(
//     "test" => array(
// 		"SERVER_NAME" => 'localhost',
//         "DB_NAME" => "shared",
//         "USER" => "elissa_shared",
//         "PWD" => "secret"
// 	)
// );


$path = $_SERVER['DOCUMENT_ROOT'];

// if (strpos($path, 'sharedd')) {

// 	$dbs['shared'] = array(
// 		'db' => 'sharedtest',
// 		'host' => 'fullhostname',
// 		'user' => 'elissat',
// 		'pass' => 'secret',
// 	);


// foreach($dbs as &$db){
// 		$db['conn'] = new PDO(
// 			'mysql:host='.$db['host'].';dbname='.$db['db'].';charset=utf8', 
// 			$db['user'], 
// 			$db['pass'], 
// 			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
// 		);
// 		$db['conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 	}

// }

if (strpos($path, 'sharedd')) {  //this is for using docker

$dbs['shared'] = array(
		'db' => 'shared',
		'host' => 'elissat-dev.ihme.washington.edu',
		'user' => 'elissa_shared',
		'pass' => 'secret',
	);


foreach($dbs as &$db){
		$db['conn'] = new PDO(
			'mysql:host='.$db['host'].';port=3336;dbname='.$db['db'].';charset=utf8', 
			$db['user'], 
			$db['pass'], 
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$db['conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

}


else  {   //this is for using MAMP

$dbs['shared'] = array(
		'db' => 'shared',
		'host' => 'localhost',
		'user' => 'elissa_shared',
		'pass' => 'secret',
	);


foreach($dbs as &$db){
		$db['conn'] = new PDO(
			'mysql:host='.$db['host'].';dbname='.$db['db'].';charset=utf8', 
			$db['user'], 
			$db['pass'], 
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$db['conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

}



?>


