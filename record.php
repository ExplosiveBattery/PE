<?php
	include("PE_functions.php");


	error_reporting(~E_ALL);


	$date['江安']=Array(Array(326,403),Array(408,420),Array(423,424));
	$date['望江']=Array(Array(502,504),Array(507,528));
	
	#account check
	if( !accountCheck($_POST['id'],$_POST['password']) ) {
		echo "账号或密码错误！";
		goBack();
	}
	
	#telephone check
	if(!preg_match("/^1[34578]{1}\d{9}$/",$_POST['telephone'])) {
		echo "手机号格式错误！";
		goBack();
	}

	#time and campus check
	$postdate =myStrtoTime($_POST['date']);
	if(date("H")<"13")
		$today_date =strtotime(date("Y-m-d"));
	else
		$today_date =strtotime(date("Y-m-d",strtotime("+1 day")));
	$valid =false;
	if($today_date<=$postdate)
		foreach ($date as $campusKey=>$campusValue)
			foreach ($campusValue as $key=>$value)
				if ($postdate>=myStrtoTime((string)($value[0])) && $postdate<=myStrtoTime((string)($value[1])) && $_POST['campus']==$campusKey)
					$valid =true;
	if ($valid==false)
		goBack();

	#when check
	if ($_POST['when']!='下午' &&  $_POST['when']!='上午')
		goBack();

	#change variables
	if (strlen($_POST['date'])==3)
		$_POST['date'] =date("Y").'-0'.$_POST['date'][0].'-'.substr($_POST['date'],1);
	else
		$_POST['date'] =date("Y").'-'.substr($_POST['date'],0,2).'-'.substr($_POST['date'],2);

	$_POST['subject'] = implode(',', $_POST['subject']);

	#immediately reserve	
	$scan_result = scan();
	$want_to_find = $_POST['date'].' '.$_POST['when'].' '.$_POST['campus'];
	foreach ($scan_result as $key => $value) {
		if($want_to_find===$key) {
			if($value==='预约已满') {echo '您选的时间不能被预约，人数已满'; goBack(); }
			else if($value!=='暂未开放') {
				$account = Array('scuid'=>$_POST['id'], 'password'=>$_POST['password'],'telephone'=>$_POST['telephone'],'subjects'=>$_POST['subject']);
				reserve(Array($account), $value);
				goBack();
			}
		}
	}
	writeToDatabase();


function goBack() {
	// echo "<script>history.go(-1);</script>";
	exit(0);
}

/* 数据库建表语句
create table PE_reservation(
id char(13),			学号
password varchar(10),	本科系统密码
telephone char(11),		手机号
want char(24),			2018-05-13 望江 下午
subjects varchar(35)	xxx,xxx,xxx,xxx...
);
*/
function writeToDatabase() {
	//write into databases
	// $con=mysqli_connect("127.0.0.1",'guest','123456','collage');
	$con=mysqli_connect("mariadb",'root','','collage');
	if($con->connect_error) die('Could not connect: '.$con->connect_error);
	mysqli_query($con, 'set names utf8');

	//id already exits will update
	$result =$con->query('select * from PE_reservation where id='.$_POST['id']);
	if( $result->num_rows==0 ) {
		$stmt =$con->prepare("insert into PE_reservation set password=?,telephone=?,want=?,subjects=?,id=?");
	}else{
		$stmt =$con->prepare("update PE_reservation set password=?,telephone=?,want=?,subjects=? where id=?");
	}
	$want = $_POST['date'].' '.$_POST['when'].' '.$_POST['campus'];
	$stmt->bind_param('sssss',$_POST['password'],$_POST['telephone'],$want,$_POST['subject'],$_POST['id']);
	$stmt->execute();
	echo "设置成功";

	$stmt->close();
	$con->close();
}
?>		
