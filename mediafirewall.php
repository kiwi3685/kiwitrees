<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'mediafirewall.php');
require './includes/session.php';

Zend_Session::writeClose();

$mid   = KT_Filter::get('mid', KT_REGEX_XREF);
$thumb = KT_Filter::getBool('thumb');
$media = KT_Media::getInstance($mid, KT_GED_ID);

/**
 * Send a “Not found” error as an image
 */
function send404AndExit() {
	$error = KT_I18N::translate('The media file was not found in this family tree.');

	$width  = mb_strlen($error) * 6.5 + 50;
	$height = 60;
	$im  = imagecreatetruecolor($width, $height);  /* Create a black image */
	$bgc = imagecolorallocate($im, 255, 255, 255); /* set background color */
	imagefilledrectangle($im, 2, 2, $width-4, $height-4, $bgc); /* create a rectangle, leaving 2 px border */

	embedText($im, $error, 100, '255, 0, 0', KT_FONT_DEJAVU_SANS_TTF, 'top', 'left');

	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
	header('Content-Type: image/png');
	imagepng($im);
	imagedestroy($im);
}

/**
 * The media firewall passes in an image
 * this function can manipulate the image however it wants
 * before returning it back to the media firewall
 *
 * @param resource $im
 * @param Tree     KT_GED_ID
 *
 * @return resource
 */
function applyWatermark($im) {
	// text to watermark with
	$word1_text   = KT_TREE_TITLE;
	// maximum font size for “word1” ; will be automaticaly reduced to fit in the image
	$word1_maxsize = 100;
	// rgb color codes for text
	$word1_color = '0,0,0';
	// ttf font file to use
	$word1_font   = KT_FONT_DEJAVU_SANS_TTF;
	// vertical position for the text to past; possible values are: top, middle or bottom, across
	$word1_vpos = 'across';
	// horizontal position for the text to past in media file; possible values are: left, right, top2bottom, bottom2top
	// this value is used only if $word1_vpos=across
	$word1_hpos = 'left';

	$word2_text    = $_SERVER['HTTP_HOST'];
	$word2_maxsize = 20;
	$word2_color   = '0,0,0';
	$word2_font   = KT_FONT_DEJAVU_SANS_TTF;
	$word2_vpos    = 'top';
	$word2_hpos    = 'top2bottom';

	embedText($im, $word1_text, $word1_maxsize, $word1_color, $word1_font, $word1_vpos, $word1_hpos);
	embedText($im, $word2_text, $word2_maxsize, $word2_color, $word2_font, $word2_vpos, $word2_hpos);

	return $im;
}

/**
 * Embed text into an image.
 *
 * @param resource $im
 * @param string   $text
 * @param int      $maxsize
 * @param string   $color
 * @param string   $font
 * @param string   $vpos
 * @param string   $hpos
 */
function embedText($im, $text, $maxsize, $color, $font, $vpos, $hpos) {
	global $useTTF;

	// there are two ways to embed text with PHP
	// (preferred) using GD and FreeType you can embed text using any True Type font
	// (fall back) if that is not available, you can insert basic monospaced text

	$col       = explode(',', $color);
	$textcolor = imagecolorallocate($im, $col[0], $col[1], $col[2]);

	// make adjustments to settings that imagestring and imagestringup can’t handle
	if (!$useTTF) {
		// imagestringup only writes up, can’t use top2bottom
		if ($hpos === 'top2bottom') {
			$hpos = 'bottom2top';
		}
	}

	$text = reverseText($text);
	$height = imagesy($im);
	$width  = imagesx($im);
	$calc_angle=rad2deg(atan($height/$width));
	$hypoth=$height/sin(deg2rad($calc_angle));

	// vertical and horizontal position of the text
	switch ($vpos) {
	default:
	case 'top':
			$taille=textlength($maxsize, $width, $text);
			$pos_y=$height*0.15+$taille;
			$pos_x=$width*0.15;
			$rotation=0;
			break;
	case 'middle':
			$taille=textlength($maxsize, $width, $text);
			$pos_y=($height+$taille)/2;
			$pos_x=$width*0.15;
			$rotation=0;
			break;
	case 'bottom':
			$taille=textlength($maxsize, $width, $text);
			$pos_y=($height*.85-$taille);
			$pos_x=$width*0.15;
			$rotation=0;
			break;
	case 'across':
			switch ($hpos) {
		default:
		case 'left':
				$taille=textlength($maxsize, $hypoth, $text);
				$pos_y=($height*.85-$taille);
				$pos_x=$width*0.15;
				$rotation=$calc_angle;
				break;
		case 'right':
				$taille=textlength($maxsize, $hypoth, $text);
				$pos_y=($height*.15-$taille);
				$pos_x=$width*0.85;
				$rotation=$calc_angle+180;
				break;
		case 'top2bottom':
				$taille=textlength($maxsize, $height, $text);
				$pos_y=($height*.15-$taille);
				$pos_x=($width*.90-$taille);
				$rotation=-90;
				break;
		case 'bottom2top':
				$taille=textlength($maxsize, $height, $text);
				$pos_y = $height*0.85;
				$pos_x = $width*0.15;
				$rotation=90;
				break;
			}
			break;
	}

	// apply the text
	if ($useTTF) {
		// if imagettftext throws errors, catch them with a custom error handler
		set_error_handler('imagettftextErrorHandler');
		imagettftext($im, $taille, $rotation, $pos_x, $pos_y, $textcolor, $font, $text);
		restore_error_handler();
	}
	// Don’t use an ‘else’ here since imagettftextErrorHandler may have changed the value of $useTTF from true to false
	if (!$useTTF) {
		if ($rotation !== 90) {
			imagestring($im, 5, $pos_x, $pos_y, $text, $textcolor);
		} else {
			imagestringup($im, 5, $pos_x, $pos_y, $text, $textcolor);
		}
	}
}

/**
 * Generate an approximate length of text, in pixels.
 *
 * @param int    $t
 * @param int    $mxl
 * @param string $text
 *
 * @return int
 */
function textlength($t, $mxl, $text) {
	$taille_c = $t;
	$len      = mb_strlen($text);
	while (($taille_c - 2) * $len > $mxl) {
		$taille_c--;
		if ($taille_c == 2) {
			break;
		}
	}

	return $taille_c;
}

/**
 * imagettftext is the function that is most likely to throw an error
 * use this custom error handler to catch and log it
 *
 * @param int    $errno
 * @param string $errstr
 *
 * @return bool
 */
function imagettftextErrorHandler($errno, $errstr) {
	global $useTTF, $serverFilename;
	// log the error
	AddToLog('Media Firewall error: >' . $errno . '/' . $errstr . '< while processing file >' . $serverFilename . '<', 'error');

	// change value of useTTF to false so the fallback watermarking can be used.
	$useTTF = false;

	return true;
}

/**
 * Determine if the system supports editing of that image type
 *
 * @param string $reqtype
 *
 * @return string|false
 */
function isImageTypeSupported($reqtype) {
	$supportByGD = array('jpg'=>'jpeg', 'jpeg'=>'jpeg', 'gif'=>'gif', 'png'=>'png');
	$reqtype = strtolower($reqtype);

	if (empty($supportByGD[$reqtype])) {
		return false;
	}
	$type = $supportByGD[$reqtype];

	if (function_exists('imagecreatefrom' . $type) && function_exists('image' . $type)) {
		return $type;
	}

	return false;
}

// this needs to be a global variable so imagettftextErrorHandler can set it
$useTTF = function_exists('imagettftext');

// Media object missing/private?
if (!$media || !$media->canDisplayDetails()) {
	send404AndExit();
}
// media file somewhere else?
if ($media->isExternal()) {
	header('Location: ' . $media->getFilename());

	return;
}

$which = $thumb ? 'thumb' : 'main';
$serverFilename = $media->getServerFilename($which);

if (!file_exists($serverFilename)) {
	send404AndExit();

	return;
}

$mimetype = $media->mimeType();
$imgsize = $media->getImageAttributes($which);
$protocol = $_SERVER["SERVER_PROTOCOL"];  // determine if we are using HTTP/1.0 or HTTP/1.1
$filetime = $media->getFiletime($which);
$filetimeHeader = gmdate('D, d M Y H:i:s', $filetime) . ' GMT';
$expireOffset = 3600 * 24;  // tell browser to cache this image for 24 hours
if (KT_Filter::get('cb')) {
	$expireOffset = $expireOffset * 7;
} // if cb parameter was sent, cache for 7 days
$expireHeader = gmdate('D, d M Y H:i:s', KT_TIMESTAMP + $expireOffset) . ' GMT';

$type = isImageTypeSupported($imgsize['ext']);
$usewatermark = false;
// if this image supports watermarks and the watermark module is intalled...
if ($type) {
	// if this is not a thumbnail, or WATERMARK_THUMB is true
	if (($which === 'main') || $WATERMARK_THUMB ) {
		// if the user’s priv’s justify it...
		if (KT_USER_ACCESS_LEVEL > $SHOW_NO_WATERMARK ) {
			// add a watermark
			$usewatermark = true;
		}
	}
}

// determine whether we have enough memory to watermark this image
if ($usewatermark) {
	if (!hasMemoryForImage($serverFilename)) {
		// not enough memory to watermark this file
		$usewatermark = false;
	}
}

$watermarkfile     = '';
$generatewatermark	= false;

if ($usewatermark) {
	if ($which == 'thumb') {
		$watermarkfile = KT_DATA_DIR . 'media_watermarks_cache_' . KT_GEDCOM . '/thumb/' . $media->getFilename();
	} else {
		$watermarkfile = KT_DATA_DIR . 'media_watermarks_cache_' . KT_GEDCOM . '/main/' . $media->getFilename();
	}

	if (!file_exists($watermarkfile)) {
		// no saved watermark file exists
		// generate the watermark file
		$generatewatermark = true;
	} else {
		$watermarktime = filemtime($watermarkfile);
		if ($filetime > $watermarktime) {
			// if the original image was updated after the saved file was created
			// generate the watermark file
			$generatewatermark = true;
		}
	}
}

$etag = $media->getEtag($which);

// parse IF_MODIFIED_SINCE header from client
$if_modified_since = 'x';
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
}

// parse IF_NONE_MATCH header from client
$if_none_match = 'x';
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	$if_none_match = str_replace('"', '', $_SERVER['HTTP_IF_NONE_MATCH']);
}

// add caching headers.  allow browser to cache file, but not proxy
header('Last-Modified: ' . $filetimeHeader);
header('ETag: "'.$etag.'"');
header('Expires: ' . $expireHeader);
header('Cache-Control: max-age=' . $expireOffset . ', s-maxage=0, proxy-revalidate');

// if this file is already in the user’s cache, don’t resend it
// first check if the if_modified_since param matches
if ($if_modified_since === $filetimeHeader) {
	// then check if the etag matches
	if ($if_none_match === $etag) {
		header($protocol." 304 Not Modified");

		return;
	}
}

// send headers for the image
header('Content-Type: ' . $mimetype);
header('Content-Disposition: filename="' . addslashes(basename($media->getFilename())) . '"');

if ($generatewatermark) {
	// generate the watermarked image
	$imCreateFunc = 'imagecreatefrom'.$type;
	$imSendFunc   = 'image' . $type;

	if (function_exists($imCreateFunc) && function_exists($imSendFunc)) {
		$im = $imCreateFunc($serverFilename);
		$im = applyWatermark($im);

		// save the image, if preferences allow
		if ((($which=='thumb') && $SAVE_WATERMARK_THUMB) || (($which=='main') && $SAVE_WATERMARK_IMAGE)) {
			// make sure the folder exists
			if (!is_dir(dirname($watermarkfile))) {
				KT_File::mkdir(dirname($watermarkfile), KT_PERM_EXE, true);
			}
			// save the image
			$imSendFunc($im, $watermarkfile);
		}
		if ($which === 'thumb' && $SAVE_WATERMARK_THUMB || $which === 'main' && $SAVE_WATERMARK_IMAGE) {
			// make sure the folder exists
			KT_File::mkdir(dirname($watermarkfile));
			// save the image
			$imSendFunc($im, $watermarkfile);
		}

		// send the image
		$imSendFunc($im);
		imagedestroy($im);

		return;
	} else {
		// this image is defective.  log it
		AddToLog('Media Firewall error: >' . KT_I18N::translate('This media file is broken and cannot be watermarked.') . '< in file >' . $serverFilename . '< memory used: ' . memory_get_usage(), 'media');

		// set usewatermark to false so image will simply be passed through below
		$usewatermark = false;
	}
}

// pass the image through without manipulating it

if ($usewatermark) {
	// the stored watermarked image is good, lets use it
	$serverFilename = $watermarkfile;
}

// determine filesize of image (could be original or watermarked version)
$filesize = filesize($serverFilename);

// set content-length header, send file
header("Content-Length: " . $filesize);

// Some servers disable fpassthru() and readfile()
if (function_exists('readfile')) {
	readfile($serverFilename);
} else {
	$fp=fopen($serverFilename, 'rb');
	if (function_exists('fpassthru')) {
		fpassthru($fp);
	} else {
		while (!feof($fp)) {
			echo fread($fp, 65536);
		}
	}
	fclose($fp);
}
