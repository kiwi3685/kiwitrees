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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

/**
 * this function is a wrapper to the php mail() function so that we can change settings globally
 * for more info on format="flowed" see: http://www.joeclark.org/ffaq.html
 * for deatiled info on MIME (RFC 1521) email see: http://www.freesoft.org/CIE/RFC/1521/index.htm
 */
function kiwiMail($to, $from, $subject, $message) {
	$SMTP_ACTIVE   =WT_Site::preference('SMTP_ACTIVE');
	$SMTP_HOST     =WT_Site::preference('SMTP_HOST');
	$SMTP_HELO     =WT_Site::preference('SMTP_HELO');
	$SMTP_FROM_NAME=WT_Site::preference('SMTP_FROM_NAME');
	$SMTP_PORT     =WT_Site::preference('SMTP_PORT');
	$SMTP_AUTH     =WT_Site::preference('SMTP_AUTH');
	$SMTP_AUTH_USER=WT_Site::preference('SMTP_AUTH_USER');
	$SMTP_AUTH_PASS=WT_Site::preference('SMTP_AUTH_PASS');
	$SMTP_SSL      =WT_Site::preference('SMTP_SSL');
	$MAIL_FORMAT   =WT_Site::preference('MAIL_FORMAT');
	global $TEXT_DIRECTION;

	//$mailFormat = "plain";
	//$mailFormat = "html";
	if ($MAIL_FORMAT == "1") {
		$mailFormat = "multipart";
	} else {
		$mailFormat = "plain";
	}

	$mailFormatText = "text/plain";

	$boundary = "WT-123454321-WT"; //unique identifier for multipart
	$boundary2 = "WT-123454321-WT2";

	if ($TEXT_DIRECTION == "rtl") { // needed for rtl but we can change this to a global config
		$mailFormat = "html";
	}

	if ($mailFormat == "html") {
		$mailFormatText = "text/html";
	} else if ($mailFormat == "multipart") {
		$mailFormatText = "multipart/related; \n\tboundary=\"$boundary\""; //for double display use:multipart/mixed
	} else {
		$mailFormatText = "text/plain";
	}

	$extraHeaders = "From: $from\nContent-type: $mailFormatText;";

	if ($mailFormat != "multipart") {
		$extraHeaders .= "\tcharset=\"UTF-8\";\tformat=\"flowed\"\nContent-Transfer-Encoding: 8bit";
	}

	if ($mailFormat == "html" || $mailFormat == "multipart") {
		$extraHeaders .= "\nMime-Version: 1.0";
	}

	$extraHeaders .= "\n";


	if ($mailFormat == "html") {
		//wrap message in html
		$htmlMessage = "";
		$htmlMessage .= "<!DOCTYPE html>";
		$htmlMessage .= "<html ".WT_I18N::html_markup().">";
		$htmlMessage .= "<head>";
		$htmlMessage .= '<meta charset="UTF-8">';
		$htmlMessage .= "</head>";
		$htmlMessage .= "<body dir=\"$TEXT_DIRECTION\"><pre>";
		$htmlMessage .= $message; //add message
		$htmlMessage .= "</pre></body>";
		$htmlMessage .= "</html>";
		$message = $htmlMessage;
	} else if ($mailFormat == "multipart") {
		//wrap message in html
		$htmlMessage = "--$boundary\n";
		$htmlMessage .= "Content-Type: multipart/alternative; \n\tboundary=--$boundary2\n\n";
		$htmlMessage = "--$boundary2\n";
		$htmlMessage .= "Content-Type: text/plain; \n\tcharset=\"UTF-8\";\n\tformat=\"flowed\"\nContent-Transfer-Encoding: 8bit\n\n";
		$htmlMessage .= $message;
		$htmlMessage .= "\n\n--$boundary2\n";
		$htmlMessage .= "Content-Type: text/html; \n\tcharset=\"UTF-8\";\n\tformat=\"flowed\"\nContent-Transfer-Encoding: 8bit\n\n";
		$htmlMessage .= "<!DOCTYPE html>";
		$htmlMessage .= "<html ".WT_I18N::html_markup().">";
		$htmlMessage .= "<head>";
		$htmlMessage .= '<meta charset="UTF-8">';
		$htmlMessage .= "</head>";
		$htmlMessage .= "<body dir=\"$TEXT_DIRECTION\"><pre>";
		$htmlMessage .= $message; //add message
		$htmlMessage .= "</pre>";
		$htmlMessage .= "<img src=\"cid:ktlogo@ktserver\" alt=\"\" style=\"border: 0px; display: block; margin-left: auto; margin-right: auto;\">";
		$htmlMessage .= "</body>";
		$htmlMessage .= "</html>";
		$htmlMessage .= "\n--$boundary2--\n";
		$htmlMessage .= "\n--$boundary\n";
		$htmlMessage .= getKTMailLogo();
		$htmlMessage .= "\n\n\n\n--$boundary--";
		$message = $htmlMessage;
	}
	// if SMTP mail is set active AND we have SMTP settings available, use the PHPMailer classes
	if ($SMTP_ACTIVE=='external'  && ( $SMTP_HOST && $SMTP_PORT ) ) {
		require_once WT_ROOT.'library/phpmailer/class.phpmailer.php';
		$mail_object = new PHPMailer();
		$mail_object->IsSMTP();
		$mail_object->SetLanguage(WT_LOCALE, WT_ROOT.'library/phpmailer/language/');
		if ($SMTP_AUTH && $SMTP_AUTH_USER && $SMTP_AUTH_PASS) {
			$mail_object->SMTPAuth = $SMTP_AUTH;
			$mail_object->Username = $SMTP_AUTH_USER;
			$mail_object->Password = $SMTP_AUTH_PASS;
		}
		$mail_object->Host = $SMTP_HOST;
		$mail_object->Port = $SMTP_PORT;
		$mail_object->Hostname = $SMTP_HELO;
		if ($SMTP_SSL=='ssl') {
			$mail_object->SMTPSecure = 'ssl';
		} else if ($SMTP_SSL=='tls') {
			$mail_object->SMTPSecure = 'tls';
		}
		$from_name = '';
		preg_match('/<(.*)>/', $to, $matches);
		if (isset($matches[1])) $to = $matches[1];
		preg_match('/<(.*)>/', $from, $matches);
		if (isset($matches[1])) {
			if (($pos = strpos($from, '<')) !== false) $from_name = substr($from, 0, $pos);
			$from = $matches[1];
		}
		$mail_object->SetFrom($from, $from_name);
		if ((!empty($SMTP_FROM_NAME) && $from!=$SMTP_AUTH_USER) || !empty($from_name)) {
			if (!empty($from_name)) {
				$mail_object->FromName = $from_name.' - '.$SMTP_FROM_NAME;
			} else {
				$mail_object->FromName = $SMTP_FROM_NAME;
			}
			$mail_object->AddAddress($to);
		} else if (!empty($from_name)) {
			$mail_object->FromName = $from_name;
		} else {
			$mail_object->FromName = $mail_object->AddAddress($to);
		}
		$mail_object->Subject = hex4email( $subject, 'UTF-8');
		$mail_object->ContentType = $mailFormatText;
		if ($mailFormat!="multipart") {
			$mail_object->ContentType = $mailFormatText . '; format="flowed"';
			$mail_object->CharSet = 'UTF-8';
			$mail_object->Encoding = '8bit';
		}
		if ($mailFormat == "html" || $mailFormat == "multipart") {
			$mail_object->AddCustomHeader( 'Mime-Version: 1.0' );
			$mail_object->IsHTML(true);
		}
		$mail_object->Body = $message;
		// attempt to send mail
		if (!$mail_object->Send()) {
			echo WT_I18N::translate('Message was not sent'), '<br>';
			echo /* I18N: %s is an error message */ WT_I18N::translate('Mailer error: %s',  $mail_object->ErrorInfo), '<br>';
			return false;
		} else {
			// SMTP OK
			return true;
		}
	} elseif ($SMTP_ACTIVE=='internal') {
		// use original PHP mail sending function
		if (!mail($to, hex4email($subject, 'UTF-8'), $message, $extraHeaders)) {
			echo WT_I18N::translate('Message was not sent'), '<br>';
			echo WT_I18N::translate('Mailer error: %s', 'PHP mail() failed'), '<br>';
			return false;
		} else {
			// original PHP mail sending function OK
			return true;
		}
	}
}

function getKTMailLogo() {
// the following is a base64 encoded kiwitrees logo for use in html formatted email.
	$wtLogo =
"Content-Type: image/png;
	name=\"kiwitrees.png\"
Content-Transfer-Encoding: base64
Content-ID: <ktlogo@ktserver>
Content-Description: kiwitrees.png
Content-Location: kiwitrees.png

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";

return $wtLogo;
}

/**
 * hex encode a string
 *
 * this function encodes a string in quoted_printable format
 * found at http://us3.php.net/bin2hex
 */
function hex4email($string, $charset) {
	//-- check if the string has extended characters in it
	$str = utf8_decode($string);
	//-- if the strings are the same no conversion is necessary
	if ($str==$string) return $string;
	//-- convert to string into quoted_printable format
	$string = bin2hex ($string);
	$encoded = chunk_split($string, 2, '=');
	$encoded = preg_replace ("/=$/","",$encoded);
	$string = "=?$charset?Q?=" . $encoded . "?=";
	return $string;
}


function RFC2047Encode($string, $charset) {
	if (preg_match('/[^a-z ]/i', $string)) {
		$string = preg_replace('/([^a-z ])/ie', 'sprintf("=%02x", ord(StripSlashes("\\1")))', $string);
		$string = str_replace(' ', '_', $string);
		return "=?$charset?Q?$string?=";
	}
}
