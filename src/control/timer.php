<?php

class timer
{
	protected $base;
	protected $root;
	protected $timers = array();
	
	public function __construct( $base, $root )
	{
		$this->base = $base;
		$this->root = $root;
		
		$this->create_timers();
	}
	
	public function create_timers()
	{
		$this->timers = array();
		
		$root = $this->root;
		echo(date("\n\nY-m-d H:i:s\n"));
		echo("Reseting timers...\n");
		foreach( $root->get_collection( 'plan' ) as $plan )
		{
			if( $plan->get('active') == 1 )
			{
				foreach( (array)$plan->get_collection( 'bell' ) as $bell )
				{
					foreach( (array)$bell->get_collection( 'alarm' ) as $alarm )
					{
						if( $alarm->get( 'active' ) == 1 and substr( $alarm->get( 'days' ), ((int)date('N') - 1), 1 ) == 1 )
						{
							
							$alert_time = strtotime( date("Y-m-d ") . $alarm->get( 'time' ) . ":00" ); 
							$delay = $alert_time - time();
							if( $delay > -1 )
							{
								$timer = Event::timer
								( 
									$this->base,
									function( $args )  
									{
										extract( $args );
										echo(date("Y-m-d H:i:s") . " {$bell_title} ({$alarm_time}) activated!\n");
										play_mp3( $melody );
									}, 
									array(
										'bell_title'=>$bell->get('title'),
										'alarm_time'=>$alarm->get('time'),
										'melody'=>$bell->get('melody') )
								);
								$timer->addTimer( $delay );
								$this->timers[] = $timer; 
								echo("Timer (" . $alarm->get( 'time' ) . ") have been set\n");
							}
						}
					}
				}
			}
		}
// 		Event::timer($base,function($timer) use (&$timer) 
// 		{
// 			echo("Timeout!\n");
// 		});
// 		$timer->addTimer( 1 );
	}
}

?>