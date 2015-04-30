<?php

	$data_str = "##0245ST=32;CN=2051;PW=123456;MN=12091200160055;CP=&&DataTime=20150430135700;B01-Min=895.13,B01-Avg=909.87,B01-Max=932.28,B01-Cou=54.59;011-Min=999.99,011-Avg=999.99,011-Max=999.99,011-Cou=54.59;060-Min=30.18,060-Avg=30.18,060-Max=30.18,060-Cou=1.65&&7640";
	
	$dv = new data_val();
	parse_data( $data_str, $dv );
	
	$CP_data = array();
	$res = parse_CP( $dv->CP, $CP_data );
	if( $res>0 )
		var_dump( $CP_data );
	echo var_dump( $dv )."\r\n";
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
	
	function parse_data( $data_str, &$data_val ) {
		// 设置时区为 0 时区
		date_default_timezone_set ( 'UTC' );	
		
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

?>