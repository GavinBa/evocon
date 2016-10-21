<?php 

require_once "state.php";
require_once "lib/db.php";
require_once "lib/util.php";
require_once "code/market/res.php";
require_once "code/heroes/heroes.php";
require_once "code/cities/city.php";
require_once "code/buildings/buildings.php";
require_once "code/db/DbCity.php";
require_once "code/request/ClientRequest.php";
require_once "code/script/ClientScript.php";

/* Get a connection to the database. */
$dbc = db_connectDB();
if (is_null($dbc)) {
	return;
}


/* Get the city parameter */
$p1 = util_setParam ("p1", 0);
if (! is_string($p1)) {
	print "no p1";
	return;
}

/* Get the server parameter */
$server = util_setParam("server", 0);
if ($server == 0) {
	print "No server";
	return;
}

/* Get the player parameter */
$userName = util_setParam("player", "none");
if ($userName == "none") {
	print "No user name";
	return;
}

$cTime = util_setParam("time",0);


/* Decode the json parameter */
$json = json_decode($p1);
if ($json == NULL) {
	return;
}

/* Get the state parameter */
//$state = util_setParam("state", 0);

/* Create city object */
$c = new City($json);

/* Create a client request object. */
$cReq = new ClientRequest($dbc, $server, $userName, $c);
$cReq->setCtime($cTime);

/* Add entry if needed */
$dbcity = new DbCity($dbc, $server, $userName, $c);
$dbcity->create();
$state = $dbcity->getState();

/* Create a client script file */
$cScript = new ClientScript($cReq);
/* Clean up old scripts for current city */
$cScript->purge();
$cScript->startFile();

/* Update the state */
$s = new StateController($c, $cScript, $cReq);
/* Advance to the next state */
$ns = $s->nextState($state);
$dbcity->setState($ns);


print "http://192.168.1.77:8000/" . $cScript->getFullPath();

$cScript->endFile();

db_disconnectDB($dbc);
?>
