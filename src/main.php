<?php
echo("Starting server from " . getcwd() . "...\n");
define("ADDRESS", "127.0.0.1");
define("PORT", 8088);

require_once("vendor/ez_sql/ez_sql_core.php");
require_once("vendor/ez_sql/ez_sql_mysql.php");
require_once("vendor/2627_db.php");

require_once("model/root.php");
require_once("model/plan.php");
require_once("model/bell.php");
require_once("model/alarm.php");

require_once("control/static.php");
require_once("control/api.php");
require_once("control/timer.php");
require_once("control/mp3_player.php");

//Create event base
$base = new EventBase();

//Create HTTP server
// $ctx = new EventSslContext(EventSslContext::SSLv3_SERVER_METHOD, array() );
$http = new EventHttp($base);


//Create model from DB
$db = new code2627_db('root','kalapacs','schoolbell','localhost');
$root = new root();
$root->bind_to_db( $db );
$root->load_from_db();

//Create timer
$timer = new timer( $base, $root );

//Create REST api
$api = new schoolbell_api( $root, $timer );


//Bind server to ADDRESS:PORT
if (!$http->bind(ADDRESS, PORT)) {
    exit("Bind to " . ADDRESS . ":" . PORT . " failed\n");
}
else{
  echo "Listening on " . ADDRESS . ":" . PORT, PHP_EOL;
}

//Handle calls
$http->setDefaultCallback( function($req,$api) use ($api) {

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
