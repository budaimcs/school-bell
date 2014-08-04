<?php
require_once("model.php");

class plan extends model
{
  protected $active = false;
  protected $title = '';
  
  protected $bell = array();
  
  protected $__is_new = true;
  protected $__collections = array ( 'bell' );
  protected $__parent_ids = array(); 
  protected $__table = 'plan';
  
}

?>