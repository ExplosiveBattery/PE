#!/usr/bin/python3
#image
from pretreat_image import downloadImg,getListFromImageInSimpleWay
from PIL import Image
#web
import requests
from bs4 import BeautifulSoup,SoupStrainer
import re
#Mariadb
import pymysql.cursors
#threads
from concurrent.futures import ThreadPoolExecutor,wait
#sleep
from time import sleep


config =dict(
	host ='127.0.0.1',
	port =3306,
	user='root',
	password='',
	db='PE',
	charset='utf8',
	cursorclass=pymysql.cursors.DictCursor
)
connection =pymysql.connect(**config)
#create database PE character set utf8 collate utf8_general_ci;
#use PE;
#create table exam( question varchar(150), A tinyint(1), B tinyint(1), C tinyint(1), D tinyint(1),answer varchar(150));




def getCode():
	read_file =open('imgout/record.txt','r')
	lines =read_file.readlines()
	dictionary={}
	for line in lines:
		ch =line[0]
		tmp_list =line[2:302]
		dictionary[tmp_list]=ch
	read_file.close()
	s =requests.Session()
	s.get(url='http://202.115.33.141/stu/count.asp', timeout=8)
	s.get(url='http://202.115.33.141/stu/count.asp?security_verify_data=323536302c31303830', timeout=8)
	downloadImg(s, 'http://202.115.33.141/stu/count.asp', '1.bmp');image =Image.open('1.bmp')
	code=''
	for j in range(4):
		subimage =image.crop((j*10,0,j*10+10,10))
		code +=dictionary[ str(getListFromImageInSimpleWay(subimage)) ]
	return code

def getFetchCh(cursor, text):
	question =re.findall(r'\).+\(', text)[0][1:-1]
	if not cursor.execute("select * from  exam where question='"+question+"'"):
		cursor.execute("insert into  exam set question='"+question+"'")
		return 'A'
	else:
		result =cursor.fetchone()
		if 1 in result.values():
			if result['A']==1: ch='A'
			elif result['B']==1: ch='B'
			elif result['C']==1: ch='C'
			else:  ch='D'
			answer =re.findall(ch+r"\..*",text)[0].strip()[3:].strip()
			print(answer+'!!!')

			#if result['answer']: return
			
			cursor.execute("update exam set answer='"+answer+"' where question='"+question+"'")
			connection.commit()
		else:
			if result['B']==None:
				return 'B'
			elif result['C']==None:
				return 'C'
			else:
				return 'D'

		


if __name__ =='__main__':
	s =requests.Session()
	print_out =0;
#login
	data =dict( xh='2015141462109', pwd='EMC7BE')#, VerifyCode=getCode() 
	#data =dict( xh='2015141462130', pwd='RJ5NXC', VerifyCode=getCode() )
	#s.get(url='http://pead.scu.edu.cn/stu/', timeout=18)#get Cookie:_D_SID
	#s.get(url='http://pead.scu.edu.cn/stu/', timeout=18)#get Cookie:ASPSESSIONIDACBSBARR
	s.post(url='http://pead.scu.edu.cn:8080/stu/dl.asp', data=data, timeout=8)#get Cookie:_D_SID,ASPSESSIONIDACBSBARR
	

#resolv questions
	s.get(url='http://pead.scu.edu.cn:8080/stu/lllx.asp', timeout=8) #first touch will error
	collision =0;
	
	try:
		with connection.cursor() as cursor:
			while 1:#404
				r=s.get(url='http://pead.scu.edu.cn:8080/stu/lllx.asp', timeout=8)
				parse_only =SoupStrainer('span',attrs={'class':'style7'})
				span =BeautifulSoup(r.content.decode('gbk'), 'lxml', parse_only=parse_only)
				question =re.findall(r'\).+\(', span.text)[0][1:-1]
				ch =getFetchCh(cursor, span.text)
				#will be better if there is a function in getFetchCh
				if ch:
					r=s.get(url='http://pead.scu.edu.cn:8080/stu/lllx.asp?pd=1&pdr='+ch, timeout=8)
					if r.content.decode('gbk').find('正确')!=-1:
						cursor.execute("update exam set "+ch+"=1 where question='"+question+"'")
					else:
						cursor.execute("update exam set "+ch+"=0 where question='"+question+"'")
				else:
					collision +=1
					print_out=1
				if print_out==1 and float(collision)/100 == int(float(collision)/100):
					print("A collision has happened! collision=%d",collision)
					print_out =0
				connection.commit()
				#sleep(2); #2s!
	finally:
		connection.close()
#quit
	r =s.get(url='http://pead.scu.edu.cn:8080/stu/out.asp', timeout=8)

'''
#import requests
#from py.pretreat_image import downloadImg,
def getImage:
	
	s =requests.Session()
	for i in range(100):
		r =s.get(url='http://202.115.33.141/stu/count.asp', timeout=8)
		r =s.get(url='http://202.115.33.141/stu/count.asp?security_verify_data=323536302c31303830', timeout=8)
		downloadImg(s, 'http://202.115.33.141/stu/count.asp', 'img/'+str(i)+'.bmp')



def extractFingerprint:
	f =open('imgout/record.txt','w')
	image_number =0;
	all_list =[]
	for i in range(101):
		vimage =Image.open('img/'+str(i)+'.bmp')
		#vimage =vimage.resize((100,30))
		for j in range(4):
			subvimage =vimage.crop((j*10,0,j*10+10,10))
			tmp_list =getListFromImageInSimpleWay(subvimage)
			if tmp_list in  all_list:
				continue
			else:
				subvimage.save('imgout/'+str(image_number)+'.bmp')
				content =str(image_number)+' '+str(tmp_list)+'\n'
				f.write(content);f.flush()
				all_list.append(tmp_list)
				image_number +=1
	f.close()
'''

