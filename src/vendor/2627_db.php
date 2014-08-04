<?php

class code2627_db extends ezSQL_mysql
{
	public function update( $table, $set, $where )
	{
		$sql = "UPDATE {$table} SET ";
		$first = true;
		if( is_array( $set ) )
		foreach($set as $key=>$value)
		{
			if( $first != true )
				$sql .= ",";
			else
				$first = false;
			$sql .= "`" . $this->escape( $key ) . "`='" . $this->escape( $value ) . "'";
		}
		
		$first = true;
		if( is_array( $where ) )
		{
			$sql .= " WHERE ";
			foreach($where as $key=>$value)
			{
				if( $first != true )
					$sql .= " and ";
				else
					$first = false;
				$sql .= "`" . $this->escape( $key ) . "`='" . $this->escape( $value ) . "'";
			}
		}
		
		
		return $this->query( $sql );
	}
	
	public function insert( $table, $set )
	{
		$sql = "INSERT INTO {$table} (";
		$first = true;
		if( is_array( $set ) )
		foreach($set as $key=>$value)
		{
			if( $first != true )
				$sql .= ",";
			else
				$first = false;
			$sql .= "`" . $this->escape( $key ) . "`";
		}
		$sql .= ") VALUES (";
		$first = true;
		if( is_array( $set ) )
		foreach($set as $key=>$value)
		{
			if( $first != true )
				$sql .= ",";
			else
				$first = false;
			$sql .= "'" . $this->escape( $value ) . "'";
		}
		$sql .= ")";
		return $this->query( $sql );
	}
}

?>