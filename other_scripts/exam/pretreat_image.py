#/usr/bin/python
#coding=utf-8

# pretreat_image.py
# 注意:二值图像0/1统一转化为0/255
from PIL import Image,ImageDraw,ImageChops

# 验证码预处理,主要是降噪
# 预处理结束后返回0/255二值图像
# 降噪,参考 http://blog.csdn.net/xinghun_4/article/details/47864949
def pretreat_image(image):
    # 将图片转换成灰度图片
    image = image.convert("L")

    # 二值化,得到0/255二值图片
    # 阀值threshold = 180
    image = iamge2imbw(image,180)

    # 对二值图片进行降噪
    # N = 4
    clear_noise(image,4)

    # 去除外边框
    # 原图大小:122*54
    # 左上右下,左 <= x < 右
    box = (   5,   5,  95,  30 )
    image = image.crop(box)
    return image

# 灰度图像二值化,返回0/255二值图像
def iamge2imbw(image,threshold):
    # 设置二值化阀值
    table = []
    for i in range(256):
        if i < threshold:
            table.append(0)
        else:
            table.append(1)

    #根据table来映射为二值图像。
    image = image.point(table,'1')
# The RGB color table actually is like this
# [0, 1, 2, 3, 4, 5, ...255, 0, 1, 2, 3, ....255, 0, 1, 2, 3, ...255]
#
# 使用color table进行颜色反转
# im = im.point(range(256, 0, -1) * 3)

    # 像素值变为0,255
# 模式“1”为二值图像，非黑即白。但是它每个像素用8个bit表示，0表示黑，255表示白。
# 模式“L”为灰色图像，它的每个像素用8个bit表示，0表示黑，255表示白，其他数字表示不同的灰度。在PIL中，从模式“RGB”转换为“L”模式是按照下面的公式转换的
# 模式“P”为8位彩色图像，它的每个像素用8个bit表示，其对应的彩色值是按照调色板查询出来的。
# 模式“RGBA”为32位彩色图像，它的每个像素用32个bit表示，其中24bit表示红色、绿色和蓝色三个通道，另外8bit表示alpha通道，即透明通道
# 还有32位浮点灰度和32位整型灰度图像
# .mode可以查看是什么模式
    image = image.convert('L')
    return image

# 缓解边界混乱的问题
# 根据一个点A的灰度值(0/255值),与周围的8个点的值比较
# 降噪率N: N=1,2,3,4,5,6,7
# 当A的值与周围8个点的相等数小于N时,此点为噪点
# 如果确认是噪声,用该点的上面一个点的值进行替换
def get_near_pixel(image,x,y,N):
    pix = image.getpixel((x,y))

    near_dots = 0
    if pix == image.getpixel((x - 1,y - 1)):
        near_dots += 1
    if pix == image.getpixel((x - 1,y)):
        near_dots += 1
    if pix == image.getpixel((x - 1,y + 1)):
        near_dots += 1
    if pix == image.getpixel((x,y - 1)):
        near_dots += 1
    if pix == image.getpixel((x,y + 1)):
        near_dots += 1
    if pix == image.getpixel((x + 1,y - 1)):
        near_dots += 1
    if pix == image.getpixel((x + 1,y)):
        near_dots += 1
    if pix == image.getpixel((x + 1,y + 1)):
        near_dots += 1

    if near_dots < N:
        # 确定是噪声,用上面一个点的值代替
        return image.getpixel((x,y-1))
    else:
        return None

# 降噪处理
def clear_noise(image,N):
    draw = ImageDraw.Draw(image)

    # 外面一层半圈像素变白色
    Width,Height=image.size
    for x in range(Width):
        draw.point((x,0),255)
        draw.point((x,Height-1),255)
    for y in range(Height):
        draw.point((0,y),255)
        draw.point((Width-1,y),255)

    # 继续绘制其余地方同时进行降噪。
    for x in range(1,Width - 1):
        for y in range(1,Height - 1): # 因为get_near_pixel总是去上面一层像素的点
            color = get_near_pixel(image,x,y,N)
            if color != None:
                draw.point((x,y),color)

def downloadImg(s, url, img_path):
	r =s.get(url=url, timeout=8)
	if(r.status_code == 200):
		f =open(img_path,'wb')
		f.write(r.content);
		f.flush()
		f.close()
	else:
		exit()

def getListFromImageInSimpleWay(image):
	tmp =[]
	for i in range(image.size[1]): #1!
		for j in range(image.size[0]): #0!
			if image.getpixel((j,i))[0]>230:
				tmp.append(1)
			else:
				tmp.append(0)
	return tmp[:]

if __name__ == '__main__':
    image = Image.open('vimage.jpeg')
    image = pretreat_image(image)
    image.show()
