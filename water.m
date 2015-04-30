function water

host = 'www.swaytech.biz';
obj = tcpip( host, 2015 );
fopen( obj );

fwrite( obj, 'wang dehui-xie zhimei' );
pause( 2 );
fwrite( obj, 'wwwwwwwww' );
pause( 2 );
fwrite( obj, 'hhhhhhhh' );
pause( 3 );
fclose( obj );