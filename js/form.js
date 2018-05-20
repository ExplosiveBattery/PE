//整个思路暂时不考虑12月跨1月，不考虑时间跨度跨了2个月
date =new Array();
date['江安']=Array([326,403],[408,420],[423,424]);
date['望江']=Array([502,504],[507,528]);
now_date =new Date();
today_date=now_date.getMonth()*100+100+now_date.getDate();

//find valiable date
for (campus in date) 
	for (period in date[campus]) {
		var judge =today_date<date[campus][period][0];
		judge +=today_date<date[campus][period][1];
		switch(judge) {
			case 0:	delete date[campus][period];
					if ( date[campus][date[campus].length-1]==undefined ) delete date[campus];
					continue;
			case 1: date[campus][period][0] =today_date;
					break;
			case 2:  ;
			default:  ;
		}
	}

//give choices
$campus =document.getElementById('campus');
$date =document.getElementById('date');
$when =document.getElementById('when');
for (campus in date) 
	if( date[campus]!=undefined )
		$campus.innerHTML += '<option value='+campus+'>'+campus+'</option>';
displayByCampus($campus.options[$campus.selectedIndex].value);

	


function displayByCampus(campus) {
	$date.options.length =0;
	for (period in date[campus]) {
		var begin =date[campus][period][0];
		var end =date[campus][period][1];
		var begin_month =parseInt(begin/100);
		var end_month =parseInt(end/100)
		//这里的算法最多支持时间跨度一个月，可以改为for循环
		if (begin_month!=end_month) {
			var tmp =new Date(now_date.getFullYear(),begin_month,0 );
			var data =begin_month*100+tmp.getDate();
			for (; begin<=data ;++begin)
				$date.innerHTML +='<option value='+begin+'>'+begin+'</option>';
			begin =(begin_month+1)*100+1;
		}
		for (;begin<=end;++begin)
			$date.innerHTML +='<option value='+begin+'>'+begin+'</option>';
	}
	if ($date.options[$date.selectedIndex].value==today_date && now_date.getHours()>13 ) {
		$date.options.remove(0);
	}

	displayWhen();
}

function displayWhen() {
	$when.options.length =0;
	$when.innerHTML += '<option value='+'上午'+'>'+'上午'+'</option>';
	$when.innerHTML += '<option value='+'下午'+'>'+'下午'+'</option>';
	if ($date.options[$date.selectedIndex].value==today_date && now_date.getHours()>8 && now_date.getHours()<13) //并且当前选中是今天
		$when.options.remove(0);
}

function reserve() {
	var ajax_option={
		url:"http://www.hellovega.cn/PE/record.php",
		success:function(data){
			alert(data);//考虑到系统简单，就不适用json传递
			$(this).resetForm();
		},
		error:function(e){
			console.log("into error");
			alert("into error function");
		}
	}
	$('#PEform').ajaxSubmit(ajax_option);
	return false;
}

function cancle() {
	var ajax_option={
		url:"http://www.hellovega.cn/PE/cancle.php",
		success:function(data){
			alert(data);
		},
		error:function(XmlHttpRequest, textStatus, errorThrown){
			alert("into error function");
		}
	}
	$('#PEform').ajaxSubmit(ajax_option);
	return false;
}