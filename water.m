function water

host = 'www.swaytech.biz';
obj = tcpip( host, 2015 );
fopen( obj );

data = '##0245ST=32;CN=2051;PW=123456;MN=12091200160055;CP=&&DataTime=20150430135800;B01-Min=880.17,B01-Avg=900.88,B01-Max=928.08,B01-Cou=54.05;011-Min=999.99,011-Avg=999.99,011-Max=999.99,011-Cou=54.05;060-Min=30.18,060-Avg=30.18,060-Max=30.18,060-Cou=1.63&&9841';
data = [ data 13 10 ];
fwrite( obj, data );
% fwrite( obj, 'wang dehui-xie zhimei' );
% pause( 2 );
% fwrite( obj, 'wwwwwwwww' );
% pause( 2 );
% fwrite( obj, 'hhhhhhhh' );
% pause( 3 );
fclose( obj );