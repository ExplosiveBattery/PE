<?php
	error_reporting(~E_ALL);

	#id check
	$id_check =(int)(intval($_POST['id'])/1e12);
	if ( $id_check!=2 ) {
		echo "alert('学号格式错误！');history.go(-1);";
		exit(0);
	}//遗憾的是我这里没哟连接数据库对学号有效性进行验证。

	$con =mysqli_connect("localhost", "guest", "123456", "collage");
	if($con->connect_error) die("can't connect to Mariadb");
	
	$delete =$con->prepare("delete from PE_reservation where id=?");
	$delete->bind_param("s", $_POST['id']);
	$delete->execute();
	echo "alert('已经尝试从数据库中删除指定账号')";
?>
