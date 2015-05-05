<?php
	require_once( "./clear.php" );
	
	$mysql_user = 'root';
	$mysql_pass = 'blue';
	
	//$data_str = "##0245ST=32;CN=2051;PW=123456;MN=12091200160055;CP=&&DataTime=20150430135700;B01-Min=895.13,B01-Avg=909.87,B01-Max=932.28,B01-Cou=54.59;011-Min=999.99,011-Avg=999.99,011-Max=999.99,011-Cou=54.59;060-Min=30.18,060-Avg=30.18,060-Max=30.18,060-Cou=1.65&&7640";
	//$data_str = "##0149ST=32;CN=2011;PW=123456;MN=12091200160055;CP=&&DataTime=20150430135830;B01-Rtd=880.17,B01-Flag=N;011-Rtd=999.99,011-Flag=N;060-Rtd=30.18,060-Flag=N&&F881";
	//$data_str = "##0144ST=32;CN=2021;PW=123456;MN=12091200160055;CP=&&DataTime=20150430135830;SB1-RS=0;SB2-RS=0;SB3-RS=0;SB4-RS=0;SB5-RS=0;SB6-RS=0;SB7-RS=0;SB8-RS=0&&A781";
	//process_data( $data_str );
	
//---------------------------------------------------------
	class name_value{
		public $name = '';
		public $value = '';
	}
	
	class data_val {
		public $QN = 0;
		public $ST = 0;
		public $FLAG = 0;
		public $CN = 0;
		public $MN = '';
		public $PW = '';
		public $CP = '';
	}
	
	function process_data( $data_str ) {
		
		$dv = new data_val();
		parse_data( $data_str, $dv );
		
		$CP_data = array();
		$res = parse_CP( $dv->CP, $CP_data );
		if( $res>0 ) {							// 向数据库中写入数据
			//var_dump( $CP_data );
			// 注册设备
			$con = touch_mysql();
			$query_str = "CALL add_belam_dev( $dv->MN )";
			mysql_unbuffered_query( $query_str, $con );
			mysql_close( $con );
			
			if( !empty($CP_data['DataTime']) ) {
				// 首先解码时间
				$data_time = strtotime( $CP_data['DataTime'] );
				unset( $CP_data['DataTime'] );
				if( $data_time<time() )
					$data_time = time();
				
				foreach ( $CP_data as $key => $value) {
					$key_1 = strtok( $key, "-" );
					$key_2 = strtok( "-" );
					
					if( empty($key_1) || empty($key_2) )
						continue;
					
					$r_num = mt_rand( 1, 6 );
					if( $r_num==1 )
						clear_dev_d_id( $dv->MN, $key );
				
					$remark = '';
					$unit = get_uint_and_name( $key_1, $remark );

					switch( $key_2 ) {
						case 'Flag':
							$unit = 'sys/null';
							add_dev_p( $dv->MN, $key, $remark, $unit );
							save_belam_data( $dv->MN, $key, ord($value), $data_time );
							break;
							
						default:
							if( empty($unit) )
								$unit = 'sys/null';
							add_dev_p( $dv->MN, $key, $remark, $unit );
							save_belam_data( $dv->MN, $key, $value, $data_time );
							break;
					}
					
				}
			}	
		}
		
	}

	function parse_data( $data_str, &$data_val ) {
		// 设置时区为 0 时区
		//date_default_timezone_set( 'UTC' );
		date_default_timezone_set( 'Asia/Chongqing' );			

		$head = substr( $data_str , 0, 2 );
		switch( $head ) {
			case '##':
				$data_str = substr( $data_str , 2 );
				break;
			default:
				return NULL;
				exit;
		}		
		
		$pal = " ;\r\n";
		
		$data_len = intval( substr($data_str, 0, 4) );
		$data_str = substr( $data_str , 4 );
		
		$nv = new name_value();
		$tok = strtok( $data_str, $pal );	
		while( $tok!==false ) {
			
			get_name_value( $tok, $nv );

			switch( $nv->name ) {
				case 'QN':
					// 最后三位为毫秒值
					$t = substr( $nv->value, 0, 14 );
					$ms = substr( $nv->value, -3 );
					$data_val->QN = strtotime($t) + floatval($ms)/1000;
					break;
					
				case 'ST':
					$data_val->ST = intval( $nv->value );
					break;
					
				case 'CN':
					$data_val->CN = intval( $nv->value );
					break;
					
				case 'Flag':
					$data_val->FLAG = intval( $nv->value );
					break;
				
				case 'PW':
					$data_val->PW = $nv->value;
					break;
					
				case 'MN':
					$data_val->MN = $nv->value;
					break;
					
				case 'CP':
					$data_val->CP = $nv->value;
					$tok = strtok( $pal );
					while( $tok!==FALSE ) {
						$data_val->CP = $data_val->CP.";$tok";
						$tok = strtok( $pal );
					}
					break;
					
				default:
					break;
			}
	
			$tok = strtok( $pal );
		}
	}	
	
	// $str - ××=aa
	// 返回 xx 和 aa 值; 或者返回 ''
	function get_name_value( $str, &$nv ) {
		$pos = strpos( $str, '=' );
		if( $pos!==FALSE ) {
			$nv->name = substr( $str, 0, $pos );
			$nv->value = substr( $str, $pos+1 );	
		}
	}
	
	
	function parse_CP( $data_str, &$CP_data ) {
		
		if( empty($data_str) )
			return -1;
		
		$mid1 = substr( $data_str, 0,2 );
		$mid2 = substr( $data_str, -6, 2 );
		if( $mid1==='&&' && $mid2==='&&' ) {
			
			$data_str = substr( $data_str, 2, strlen($data_str)-8 );

			$pal = ", ;\r\n";
			$nv = new name_value();
			$tok = strtok( $data_str, $pal );	
			while( $tok!==false ) {
				get_name_value( $tok, $nv );
				$CP_data[$nv->name] = $nv->value;
				$tok = strtok( $pal );	
			}
		}
		else
			return -1;
		
		return 1;
	}

	function touch_mysql() {
	
		global $mysql_user, $mysql_pass;
		
		$con = mysql_connect( 'localhost', $mysql_user, $mysql_pass );
		if( !$con )
			die( 'Could not connect: ' . mysql_error() );
			
		mysql_unbuffered_query( "SET NAMES 'utf8'", $con );
		mysql_select_db( 'data_db', $con );
		return $con;
	}	
	
// 根据污染物代码，从 data_db.pollution_table 表中获得对应参数的单位
	function get_uint_and_name( $code, &$name ) {
		$con = touch_mysql();
		if( empty($con) )
			return '';
		
		$sql_str = "SELECT unit, v_name FROM pollution_table WHERE code='$code'";
		$res = mysql_query( $sql_str, $con );
		if( empty($res) )
			return '';
		
		$row = mysql_fetch_array( $res );
		$name = $row[1];
		mysql_free_result( $res );
		mysql_close( $con );
		return $row[0];
	}
	
	function add_dev_p( $dev_id, $name, $remark, $unit ) {
		$con = touch_mysql();
		if( empty($con) )
			return -1;
		$sql_str = "CALL add_dev_p( '$dev_id', '$name', '$remark', '$unit' )";
		//echo "$sql_str\r\n";
		mysql_unbuffered_query( $sql_str, $con );
		mysql_close( $con );
		return 1;
	}
	
	// $name - 待保存的参数名称
	// $val - 待保存参数的值
	function save_belam_data( $dev_id, $name, $val, $t ) {
		$con = touch_mysql();
		if( empty($con) )
			return -1;
		
		$sql_str = "CALL save_belam_data( '$dev_id', '$name', '$val', $t )";
		//echo "$sql_str\r\n";
		mysql_unbuffered_query( $sql_str, $con );
		mysql_close( $con );
		
		return 1;
	}
	
	
?>