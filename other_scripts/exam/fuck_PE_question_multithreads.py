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

#create database PE character set utf8 collate utf8_general_ci;
#use PE;
#create table exam( question varchar(150), A tinyint(1), B tinyint(1), C tinyint(1), D tinyint(1),answer varchar(150));




def getFetchCh(connection, text):
	with connection.cursor() as cursor:
			question =re.findall(r'\).+\(', text)[0][1:-1]
			if not cursor.execute("select * from  exam where question='"+question+"'"):
				cursor.execute("insert into  exam set question='"+question+"'")
				return 'A'
			else:
				result =cursor.fetchone()
				if 1 in result.values():
						if result['answer']: return
						if result['A']==1: ch='A'
						elif result['B']==1: ch='B'
						elif result['C']==1: ch='C'
						else:  ch='D'
						answer =re.findall(ch+r"\..*",text)[0].strip()[3:].strip()
						cursor.execute("update exam set answer='"+answer+"' where question='"+question+"'")
						connection.commit()
						return
				else:
					if result['B']==None:
						return 'B'
					elif result['C']==None:
						return 'C'
					else:
						return 'D'


def brute():
	connection =pymysql.connect(**config)
	s =requests.Session()
	print_out=0
#login
	data =dict( xh='2015141462xxx', pwd='xxxx' )
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
				ch =getFetchCh(connection, span.text)
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
					print_out=0
				connection.commit()
				#sleep(2); #2s!
	finally:
		connection.close()
#quit
	r =s.get(url='http://pead.scu.edu.cn:8080/stu/out.asp', timeout=8)

if __name__ =='__main__':
	with ThreadPoolExecutor(max_workers=15) as executor:
		futures =[]
		for i in range(15):
			futures.append( executor.submit(brute) )
	wait(futures)

