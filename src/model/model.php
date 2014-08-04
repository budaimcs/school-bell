<?php

class model
{
	protected $ID;
	
	protected $__is_new = true;
	protected $__is_changed = false;
	protected $__db = null;
	protected $__parents = array();
	
	public function __construct( $data = null )
	{
		if( !is_null( $data ) )
		{
			$this->load_from_object( $data );
		}
	}
	
	public function bind_to_db( $db )
	{
		$this->__db = $db;
	}
	
	public function load_from_object( $data, $change = true )
	{
		if( !is_object( $data ) )
			return;
		
		$properties = array_keys( get_object_vars( $this ) ); 
		foreach( (array)$properties as $property)
		{
			if( substr( $property, 0, 2 ) != "__" and !in_array( $property, (array)$this->__collections ) and isset( $data->$property) )
			{
				if( $property == 'ID')
				{
					$this->set_id( $data->ID, $change );
				}
				else
				{
					$this->set( $property, $data->$property, $change );
				}
			}
		}
		if( !is_null( $this->ID ) )
		{
			$this->__is_new = false;
		}
		else
		{
			$this->generate_id();
		}
		return true;
	}
	
	public function load_from_db( $ID = null )
	{
		
		if( !is_null( $ID ) )
		{
			$row = $this->__db->get_row( "SELECT * FROM " . $this->get_table() . " WHERE `ID`='{$ID}'" );
			$this->load_from_object( $row, false );
		}
		foreach( (array)$this->__collections as $c )
		{
			$e = new $c;
			$rows = $this->__db->get_col( "SELECT ID FROM " . $e->get_table() . 
				(( !is_null( $ID ) ) ? " WHERE `" . $e->get_parent_id_field( get_class( $this ) ) 
				. "`='" . $ID . "'" : "" ) );
				
			if( is_array( $rows ) )
			foreach( $rows as $c_ID )
			{
				$e = new $c;
				$e->bind_to_db( $this->__db );
				$e->load_from_db( $c_ID );
				$this->add_to_collection( $c, $e );
				
			}
		}
	}
	
	public function save_to_db()
	{
		foreach( (array)$this->__collections as $c )
		{
			foreach( (array)$this->$c as $e )
			{
				$e->save_to_db();
			}
		}
		if( $this->is_changed() !== true )
			return;
		$data = $this->toArray();
		unset( $data[ 'ID' ] );
		if( $this->is_new() === true )
		{
			$this->__db->insert(
				$this->get_table(),
				$data
			);
			$this->set_id( $this->__db->insert_id, false );
		}
		else
		{
			$this->__db->update(
				$this->get_table(),
				$data,
				array( "ID"=>$this->ID )
			);
		}
	}
	
	public function delete()
	{
	}
	
	public function toJSON()
	{
			$output = "{";
			$first = true;
			$properties = array_keys( get_object_vars($this) ); 
			foreach( (array)$properties as $property)
			{
				if( substr( $property, 0, 2 ) != "__" and !in_array( $property, $this->__collections ) )
				{
					if( !$first )
							$output .= ",";
					else
						$first = false;
					$output .= "{$property}:'{$this->$property}'";
				}
			}
			$output .= "}";
			return $output;
	}
	
	public function toArray()
	{
		$a = array();
		$properties = array_keys( get_object_vars($this) ); 
		foreach( (array)$properties as $property)
		{
			if( substr( $property, 0, 2 ) != "__"  and !in_array( $property, $this->__collections ) )
				$a[ $property ] = $this->$property;
		}
		return $a;
	}
	
	public function get_collection( $collection, $json = false )
	{
		if( !in_array( $collection, $this->__collections ) )
			return false;
		
		if( $json )
		{
			$output = "[";
			$first = true;
			foreach( (array)$this->{$collection} as $element )
			{
				if(!$first)
					$output .=",";
				else
					$first = false;
				$output .= $element->toJSON();
// 				echo( $element->toJSON() . "\n" );
			}
			$output .= "]";
			
			return $output;
		}
		else
		{
			return $this->$collection;
		}
		
	}
	
	public function add_to_collection( $collection, $element )
	{
		if( !in_array( $collection, $this->__collections ) )
		{
			return false;
		}
		
		$id = $element->get( 'ID' );
		$element->set_parent( get_class( $this ), $this );
		$element->bind_to_db( $this->__db );
		$this->{$collection}[ $id ] = $element;
	}
	
	public function get_collection_element( $collection, $element_id )
	{
		if( !in_array( $collection, $this->__collections ) )
		{
			return false;
		}
		
		return $this->{$collection}[ $element_id ];
	}
	
	public function is_collection_element_exists( $collection, $element_id )
	{
		if( !in_array( $collection, $this->__collections ) )
		{
			return false;
		}
		return isset( $this->{$collection}[ $element_id ] );
	}
	public function remove_from_collection( $collection, $id )
	{
		if( !in_array( $collection, $this->__collections ) )
		{
			return false;
		}
		
		$element = $this->$collection[ $id ];
		$element->set_parent_id(get_class($this), null );
		unset( $this->$collection[ $id ] );
		return $element;
	}
	
	public function change_collection_element_id( $collection, $old_id, $new_id )
	{
		if( !in_array( $collection, $this->__collections ) )
		{
			return false;
		}
		
		$this->{$collection}[ $new_id ] = $this->{$collection}[ $old_id ];
		unset( $this->{$collection}[ $old_id ] );
	}
	
	public function set_parent_id( $parent, $id )
	{
		if( !array_key_exists( $parent, $this->__parent_ids ) )
			return false;
		$act_id = $this->{$this->__parent_ids[$parent]};
		if( $id != $act_id )
		{
			$this->__is_changed = true;
			$this->set( $this->__parent_ids[$parent], $id);
		}
		return true;
	}
	
	public function get_parent_id_field( $parent )
	{
		if( !array_key_exists( $parent, $this->__parent_ids ) )
			return false;
		return $this->__parent_ids[$parent];
	}
	
	public function get_parent_id( $parent )
	{
		if( !array_key_exists( $parent, $this->__parent_ids ) )
			return false;
		return $this->get( $this->__parent_ids[$parent] );
	}
	
	public function set_parent( $parent, $object )
	{
		if( !array_key_exists( $parent, $this->__parent_ids ) )
			return false;
		$this->__parents[ $parent ] = $object;
		return $this->set_parent_id( $parent, $object->get( 'ID' ) );
	}
	
	public function set( $property, $value, $change = true )
	{
		if( in_array( $property, (array)$this->__collections ) )
		{
			return false;
		}
		if( !isset( $this->$property ) )
		{
			return false;
		}
		if( substr( $property, 0, 2) == "__" or $property == "ID")
		{
			return false;
		}
		
		if( $value == $this->$property )
		{	
			return true;
		}
		$this->$property = $value;
		if( $change ) $this->__is_changed = true;
	}
	
	public function get( $property )
	{
		if( in_array( $property, $this->__collections ) )
		{
			return false;
		}
		
		if( !isset( $this->$property ) )
			return false;
		
		if( substr( $property, 0, 2) == "__" )
			return false;
		
		return $this->$property;
	}
	
	public function is_changed()
	{
		return $this->__is_changed;
	}
	
	public function is_new()
	{
		return $this->__is_new;
	}
	
	public function get_table()
	{
		return $this->__table;
	}
	
	protected function set_id( $id, $change = true )
	{
		if( $id != $this->ID )
		{
			if( $change ) $this->__is_changed = true;
			foreach( (array)$this->__parents as $p )
			{
				$p->change_collection_element_id( get_class( $this ), $this->ID, $id );
			}
			$this->ID = $id; 
		}
		return true;
	}
	
	public function generate_id()
	{
		$this->set_id( get_class( $this ) . "-" . time() . "-" . rand(1000,9999) );
	}
}

?>