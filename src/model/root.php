<?php
require_once("model.php");

class root extends model
{
  protected $plan = array();
  
  protected $plans = array();
  protected $bells = array();
  protected $alarms = array();
  
  protected $__is_new = true;
  protected $__collections = array ( 'plan' );
  protected $__parent_ids = array();
  protected $__table = null;
  
}

?>