<?php 
//method timeout headers_display
include("curl.php");

function login($ch, &$account) {
	$url ='https://scusport.com/login';
	curl_setopt($ch, CURLOPT_URL, $url);
	//get token
	preg_match('/<input type="hidden" name="_token" value="([0-9a-zA-Z]+)">/', curl_fetch($ch), $token);
	$account['_token'] = $token[1];

	$args['method'] = 'POST';
	$args['post_data'] = '_token='.$account['_token'].'&scuid='.$account['scuid'].'&password='.$account['password'];
	curl_fetch($ch,$args);
	return 302==curl_getinfo($ch, CURLINFO_HTTP_CODE); //redirect to https://scusport.com/login when login successfully
}
function logout($ch,$account) {
	$url ='https://scusport.com/logout';
	curl_setopt($ch, CURLOPT_URL, $url);
	$args['method'] = 'POST';
	$args['post_data'] = '_token='.$account['_token'];
	curl_fetch($ch,$args);
}
function accountCheck($username,$password) {
	$account = Array('scuid'=>$username,'password'=>$password);
	$headers=array(
		// 'Origin: https://scusport.com',
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0'
	);

	$ch =curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "");

	$return = login($ch, $account);
	logout($ch,$account);

	curl_close($ch);
	return $return;
}
function scan() {
	$headers=array(
		// 'Origin: https://scusport.com',
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0'
	);
	$ch =curl_init();
	// curl_setopt($ch, CURLOPT_HEADER, TRUE);
	// curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "");
	// curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
	//foreach(curl_getinfo($curl_a, CURLINFO_COOKIELIST) as $cookie_line)
	$account= Array("scuid"=>'xxxx', "password"=>'xxxx');
	while (!login($ch, $account))
		sleep(60);

	$url ='https://scusport.com/reserve';
	curl_setopt($ch, CURLOPT_URL, $url);
	$headers[]='Referer: https://scusport.com/home';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	preg_match_all('/<tr>\s+?<td>(\d{4})<\/td>\s+?<td>\d{5}<\/td>\s+?<td>(.{6})<\/td>\s+?<td>(\d{4}-\d{2}-\d{2}) .{6} (.{6})<\/td>\s+?<td>[\s\S]+?<\/tr>/', curl_fetch($ch), $all);
	for($i1=0;$i1<count($all[0]);++$i1) {
		if( strpos($all[0][$i1],"预约已满")!==FALSE ) {
			$return[$all[3][$i1]." ".$all[4][$i1]." ".$all[2][$i1]] = "预约已满";
		}else if( strpos($all[0][$i1],"现在预约")!==FALSE ) {
			$return[$all[3][$i1]." ".$all[4][$i1]." ".$all[2][$i1]] = $all[1][$i1];
		}else {
			$return[$all[3][$i1]." ".$all[4][$i1]." ".$all[2][$i1]] = "暂未开放";
		}
	}

	logout($ch,$account);
	curl_close($ch);
	return $return;
}

function reserve(&$accounts, $schid){
	$headers=array(
		'Referer: https://scusport.com', 
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0'
	);

	$ch =curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "");
	foreach ($accounts as $key=>$account) {
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);

		if( login($ch,$account) ) {
			$args['method'] = 'POST';
			$args['post_data'] = '_token='.$account['_token'].'&schid='.$schid.'&tos=on';
			$subjects = explode(",", $account['subjects']);
			foreach ($subjects as $subject) {
				$args['post_data'] .= "&subject%5B%5D=".$subject;
			}
			curl_fetch($ch,$args);

			//成功则发送短信
			//...

			//删除account
			unset($accounts[$key]);


			logout($ch,$account);
		}else {
			//密码错误，如果数据库中存在记录就应该删除
				$con=mysqli_connect("mariadb",'root','','collage');
				if($con->connect_error) die('Could not connect: '.$con->connect_error);
				mysqli_query($con, 'set names utf8');
				$con->query('delete from PE_reservation where id='.$_POST['id']);
				$con->close();
		}
	}
	curl_close($ch);
}


?>

