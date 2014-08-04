<?php
require_once("model.php");

class alarm extends model
{
  protected $time = '';
  protected $days = '';
  protected $active = false;
  protected $bell_id = '';
  
  
  protected $__collections = array ();
  protected $__parent_ids = array('bell' => 'bell_id'); 
  protected $__table = 'alarm';
  
}

?>