<?php

class resources
{
	protected $__db;
	protected $tables = array();
	protected $items = array();

	public function __construct( $db, $tables = array() )
	{
		$this->__db = $db;
		$this->tables = $tables;
		foreach( $tables as $table )
		{
			if( $table == 'root' ) continue;
			$e = new $table;
			$rows = $this->__db->get_results( "SELECT * FROM " . $e->get_table() );	
			if( is_array( $rows ) )
			foreach( $rows as $row )
			{
				$e = new $table;
				$e->bind_to_db( $this->__db );
				$e->load_from_object( $row );
				$this->put_item( $table, $e );
			}
		}
	}

	public function put_item( $table, $item )
	{
		$this->items[ $table ][ $item->get('ID') ] = $item;
	}
	
	public function delete_item( $table, $ID )
	{
		if( !$this->has_item( $table, $ID ) )
			return null;
		
		$item = $this->get_item( $table, $ID );
		$item->remove_from_parents();
		$item->delete_from_db();
		unset( $this->items[ $table ][ $ID ] );
		unset( $item );
	}
	
	public function get_item( $table, $ID, $json = false )
	{
		if( !$this->has_item( $table, $ID ) )
			return null;
		
		if( $json )
		{
			$this->items[ $table ][ $ID ]->toJSON();
		}
		else
		{
			return $this->items[ $table ][ $ID ];
		}
	}
	
	public function has_item( $table, $ID )
	{
// 		if( $table == "root" ) return true;
		return isset( $this->items[ $table ][ $ID ] );
	}
	
	public function get_list( $table, $json = false, $with_collections )
	{
		if( !in_array( $table, $this->tables ) )
			return false;
			
		if( $json )
		{
			$output = "[";
			$first = true;
			foreach( (array)$this->items[ $table ] as $element )
			{
				if(!$first)
					$output .=",";
				else
					$first = false;
				$output .= $element->toJSON( $with_collections );
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
	
}

?>