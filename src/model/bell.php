<?php
require_once("model.php");

class bell extends model
{
  protected $melody = '';
  protected $title = '';
  protected $plan_id = '';
  
  protected $alarm = array();
  
  protected $__is_new = true;
  protected $__collections = array ( 'alarm' );
  protected $__parent_ids = array('plan' => 'plan_id');
  protected $__table = 'bell';
  
}

?>