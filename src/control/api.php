<?php

class schoolbell_api
{
	protected $timer;
	public function __construct( $root, $timer, $resources )
	{
		$this->root = $root;
		$this->timer = $timer;
		$this->resources = $resources;
	}
	public function handle_api_call( $req )
	{
		$root = $this->root;
		$resources = $this->resources;
		
		$uri = preg_replace( "%^/api%", "", $req->getURI() );
		echo( $uri ."\n");
		
		$req->addHeader( "Content-Type", "application/json; charset=utf-8", EventHttpRequest::OUTPUT_HEADER );
		
		$method = $req->getCommand();
		$body = $req->getInputBuffer()->read(8000);
		$buf = new EventBuffer;
		$param = array();
		
		//Check URL, map endpoints
		if( preg_match( "%^/plan/?$%", $uri, $param ) )
		{
			$endpoint = 'plans';
		}
		else if( preg_match( "%^/plan/([^/]*)/?$%", $uri, $param ) )
		{
			$endpoint = 'plan';
		}
		else if( preg_match( "%^/bell/?$%", $uri, $param ) )
		{
			$endpoint = 'bells';
		}
		else if( preg_match( "%^/bell/([^/]*)/?$%", $uri, $param ) )
		{
			$endpoint = 'bell';
		}
		else if( preg_match( "%^/alarm/?$%", $uri, $param ) )
		{
			$endpoint = 'alarms';
		}
		else if( preg_match( "%^/alarm/([^/]*)/?$%", $uri, $param ) )
		{
			$endpoint = 'alarm';
		}
		else if( preg_match( "%^/print_model?$%", $uri, $param ) )
		{
			$req->addHeader( "Content-Type", "text/html; charset=utf-8", EventHttpRequest::OUTPUT_HEADER );
			$buf->add( "<html><head><meta charset=\"utf-8\"></head><body><pre>\n" . $root->print_this( 0, true ) . "\n</pre></body></html>");
			$req->sendReply(200, "OK", $buf);
			echo( " 200 OK\n");
			return true;
		}
		else
		{
			$req->sendError(404);
			echo( "Invalid API call: {$uri}\n");
			return false;
		}
		echo( "Endpoint:" . $endpoint ."\n");
		

		if( $body != "" )
		{
			$data = json_decode( $body );
			if( !is_object( $data ) )
			{
				$req->sendError(500);
				echo( "Bad request: " . print_r( $data , true) . "\n");
				return false;
			}
			print_r( $data );
		}
		else
		{
			$data = new stdClass;
		}
		echo("Parameters are valid\n");
		
		switch( $method )
		{
			case EventHttpRequest::CMD_GET :
				echo("GET");
				switch( $endpoint )
				{
					case 'plans' :
					case 'bells' :
					case 'alarms' :
						$json = $resources->get_list( substr($endpoint,0,-1), true, true );
						break;
					case 'plan' :
					case 'bell' :
					case 'alarm' :
						$json = $resources->get_item( $endpoint, $param[1], true );
						break;
				}
				$buf->add( $json . "\n");
				$req->sendReply(200, "OK", $buf);
				echo( " 200 OK\n");
				break;
			case EventHttpRequest::CMD_POST :
				echo("POST");
				switch( $endpoint )
				{
					case 'plans' :
					case 'bells' :
					case 'alarms' :
						$model = substr($endpoint,0,-1);
						$e = new $model( $data );
						$e->bind_to_db( $root->get_db() );
						$e->save_to_db();
						$resources->put_item( $model, $e );
						$e->register_to_parents( $resources );
						$e->fetch_collections( $resources );
						if( $endpoint == 'plans' )
						{
							$root->add_to_collection( 'plan', $e );
						}
						break;
					case 'plan' :
					case 'bell' :
					case 'alarm' :
						break;

				}
				
// 				$root->print_this();
				$this->timer->create_timers();
				if( is_object( $e ) )
					$buf->add( $e->toJSON() . "\n");
				$req->sendReply(200, "OK", $buf);
				echo( " 200 OK\n");
				break;
			case EventHttpRequest::CMD_PUT :
				echo("PUT");
				switch( $endpoint )
				{
					case 'plans' :
						break;
					case 'bells' :
						break;
					case 'alarms' :
						break;
					case 'plan' :
					case 'bell' :
					case 'alarm' :
						$e = $resources->get_item( $endpoint, $param[1] );
						if( is_null( $e ) )
						{
							$req->sendError(404);
							echo( "Not found: {$endpoint}/{$param[1]}\n");
							return false;
						}
						else
						{
							$e->load_from_object( $data );
							$e->save_to_db();
							$e->register_to_parents( $resources );
// 							$e->fetch_collections( $resources );
							$this->timer->create_timers();
							$buf->add( "{}" . "\n");
							$req->sendReply(200, "OK", $buf);
							echo( " 200 OK\n");
						}
						break;
					
				}
				break;
			case EventHttpRequest::CMD_DELETE :
				echo("DELETE");
				switch( $endpoint )
				{
					case 'plans' :
						break;
					case 'bells' :
						break;
					case 'alarms' :
						break;
					case 'plan' :
					case 'bell' :
					case 'alarm' :
						if( !$resources->has_item( $endpoint, $param[1] ) )
						{
							$req->sendError(404);
							echo( "Not found: {$endpoint}/{$param[1]}\n");
							return false;
						}
						else
						{
							if( $endpoint == 'plan' )
							{
								$root->remove_from_collection( 'plan', $param[1] );
							}
							$resources->delete_item( $endpoint, $param[1] );
							$this->timer->create_timers();
							$buf->add( "{}" . "\n");
							$req->sendReply(200, "OK", $buf);
							echo( " 200 OK\n");
						}
						break;
						
				}
				break;
		}
		return true;
	}
}
?>