<? 
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);

header("Access-Control-Allow-Origin: *");

$results = [];
// Connect to the database (create it if it doesn't exist)
$db = new SQLite3('data.db');

// Create a table
//$query = "CREATE TABLE polygons (id INTEGER PRIMARY KEY, polygons TEXT, session VARCHAR(255) UNIQUE)";
//$db->exec($query);

$content = trim(file_get_contents("php://input"));
$decoded = json_decode($content, true);

	 	 
if(isset($decoded['polygons'])){
	// Save data to database
	$milliseconds = floor(microtime(true) * 1000);
	$session = $decoded['session'];
	$polygons = json_encode($decoded['polygons']);
	$query = "INSERT INTO polygons (polygons, session) VALUES ('$polygons', '$session')";
	
	if(isset($session) && $session!=""){ //UPDATE
	
		//on duplicate session update
		$query = "
			UPDATE polygons
			SET polygons = '$polygons'
			WHERE session = '$session';
			
			INSERT INTO polygons (polygons, session)
			SELECT '$polygons', '$session'
			WHERE (Select Changes() = 0);
		";

  
		// $query = "UPDATE polygons
					// SET polygons = '$polygons'
					// WHERE
    					// session = '$session' ";
	}
	$db->exec($query);
	
	$results=[
		//'session'=>$db->lastInsertRowID(),
		'session'=>$session,
		'query'=>$query
	];
}

// Load polygons from database by ID (sessionID)
if(isset($_GET['session'])){
	$session = $_GET['session'];
	$query = "SELECT * FROM polygons where session = '$session'";
	$result = $db->query($query);
	while ($row = $result->fetchArray()) {
	    $results = array(
	    	'geometry'=>json_decode($row['polygons']), 
	    	'session'=> $row['session']
	    );
	}
}

echo json_encode($results);

?>