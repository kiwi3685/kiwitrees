<?php
// Mail specific functions
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
//
// Modifications Copyright (c) 2010 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

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
		$htmlMessage .= "<img src=\"cid:ktlogo@wtserver\" alt=\"\" style=\"border: 0px; display: block; margin-left: auto; margin-right: auto;\">";
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
Content-ID: <ktlogo@wtserver>
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
