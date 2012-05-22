<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2012 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: antibot.php
-----------------------------------------------------
 Назначение: антибот
=====================================================
*/
// ---------- ---------- ---------- ---------- ----------
// Automatic test to tell computers and humans apart
// Copyright by Kruglov Sergei, 2006
// www.captcha.ru, www.kruglov.ru
// ---------- ---------- ---------- ---------- ----------

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

function clean_url ($url) {

  if ($url == '') return;

  $url = str_replace("http://", "", strtolower($url));
  $url = str_replace("https://", "", $url );
  if (substr($url, 0, 4) == 'www.')  $url = substr($url, 4);
  $url = explode('/', $url);
  $url = reset($url);
  $url = explode(':', $url);
  $url = reset($url);

  return $url;
}

if (clean_url($_SERVER['HTTP_REFERER']) != clean_url($_SERVER['HTTP_HOST'])) die("Hacking attempt!");

class genrandomimage {
	
	// Переменные настройки скрипта ---------
	
	var $alphabet = "0123456789abcdefghijklmnopqrstuvwxyz"; // НЕ ИЗМЕНЯЙТЕ, если вы не изменяли файл шрифтов!
	// последовательность букв должна СОВПАДАТЬ!
	
	// папка с шрифтами
	var $fontsdir = 'fonts';	
	
	// размер изображения CAPTCHA (оптимальные параметры)
	var $width = 120;
	var $height = 50;
	
	// амплитуда вертикальной флуктуации символов, деленная на 2
	var $fluctuation_amplitude = 7;
	
	// для увеличения безопасности можно убрать пробелы между символами
	var $no_spaces = true;
	
	// качество JPEG-изображения CAPTCHA (чем больше, тем выше качество)
	var $jpeg_quality = 90; // максимальное, можно поставить 70-80
	
	var $keystring = ''; // ключевая генерируемая строка

	// символы, используемые для рисования сгенерированного изображения CAPTCHA	
	var $allowed_symbols = "23456789acdefhkmnprsuvwxyz"; // алфавит БЕЗ похожих символов (o=0, 1=l, i=j, t=f)
	
	// количество символов в строке
	var $length_min = 5; // минимальное
	var $length_max = 6; // максимальное
	var $length = 0; // длина будет сгенерирована
	
	// ---------- ---------- ---------- ---------- ----------
	
	// Генерация строки ---------- ---------- -------
	function genstring() {
		// длина строки (количество символов) CAPTCHA
		// случайное число в пределах от $length_min до $length_max включительно
		$length = mt_rand( $this->length_min, $this->length_max );
		$this->length = $length;
		
		$this->keystring = '';
		for ($i = 0; $i < $length ; $i++) {
			// в цикле добавляем к строке по 1 случайному символу
			$this->keystring .= $this->allowed_symbols{ mt_rand( 0, strlen( $this->allowed_symbols ) -1 ) };
		}

	}
	// ---------- ---------- ---------- ---------- ----------

	// Генерация изображения
	function genimage() {

		// цвета изображения CAPTCHA (RGB, 0-255)
		$foreground_color = array( mt_rand( 0, 100 ), mt_rand( 0, 100 ), mt_rand( 0, 100 ) );
		$background_color = array( mt_rand( 200, 255 ), mt_rand( 200, 255 ), mt_rand( 200, 255 ) ); // фон всегда светлее

		$fonts = array();
		$fontsdir_absolute = dirname( __FILE__ ).'/'.$this->fontsdir; // путь к папке с шрифтами

		if ($handle = opendir( $fontsdir_absolute )) { // создаем массив с полными путями к изображениям с шрифтами
			while (false !== ($file = readdir( $handle ))) {
				if (preg_match( '/\.png$/i', $file )) {
					$fonts[] = $fontsdir_absolute.'/'.$file;
				}
			}
		    closedir( $handle );
		}

		$alphabet_length = strlen( $this->alphabet );
		
		while (true) {
			$font_file = $fonts[mt_rand( 0, count( $fonts ) - 1 )]; // выбираем случайный файл шрифта
			$font = imagecreatefrompng( $font_file );
			$black = imagecolorallocate( $font, 0, 0, 0 );
			$fontfile_width = imagesx( $font );
			$fontfile_height = imagesy( $font ) - 1;
			$font_metrics = array();
			$symbol = 0;
			$reading_symbol = false;

			// loading font
			for ($i = 0; $i < $fontfile_width && $symbol < $alphabet_length; $i++) {
				$transparent = (imagecolorat( $font, $i, 0 ) >> 24) == 127;

				if (!$reading_symbol && !$transparent) {
					$font_metrics[$this->alphabet{$symbol}] = array( 'start' => $i );
					$reading_symbol = true;
					continue;
				}

				if ($reading_symbol && $transparent) {
					$font_metrics[$this->alphabet{$symbol}]['end'] = $i;
					$reading_symbol = false;
					$symbol++;
					continue;
				}
			}

			$img = imagecreatetruecolor( $this->width, $this->height );

			$white = imagecolorallocate( $img, 255, 255, 255 );
			$black = imagecolorallocate( $img, 0, 0, 0 );

			imagefilledrectangle( $img, 0, 0, $this->width - 1, $this->height - 1, $white );

			// draw text
			$x = 1;
			$shift = 0;
			
			for ($i = 0; $i < $this->length; $i++) {
				$m = $font_metrics[$this->keystring{$i}];

				$y = mt_rand( -$this->fluctuation_amplitude, $this->fluctuation_amplitude ) + ($this->height - $fontfile_height) / 2 + 2;
				

				if ($this->no_spaces) {
					$shift = 0;
					if ($i > 0) {
						$shift = 1000;
						for ($sy = 1; $sy < $fontfile_height - 15; $sy += 2) {
							for ($sx = $m['start'] - 1; $sx < $m['end']; $sx++) {
								$rgb = imagecolorat( $font, $sx, $sy );
								$opacity = $rgb >> 24;
								if ($opacity < 127) {
									$left = $sx - $m['start'] + $x;
									$py = $sy + $y;
									for ($px = min( $left, $this->width - 1 ); $px > $left - 15 && $px >= 0; $px--) {
										$color = imagecolorat( $img, $px, $py ) & 0xff;
										if ($color + $opacity < 190) {
											if ($shift > $left-$px) {
												$shift = $left - $px;
											}
											break;
										}
									}
									break;
								}
							}
						}
					}
				} else {
					$shift = 1;
				}
				
				imagecopy( $img, $font, $x - $shift, $y, $m['start'], 1, $m['end'] - $m['start'], $fontfile_height );
				
				$x += $m['end'] - $m['start'] - $shift;
			}
			if ($x < $this->width - 10) break; // fit in canvas
		}
		$center = $x/2;
		
		$img2=imagecreatetruecolor($this->width, $this->height);
		$foreground=imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background=imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, $this->height, $this->width, $this->height+12, $foreground);


		// periods
		$rand1 = mt_rand( 750000, 1200000 ) / 10000000;
		$rand2 = mt_rand( 750000, 1200000 ) / 10000000;
		$rand3 = mt_rand( 750000, 1200000 ) / 10000000;
		$rand4 = mt_rand( 750000, 1200000 ) / 10000000;
		// phases
		$rand5 = mt_rand( 0, 3141592 ) / 500000;
		$rand6 = mt_rand( 0, 3141592 ) / 500000;
		$rand7 = mt_rand( 0, 3141592 ) / 500000;
		$rand8 = mt_rand( 0, 3141592 ) / 500000;
		// amplitudes
		$rand9 = mt_rand( 330, 420 ) / 110;
		$rand10 = mt_rand(330, 450 ) / 110;

		//wave distortion
		for ($x = 0; $x < $this->width; $x++) {
			for ($y = 0; $y < $this->height; $y++) {
				$sx = $x + (sin( $x * $rand1 + $rand5 ) + sin( $y * $rand3 + $rand6 )) * $rand9 - $this->width / 2 + $center + 1;
				$sy = $y + (sin( $x * $rand2 + $rand7 ) + sin( $y * $rand4 + $rand8 )) * $rand10;

				if ($sx < 0 || $sy < 0 || $sx >= $this->width - 1 || $sy >= $this->height - 1) {
					$color = 255;
					$color_x = 255;
					$color_y = 255;
					$color_xy = 255;
				} else {
					$color = imagecolorat( $img, $sx, $sy ) & 0xFF;
					$color_x = imagecolorat( $img, $sx + 1, $sy ) & 0xFF;
					$color_y = imagecolorat( $img, $sx, $sy + 1 ) & 0xFF;
					$color_xy = imagecolorat( $img, $sx + 1, $sy + 1 ) & 0xFF;
				}

				if ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
					$newred = $foreground_color[0];
					$newgreen = $foreground_color[1];
					$newblue = $foreground_color[2];
				} else if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
					$newred = $background_color[0];
					$newgreen = $background_color[1];
					$newblue = $background_color[2];	
				} else {
					$frsx = $sx - floor( $sx );
					$frsy = $sy - floor( $sy );
					$frsx1 = 1 - $frsx;
					$frsy1 = 1 - $frsy;
					$newcolor = (
						$color    * $frsx1 * $frsy1 +
						$color_x  * $frsx  * $frsy1 +
						$color_y  * $frsx1 * $frsy  +
						$color_xy * $frsx  * $frsy);

					if ($newcolor > 255) $newcolor = 255;
					$newcolor = $newcolor / 255;
					$newcolor0 = 1 - $newcolor;

					$newred	  = $newcolor0 * $foreground_color[0] + $newcolor * $background_color[0];
					$newgreen = $newcolor0 * $foreground_color[1] + $newcolor * $background_color[1];
					$newblue  = $newcolor0 * $foreground_color[2] + $newcolor * $background_color[2];
				}

				imagesetpixel( $img2, $x, $y, imagecolorallocate( $img2, $newred, $newgreen, $newblue ) );
			}
		}

      # Рамка
      imageline( $img2, 0, 0,  $this->width, 0, $foreground );
      imageline( $img2, 0, 0,  0, $this->height, $foreground );

      imageline( $img2, 0, $this->height-1,  $this->width, $this->height-1, $foreground );
      imageline( $img2, $this->width-1, 0,  $this->width-1, $this->height, $foreground);

		header( "Expires: Tue, 11 Jun 1985 05:00:00 GMT" );
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );
		header( "Content-Type: image/jpeg" );
		imagejpeg($img2, null, $this->jpeg_quality);
	}
	// ---------- ---------- ---------- ---------- ----------
}

@session_start();

$im = new genrandomimage();
$im->genstring();

$_SESSION['sec_code_session'] = $im->keystring;

$im->genimage();
?>