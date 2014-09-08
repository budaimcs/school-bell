<?php
echo("Starting server from " . getcwd() . "...\n");

chdir( dirname( dirname( __FILE__ ) ) );

require_once("config.php");
require_once("vendor/ez_sql/ez_sql_core.php");
require_once("vendor/ez_sql/ez_sql_mysql.php");
require_once("vendor/2627_db.php");

require_once("model/resources.php");
require_once("model/root.php");
require_once("model/plan.php");
require_once("model/bell.php");
require_once("model/alarm.php");

require_once("control/static.php");
require_once("control/api.php");
require_once("control/timer.php");
require_once("control/reloader.php");
require_once("control/mp3_player.php");

define("ADDRESS", $listening_ip);
define("PORT", $listening_port);


//Create event base
$base = new EventBase();

//Create HTTP server
// $ctx = new EventSslContext(EventSslContext::SSLv3_SERVER_METHOD, array() );
$http = new EventHttp($base);


//Create model from DB
$db = new code2627_db($db_user,$db_pass,$db_name,$db_host, 'utf8');
$resources = new resources( $db, array(
	'plan',
	'bell',
	'alarm'
));

$root = new root();
$root->bind_to_db( $db );
$root->fetch_collections( $resources );

//Create timer
$timer = new timer( $base, $root );

//Create reloader
$reloader = new reloader( $base, $timer );

//Create REST api
$api = new schoolbell_api( $root, $timer, $resources );


//Bind server to ADDRESS:PORT
if (!$http->bind(ADDRESS, PORT)) {
    exit("Bind to " . ADDRESS . ":" . PORT . " failed\n");
}
else{
  echo "Listening on " . ADDRESS . ":" . PORT, PHP_EOL;
}

//Handle calls
$http->setDefaultCallback( function($req,$api) use ($api) {

	$auth = $req->findHeader( "Authorization", EventHttpRequest::INPUT_HEADER );
	if( $auth == "" )
	{
		echo( "Authentication required!\n" );
		$req->addHeader( "WWW-Authenticate", "Basic realm=\"Iskola csengő jelszó\"", EventHttpRequest::OUTPUT_HEADER );
		$req->addHeader( "Content-Type", "text/html; charset=utf-8", EventHttpRequest::OUTPUT_HEADER );
		$req->sendReply(401, "Not authenticated");
		echo( "401 Not Authorized\n");
		return false;
	}
	echo( "Authenticating...\n" );
	$parts = explode(" ", $auth);
// 	echo( $auth . "\n" );
// 	echo( base64_decode( $parts[1] ) . "\n" );
	$u_p = explode( ":", base64_decode( $parts[1] ) );
	if( $u_p[0] != USERNAME or $u_p[1] != PASSWORD )
	{
		echo( "Bad username or password!\n" );
		$req->addHeader( "WWW-Authenticate", "Basic realm=\"Iskola csengő jelszó\"", EventHttpRequest::OUTPUT_HEADER );
		$req->addHeader( "Content-Type", "text/html; charset=utf-8", EventHttpRequest::OUTPUT_HEADER );
		$req->sendReply(401, "Not authenticated");
		echo( "401 Not Authorized\n");
		return false;
		
	}	
	echo("Password OK\n");
		
	
// 	WWW-Authenticate: Basic realm="insert realm"
		

	if( preg_match("%^/api/%", $req->getUri(), $parameters ) )
	{
		$api->handle_api_call( $req );
	}
	else if( preg_match("%^/static/(.*)%", $req->getUri(), $parameters ) )
	{
		serve_file( $parameters[1], $req );
	}
	else
		serve_file( "index.html", $req );
      
});

//Start event handler
$base->dispatch();
?>
