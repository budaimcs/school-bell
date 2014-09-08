<?php

class reloader 
{
	protected $timer;
	protected $base;
	protected $reloader;
	

	public function __construct( $base, $timer )
	{
		$this->timer = $timer;
		$this->base = $base;
		
		//Create reloader timer
// 		$alert_time = 0;
		$alert_time = strtotime( date("Y-m-d ") . "23:59:59" ) + 6; 
		$delay = $alert_time - time();
		if( $delay < 1 ) $delay = 60;
		$reloader = Event::timer
		( 
			$this->base,
			function( $args ) use (&$reloader)
			{
				extract( $args );
				$timer->create_timers();
// 				$alert_time = 0;
				$alert_time = strtotime( date("Y-m-d ") . "23:59:59" ) + 6; 
				$delay = $alert_time - time();
				if( $delay < 1 ) $delay = 60;
				echo("Reloaded\n");
				$reloader->delTimer();
				$reloader->addTimer( $delay );
			},
			array( 'timer' => $this->timer )  
		);
		$reloader->addTimer( $delay );
		$this->reloader = $reloader; 
		echo("Reloader have been set\n");
	}
}

?>