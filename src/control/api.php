<?php

class schoolbell_api
{
	protected $timer;
	public function __construct( $root, $timer )
	{
		$this->root = $root;
		$this->timer = $timer;
	}
	public function handle_api_call( $req )
	{
		global $root;
		
		$uri = preg_replace( "%^/api%", "", $req->getURI() );
		echo( $uri ."\n");
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
		else if( preg_match( "%^/plan/([^/]*)/bell/?$%", $uri, $param ) )
		{
			$endpoint = 'bells';
		}
		else if( preg_match( "%^/plan/([^/]*)/bell/([^/]*)/?$%", $uri, $param ) )
		{
			$endpoint = 'bell';
		}
		else if( preg_match( "%^/plan/([^/]*)/bell/([^/]*)/alarm/?$%", $uri, $param ) )
		{
			$endpoint = 'alarms';
		}
		else if( preg_match( "%^/plan/([^/]*)/bell/([^/]*)/alarm/([^/]*)/?$%", $uri, $param ) )
		{
			$endpoint = 'alarm';
		}
		else if( preg_match( "%^/print_model?$%", $uri, $param ) )
		{
			$buf->add( "<html><body><pre>\n" . $root->print_this( 0, true ) . "\n</pre></body></html>");
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
		
		
		if( isset( $param[1] ) )
		{
			$plan_id = $param[1];
			if( $root->is_collection_element_exists( 'plan', $plan_id ) === false )
			{
				$req->sendError(404);
				echo( "Plan not found: {$plan_id}\n");
				return false;
			}
		}
		if( isset( $param[2] ) )
		{
			 $bell_id = $param[2];
			if( $root->get_collection_element( 'plan', $plan_id )->is_collection_element_exists( 'bell', $bell_id ) === false )
			{
				$req->sendError(404);
				echo( "Bell not found: {$plan_id}\n");
				return false;
			}
		}
		if( isset( $param[3] ) )
		{
			$alarm_id = $param[3];
			if( $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->is_collection_element_exists( 'alarm', $alarm_id ) === false )
			{
				$req->sendError(404);
				echo( "Alarm not found: {$plan_id}\n");
				return false;
			}
		}
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
				echo("GET\n");
				switch( $endpoint )
				{
					case 'plans' :
						$json = $root->get_collection( 'plan', true );
						break;
					case 'bells' :
						$json = $root->get_collection_element( 'plan', $plan_id )->get_collection( 'bell', true );
						break;
					case 'alarms' :
						$json = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->get_collection( 'alarm', true );
						break;
					case 'plan' :
						$json = $root->get_collection_element( 'plan', $plan_id )->toJSON();
						break;
					case 'bell' :
						$json = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->toJSON();
						break;
					case 'alarm' :
						$json = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->get_collection_element( 'alarm', $alarm_id )->toJSON();
						break;
				}
				$buf->add( $json . "\n");
				$req->sendReply(200, "OK", $buf);
				echo( " 200 OK\n");
				break;
			case EventHttpRequest::CMD_POST :
				echo("POST\n");
				switch( $endpoint )
				{
					case 'plans' :
						$e = new plan( $data );
						$root->add_to_collection( 'plan', $e );
						$e->save_to_db();
						break;
					case 'bells' :
						$e = new bell( $data );
						$root->get_collection_element( 'plan', $plan_id )->add_to_collection( 'bell', $e );
						$e->save_to_db();
						break;
					case 'alarms' :
						$e = new alarm( $data );
						$root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->add_to_collection( 'alarm', $e );
						$e->save_to_db();
						break;
					case 'plan' :
						break;
					case 'bell' :
						break;
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
				echo("PUT\n");
				switch( $endpoint )
				{
					case 'plans' :
						break;
					case 'bells' :
						break;
					case 'alarms' :
						break;
					case 'plan' :
						$e = $root->get_collection_element( 'plan', $plan_id );
						$e->load_from_object( $data );
						$e->save_to_db();
						break;
					case 'bell' :
						$e = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id );
						$e->load_from_object( $data );
						$e->save_to_db();
						break;
					case 'alarm' :
						$e = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->get_collection_element( 'alarm', $alarm_id );
						$e->load_from_object( $data );
						$e->save_to_db();
						break;
				}
				$this->timer->create_timers();
				$req->sendReply(200, "OK");
				echo( " 200 OK\n");
				break;
			case EventHttpRequest::CMD_DELETE :
				echo("DELETE\n");
				switch( $endpoint )
				{
					case 'plans' :
						break;
					case 'bells' :
						break;
					case 'alarms' :
						break;
					case 'plan' :
						$e = $root->remove_from_collection( 'plan', $plan_id );
						break;
					case 'bell' :
						$e = $root->get_collection_element( 'plan', $plan_id )->remove_from_collection( 'bell', $bell_id );
						break;
					case 'alarm' :
						$e = $root->get_collection_element( 'plan', $plan_id )->get_collection_element( 'bell', $bell_id )->remove_from_collection( 'alarm', $alarm_id );
						break;
				}
				$e->delete_from_db();
				unset($e);
// 				$root->print_this();
				$this->timer->create_timers();
				$req->sendReply(200, "OK");
				echo( " 200 OK\n");
				break;
		}
		return true;
	}
}
?>