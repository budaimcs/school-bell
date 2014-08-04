<?php
function serve_file( $path, $req )
{
  echo( "Static: /" . $path );
  echo( getcwd() );
  if( file_exists( "document_root/" . $path ) and is_readable( "document_root/" . $path ) )
  {
    $buf = new EventBuffer;
    $buf->add( file_get_contents( "document_root/" . $path ) );
    $req->sendReply(200, "OK", $buf);
    echo( " 200 OK\n");
  }
  else
  {
    $req->sendError(404);
    echo( " 404 Not found!\n");
  }
    
}
?>