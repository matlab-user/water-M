<?php
/*
	清除数据库中指定设备、指定参数的历史数据
	设备以 guid1 号给出, 参数以 名字 指出
	以总数据数量为依据，删除数据记录
*/
	$mysql_user = 'root';
	$mysql_pass = 'blue';
	
	$max_data_num = (24*3600)/10*3;

	//clear_dev_d_id( '12091200160055', '011-Min' );


//----------------------------------------------------------------------------------------------------
	function touch_mysql_1() {
	
		global $mysql_user, $mysql_pass;
		
		$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
		if( !$con )
			die( 'Could not connect: ' . mysql_error() );
			
		mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
		mysql_select_db( 'data_db', $con );
		return $con;
	}
	
	function clear_dev_d_id( $dev_id, $name ) {
		global $max_data_num;
		
		$num = 0;
		
		$con = touch_mysql_1();
		$sql_str = "SELECT COUNT(*) FROM his_data WHERE dev_id='$dev_id' AND v_name='$name'";
		//echo "$sql_str\r\n";
		$res = mysql_query( $sql_str, $con );
		if( $row=mysql_fetch_array($res) ) {
			$num = intval( $row[0] );
			mysql_free_result( $res );
		}
		else {
			mysql_close( $con );
			return;
		}

		if( $num>$max_data_num ) {
			$diff = $num - $max_data_num;
			$sql_str = "DELETE FROM his_data WHERE dev_id='$dev_id' AND v_name='$name' ORDER BY time ASC LIMIT $diff";
			mysql_unbuffered_query( $sql_str, $con );
		}
		
		mysql_close( $con );
	}
?>
