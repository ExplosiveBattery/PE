<?php //method timeout headers_display
function curl_fetch($ch, $args=null) {
	if($args['method']=='POST'){
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $args['post_data']);
    }
	//curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, isset($args['timeout'])?$args['timeout']:10);
	curl_setopt($ch, CURLOPT_HEADER, isset($args['headers_display'])?$args['headers_display']==true:false);

	$file_contents = curl_exec($ch);
	return $file_contents;
}

function login($ch, $headers,$password) {
	$url ='http://pead.scu.edu.cn/jncx/';
	curl_setopt($ch, CURLOPT_URL, $url.'logins.asp');
	$headers[0]='Referer: '.$url.'left.asp';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$args['method'] ='POST';
	$args['post_data'] ='xh='.'2015141462163'.'&xm='.$password.'&Submit=%CC%E1%BD%BB';
	curl_fetch($ch,$args);
}


function logout($ch) {
	$url ='http://pead.scu.edu.cn/jncx/out.asp';
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_fetch($ch);
}
function scan($ch, $headers) {
	$url ='http://pead.scu.edu.cn/jncx/';
	curl_setopt($ch, CURLOPT_URL, $url.'xzyy.asp');
	$headers[0]='Referer: '.$url;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$str =iconv('gbk', 'utf-8//ignore', curl_fetch($ch));
	preg_match_all('/(\d{3,4},){3}.{33}/', $str, $arr);


	//write into maradb
	$con=mysqli_connect("127.0.0.1",'guest','123456','collage');
	if($con->connect_error) die('Could not connect: '.$con->connect_error);
	mysqli_query($con, "set names utf8");

	foreach( $arr[0] as $value ) 
		if( $con->query("select * from PE_scan where post='$value'")->num_rows==0 )
			$con->query("insert into  PE_scan(time, post) values('".date("H:i a")."','$value')");
	
	$con->close();


}
function run() {
	$ch =curl_init();
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_COOKIEFILE, null);
	$headers=array(
		'Referer: http://pead.scu.edu.cn/stu/', 
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0'
	);


	$password='RJ5NXC';//2015141462163
	while(TRUE) {
		login($ch, $headers,$password);
		scan($ch, $headers);
		logout($ch);
		sleep(5*60);
	}

		login($ch, $headers,$password);
		scan($ch, $headers);
		logout($ch);

	curl_close($ch);

}
ignore_user_abort(true);
set_time_limit(0);
error_reporting(~E_ALL);
date_default_timezone_set('Asia/Shanghai');
run()



?>
