<?php
//$_POST['question']="";
error_reporting(~E_ALL);
if (!isset($_POST['question']) || $_POST['question']=="") {
	echo "<script>alert('����Ҫ������ϸ����Ŀ����');history.go(-1);</script>";
	exit(1);
}
//write into databases
$con=new mysqli("127.0.0.1",'guest','123456','collage');
if(mysqli_connect_errno()) die('Could not connect: '.mysqli_connect_error());
mysqli_query($con, 'set names utf8');

//query(defense SQL injection)
$stmt = $con->prepare("select question,answer from PE_exam where question like ?");
//$stmt->bind_param('s', "%".$_POST['question']."%");
$question ="%".$_POST['question']."%";
$stmt->bind_param('s', $question);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_array(MYSQLI_NUM);
var_dump($result);
$stmt->close();

//id already exits will update
if( $result->num_rows==0 ) {
	echo "<script>alert('��ʹ�õ����������⣬����ϵ��ظ�����');</script>";
}else if($result->num_rows==1){
	echo "<script>alert('".$row[0]."�Ļش��ǣ�".$row[1]."');history.go(-1);</script>";
}else {
	echo "<script>alert('����Ҫ������ϸ����Ŀ����');history.go(-1);</script>";
}

$con->close();
?>