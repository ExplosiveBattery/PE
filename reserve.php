
<?php
include("PE_functions.php");
error_reporting(~E_ALL);
date_default_timezone_set("Asia/Shanghai");//注意时间是不是一样的

//find ids from databases
$con=mysqli_connect("localhost",'guest','123456','collage');
if($con->connect_error) die('Could not connect: '.$con->connect_error);
mysqli_query($con, 'set names utf8');

$will_date =date("Y-m-d", strtotime("+1 day"));
$morning_accounts =getAccounts($con, $will_date." 上午");
$afternoon_accounts =getAccounts($con, $will_date." 下午");
/*
var_dump($morning_accounts);
var_dump($afternoon_accounts);
*/

$sleep_time = 4;
while(1) {
	$scan_result =scan();
	if ($morning_accounts!=[])
		if ($scan[$morning_accounts['want']]!=="预约已满" && $scan[$morning_accounts['want']]!=="暂未开放"):
			reserve($morning_accounts, $scan[$morning_accounts['want']]);			
			if($morning_accounts===[]):
				if(strtotime(date("Y-m-d H:i:s", strtotime("12:59"))) > time()):
					sleep( strtotime(date("Y-m-d H:i:s", strtotime("12:59")))-time() );
					continue;
				endif;
			endif;
		elseif:
			if(strtotime(date("Y-m-d H:i:s", strtotime("8:05"))) < time()):
				$sleep_time = 30;
			endif;
		endif;
	if ($afternoon_accounts!=[])
		if ($scan[$afternoon_accounts['want']]!=="预约已满" && $scan[$afternoon_accounts['want']]!=="暂未开放"):
			reserve($afternoon_accounts, $scan[$afternoon_accounts['want']]);			
		elseif:
			if(strtotime(date("Y-m-d H:i:s", strtotime("13:05"))) < time()):
				$sleep_time = 30;
			endif;
		endif;	
	if (!$morning_accounts&&!$afternoon_accounts)
		break; //exit(0);
	sleep($sleep_time);
}


function getAccounts($con, $want) {
	$result =$con->query("select * from PE_reservation where want like '".$want."%'");
	$arr =mysqli_fetch_all($result,MYSQLI_ASSOC); 
	mysqli_free_result($result);

	return $arr;
}

?>