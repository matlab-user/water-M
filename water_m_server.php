<?php

	set_time_limit( 0 );
	ob_implicit_flush();
	
	date_default_timezone_set( 'Asia/Chongqing' );
	
	$mysql_user = 'root';				// 数据库访问用户名
	$mysql_pass = 'blue';				// 数据库访问密钥
	$port = 2015;
	
START:
	$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
	if( $socket===false ) {
		echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		exit;
	}

	$rval = socket_get_option($socket, SOL_SOCKET, SO_REUSEADDR);
	if( $rval===false )
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()).PHP_EOL;
	elseif( $rval!==0 )
		echo 'SO_REUSEADDR is set on socket !'.PHP_EOL;
		
	socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

	$ok = socket_bind( $socket, 'www.swaytech.biz', $port );
	if( $ok===false ) {
		echo "false  \r\n";
		echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) )."\r\n";
		exit;
	}
/*
	socket_getsockname ( $socket, $A, $P );
	echo get_local_ip().'     '.$P.'         '.time()."\n";
	socket_close( $socket );
	exit;
*/	
	echo "The water-M UDP server is running!\n";
	
	while( true ) {
		
		$r = array( $socket );

		$num = socket_select( $r, $w=NULL, $e=NULL, 16 );
		if( $num===false ) {
			echo "socket_select() failed, reason: ".socket_strerror(socket_last_error())."\n";
			socket_close( $socket );
			sleep( 20 );
			goto START;
		}
		elseif( $num>0 ) {
				socket_recvfrom( $socket, $buf, 1000, 0, $to_ip, $to_port );
				error_log( date("Y-m-d H:i:s")."\t".$buf."\r\n", 3, '/tmp/water-M.log' );
/*
				if( strlen($buf)>1 ) {
					
					echo "op_res---".$buf."\n";
					
					$str_array = str_split( $buf );
					$buf = substr( $buf, 1 );
					
					switch( $str_array[0] ) {
						case 'D':					// 解析数据
							parse_data( $buf );
							break;
							
						case 'I':					// 解析设备ip、port
							$gid = parse_I( $buf );
							socket_getsockname ( $socket, $A, $P );
							$A = get_local_ip();
							save_local_ip_port( $gid, $A, $P );
							save_remote_ip_port( $gid, $to_ip, $to_port );
							break;
							
						case 'S':					// 客户发送指令
							parse_S( $buf, $gid, $cmd );
							$r_ip = '';
							$r_port = '';
							get_remote_ip_port( $gid, $r_ip, $r_port );
							if( empty($r_ip) ) {
								$msg = 'FAIL';
								goto LP1;
							}
							
							socket_sendto( $socket, $cmd, strlen($cmd), 0, $r_ip, $r_port ); 
							echo 'I have send the cmd:'.$cmd."\n";
							
							$buf = '';
							$rev_num = socket_recvfrom( $socket, $buf, 20, 0, $r_ip, $r_port );
							$msg = 'FAIL';
							if( $rev_num==True ) {
								if( $buf==='OK' )
									$msg = 'OK';
							}
						
						LP1:
							echo $msg."\n";
							socket_sendto( $socket, $msg, strlen($msg), 0, $to_ip, $to_port ); 						
							break;
						
						case 'N':
							parse_N( $buf );
							break;
						
						case 'G':
							$gid = parse_I( $buf ); 
							$n_str = get_normal( $gid );
							socket_sendto( $socket, $n_str, strlen($n_str), 0, $to_ip, $to_port ); 			
							break;
							
						default:
							break;
					}
				}
*/
		}
	}
	
//--------------------------------------------------------------------------------------------------------
//			sub_funs 
//--------------------------------------------------------------------------------------------------------
// [dev_gid,start,timestamp,p,t,f,r]
	function parse_data( $data_str ) {
		$mid_str = explode( "[", $data_str );
		$mid_str = explode( "]", $mid_str[1] );
		if( count($mid_str)>0 ) {
			$segs = explode(",", $mid_str[0] );
			
			$gid = $segs[0];
			$start = $segs[1];
			$time = $segs[2];
			$p = $segs[3];
			$t = $segs[4];
			$f = $segs[5];
			$r = $segs[6];
			if( $time=='null' )
				$time = time();
			
			save_data( $gid, $start, $time, $p, $t, $f, $r );
			//echo count($segs)."\n";
			//var_dump( $segs );
			//echo "\n";
		}
	}
	
// I[gid]
	function parse_I( $data_str ) {
		$mid_str = explode( "[", $data_str );
		$mid_str = explode( "]", $mid_str[1] );
		if( count($mid_str)>0 ) {
			$gid = $mid_str[0];
			//var_dump( $gid );
			//echo "\n";
			return $gid;
		}
	}	
	
// S[gid,指令]
	function parse_S( $data_str, &$gid, &$cmd ) {
		$mid_str = explode( "[", $data_str );
		$mid_str = explode( "]", $mid_str[1] );
		if( count($mid_str)>0 ) {
			$segs = explode(",", $mid_str[0] );
			$gid = $segs[0];
			$cmd = '['.$segs[1].']';
			//echo $cmd."\n";
			//var_dump( $segs );
			//echo "\n";
		}
	}
	
// N[gid,v_name,th1,th2]
	function parse_N( $data_str ) {
		$mid_str = explode( "[", $data_str );
		$mid_str = explode( "]", $mid_str[1] );
		if( count($mid_str)>0 ) {
			$segs = explode(",", $mid_str[0] );
			
			$gid = $segs[0];
			$v_name = $segs[1];
			$th1 = $segs[2];
			$th2 = $segs[3];
			
			save_N( $gid, $v_name, $th1, $th2 );
			//echo count($segs)."\n";
			//var_dump( $segs );
			//echo "\n";
		}
	}
	
	function get_local_ip() {
		$preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
		exec ( "ifconfig" , $out , $stats );
		if( !empty($out) ) {
			if( isset($out[1]) && strstr($out[1],'addr:') ) {
				$tmpArray = explode( ":" , $out[1] );
				$tmpIp = explode( " " , $tmpArray[1] );
				if( preg_match($preg,trim($tmpIp[0])) ) {
					return trim( $tmpIp[0] );
				}
			}
		}
		return '127.0.0.1' ;
	} 

/*------------------------------------------------------------------------
			mysql funs
---------------------------------------------------------------------------*/

	function connect_mysql( $mysql_user, $mysql_pass ) {
		$con = mysql_connect( "localhost", $mysql_user, $mysql_pass );
		if ( !$con )
			return '';
	
		mysql_query("SET NAMES 'utf8'", $con);
		return $con;
	}
	
	function save_local_ip_port( $gid, $lip, $lport ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$sql_str = "UPDATE hx_k_db.dev_t SET l_ip='".$lip."', l_port=".$lport." WHERE gid='".$gid."'";
		mysql_query( $sql_str, $con );
		
		mysql_close($con);
	}
	
	function save_remote_ip_port( $gid, $rip, $rport ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$sql_str = "UPDATE hx_k_db.dev_t SET d_ip='".$rip."', d_port=".$rport." WHERE gid='".$gid."'";
		mysql_query( $sql_str, $con );
		
		mysql_close($con);
	}

	// $p - 压力
	// $t - 温度
	// $f - 流量
	// $r - 阻力
	// 输入参数皆为字符串
	function save_data( $gid, $start, $time, $p, $t, $f, $r ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$sql_str = sprintf( "INSERT INTO hx_k_db.data_t (dev_id,v_name,value,time,batch) VALUES ('%s','%s',%f,%d,%d)", $gid,'p',$p,$time,$start );
		mysql_query( $sql_str, $con );

		$sql_str = sprintf( "INSERT INTO hx_k_db.data_t (dev_id,v_name,value,time,batch) VALUES ('%s','%s',%f,%d,%d)", $gid,'t',$t,$time,$start );
		mysql_query( $sql_str, $con );
		
		$sql_str = sprintf( "INSERT INTO hx_k_db.data_t (dev_id,v_name,value,time,batch) VALUES ('%s','%s',%f,%d,%d)", $gid,'f',$f,$time,$start );
		mysql_query( $sql_str, $con );
		
		$sql_str = sprintf( "INSERT INTO hx_k_db.data_t (dev_id,v_name,value,time,batch) VALUES ('%s','%s',%f,%d,%d)", $gid,'r',$r,$time,$start );
		mysql_query( $sql_str, $con );
		
		mysql_close($con);
	}
	
	function get_remote_ip_port( $gid, &$r_ip, &$r_port ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$sql_str = sprintf( "SELECT d_ip, d_port FROM hx_k_db.dev_t WHERE gid='%s'", $gid );
		$res = mysql_query( $sql_str, $con );
		$t = mysql_fetch_array( $res );
		
		$r_ip = $t[0];
		$r_port = intval( $t[1] );
		
		mysql_free_result( $res );
		mysql_close($con);
	}

	function save_N( $gid, $v_name, $th1, $th2 ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$sql_str = sprintf( "INSERT INTO hx_k_db.normal_t SET gid='%s', v_name='%s', th1=%s, th2=%s", $gid, $v_name, $th1, $th2 );
		$res = mysql_query( $sql_str, $con );
		
		$sql_str = sprintf( "UPDATE hx_k_db.normal_t SET th1=%s, th2=%s WHERE gid='%s' AND v_name='%s'", $th1, $th2, $gid, $v_name );
		$res = mysql_query( $sql_str, $con );
		
		mysql_close($con);
	}
	
	function get_normal( $gid ) {
		global $mysql_user, $mysql_pass;
		$con = connect_mysql( $mysql_user, $mysql_pass );
		if( empty($con) )
			return;
		
		$res_str = '[';
		
		$sql_str = sprintf( "SELECT th1, th2 FROM hx_k_db.normal_t WHERE gid='%s' AND v_name='%s'", $gid, 'p' );
		$res = mysql_query( $sql_str, $con );
		if( $row = mysql_fetch_array($res) )
			$res_str .= $row[0].','.$row[1].',';
		else
			$res_str .= '-,-,';
		mysql_free_result( $res );
	
		$sql_str = sprintf( "SELECT th1, th2 FROM hx_k_db.normal_t WHERE gid='%s' AND v_name='%s'", $gid, 't' );
		$res = mysql_query( $sql_str, $con );
		if( $row = mysql_fetch_array($res) )
			$res_str .= $row[0].','.$row[1].',';
		else
			$res_str .= '-,-,';
		mysql_free_result( $res );
		
		$sql_str = sprintf( "SELECT th1, th2 FROM hx_k_db.normal_t WHERE gid='%s' AND v_name='%s'", $gid, 'f' );
		$res = mysql_query( $sql_str, $con );
		if( $row = mysql_fetch_array($res) )
			$res_str .= $row[0].','.$row[1];
		else
			$res_str .= '-,-';
		mysql_free_result( $res );
		
		mysql_close($con);
		
		$res_str .= ']';
		return $res_str;
	}
?>