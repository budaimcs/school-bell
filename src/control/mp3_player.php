<?php

function play_mp3( $file )
{
	if( file_exists( "./mp3/{$file}" ) )
	{
		echo("Playing: {$file}\n\n");
		exec("mpg321 ./mp3/{$file} > /dev/null 2>/dev/null &");
	}
	else
	{
		echo("File not found: {$file}\n\n");
	}
	
}

?>