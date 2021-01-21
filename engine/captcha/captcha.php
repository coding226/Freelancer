<?PHP
  header('Content-type: image/png');
  $image = @imagecreatetruecolor(160, 45) or die("Cannot Initialize new GD image stream");

  $background = imagecolorallocate($image, 242, 242, 242);
  imagefill($image, 0, 0, $background);
  $linecolor = imagecolorallocate($image, 165, 165, 165);
  $textcolor1 = imagecolorallocate($image, 27, 67, 186);
  $textcolor2 = imagecolorallocate($image, 0, 0, 0);

  // draw random lines on canvas
  for($i=0; $i < 8; $i++) {
    imagesetthickness($image, rand(1,3));
    imageline($image, rand(0,160), 0, rand(0,160), 45, $linecolor);
  }

  session_start();

  // using a mixture of TTF fonts
  $fonts = [];
  $fonts[] = realpath(dirname(__FILE__))."/captchaFonts/DejaVuSerif-Bold.ttf";
  $fonts[] = realpath(dirname(__FILE__))."/captchaFonts/DejaVuSans-Bold.ttf";
  $fonts[] = realpath(dirname(__FILE__))."/captchaFonts/DejaVuSansMono-Bold.ttf";

  $digit = '';

  for($x = 10; $x <= 130; $x += 30) {

    $textcolor = (rand() % 2) ? $textcolor1 : $textcolor2;
    $digit .= ($num = rand(0, 9));
    imagettftext($image, 20, rand(-30,30), $x, rand(20, 42), $textcolor, $fonts[array_rand($fonts)], $num);
  }

  $_SESSION['digit'] = $digit;
  imagepng($image);
  imagedestroy($image);
?>