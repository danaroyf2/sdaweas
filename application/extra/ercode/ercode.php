<?php
namespace app\extra\ercode;
include 'phpqrcode.php';
class ercode{
    
    public function createErCode($url,$qrpinname='')
    {

$value =$url;
$errorCorrectionLevel = 'L';//容错级别

$matrixPointSize = 6;//生成图片大小

//生成二维码图片

QRcode::png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 6);

$logo = 'https://tanyue.gitee.io/my-tools/image/pay.jpg';//准备好的logo图片

$QR = 'qrcode.png';//已经生成的原始二维码图



if ($logo !== FALSE) {

    $QR = imagecreatefromstring(file_get_contents($QR));

    $logo = imagecreatefromstring(file_get_contents($logo));
    if (imageistruecolor($logo)) { //添加
      imagetruecolortopalette($logo, false, 65535);//添加这行代码来解决颜色失真问题
    }
   

    $QR_width = imagesx($QR);//二维码图片宽度

    $QR_height = imagesy($QR);//二维码图片高度

    $logo_width = imagesx($logo);//logo图片宽度

    $logo_height = imagesy($logo);//logo图片高度

    $logo_qr_width = $QR_width / 5;

    $scale = $logo_width/$logo_qr_width;

    $logo_qr_height = $logo_height/$scale;

    $from_width = ($QR_width - $logo_qr_width) / 2;

//重新组合图片并调整大小

    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,

        $logo_qr_height, $logo_width, $logo_height);

}

//输出图片
header('Content-Type: image/png');

imagepng($QR);

//echo '<img src="/helloweba.png">';
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public static function createErCode2($url,$qrpinname='',$logo='')
    {

$value =$url;
$errorCorrectionLevel = 'L';//容错级别

$matrixPointSize = 6;//生成图片大小

//生成二维码图片
$QR = 'qrcode.png';//已经生成的原始二维码图


QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize, 1);

if(empty($logo)){
    $logo = 'https://tanyue.gitee.io/my-tools/image/pay.jpg';//准备好的logo图片
}






if ($logo !== FALSE) {

    $QR = imagecreatefromstring(file_get_contents($QR));

    $logo = imagecreatefromstring(file_get_contents($logo));
    if (imageistruecolor($logo)) { //添加
      imagetruecolortopalette($logo, false, 65535);//添加这行代码来解决颜色失真问题
    }

    $QR_width = imagesx($QR);//二维码图片宽度

    $QR_height = imagesy($QR);//二维码图片高度

    $logo_width = imagesx($logo);//logo图片宽度

    //$logo_height = imagesy($logo);//logo图片高度
    $logo_height = imagesx($logo);//logo图片高度

    $logo_qr_width = $QR_width / 5;

    $scale = $logo_width/$logo_qr_width;

    $logo_qr_height = $logo_height/$scale;

    $from_width = ($QR_width - $logo_qr_width) / 2;

//重新组合图片并调整大小

    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,

        $logo_qr_height, $logo_width, $logo_height);

}

//输出图片
if(empty($qrpinname)){
    $qrpinname='qrcoe/qr'.time().'.png';
}

imagepng($QR, $qrpinname);
$qrcode_path='/'.$qrpinname;
    //echo '<img src="/'.$ername.'">';
    return $qrcode_path;
    }
    
    
}
?>