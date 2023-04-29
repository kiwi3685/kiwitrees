<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'message.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_mail.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Contact us'))
	->addInlineJavascript('
		jQuery("#contact_page div.option label[for=termsConditions]").parent().css({
			"opacity": "0",
			"position": "absolute",
			"left": "-2000px",
		});
	');

if (array_key_exists('ckeditor', KT_Module::getActiveModules()) && KT_Site::preference('MAIL_FORMAT') == "1") {
	ckeditor_KT_Module::enableBasicEditor($controller);
}

// Send the message.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$to					= KT_Filter::post('to', null, KT_Filter::get('to'));
	$from_name			= KT_Filter::post('from_name');
	$from_email			= KT_Filter::post('from_email');
	$subject			= KT_Filter::post('subject', null, KT_Filter::get('subject'));
	$body				= KT_Filter::post('body');
	$url				= KT_Filter::postUrl('url', KT_Filter::getUrl('url'));
	$termsConditions	= KT_Filter::post('termsConditions', '1', '0');

	// Only an administration can use the distribution lists.
	$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);

	$recipients = recipients($to);

	// Different validation for admin/user/visitor.
	$errors		= false;
	$urlRegex	= '/(?!' . preg_quote(KT_SERVER_NAME, '/') . ')((?:ftp|http|https|www|\:|\/\/)?(?>[a-z\-0-9]{1,}\.){1,}[a-z]{2,8})/m';

	if (KT_USER_ID) {
		$from_name  = getUserFullName(KT_USER_ID);
		$from_email = getUserEmail(KT_USER_ID);
	} elseif ($from_name === '' || $from_email === '') {
		$errors = true;
	} elseif (!preg_match('/@(.+)/', $from_email, $match) || function_exists('checkdnsrr') && !checkdnsrr($match[1])) {
		KT_FlashMessages::addMessage(KT_I18N::translate('Please enter a valid email address.'));
		AddToLog('Invalid email address: ' . $from_email, 'spam');
		$errors = true;
	} elseif (in_array($from_email, explode(',', KT_Site::preference('BLOCKED_EMAIL_ADDRESS_LIST')))) {
		// This type of validation error should not be shown in the client.
		AddToLog('Blocked email address: ' . $from_email, 'spam');
		$errors = true;
	} elseif (preg_match($urlRegex, $subject . $body, $match)) {
		KT_FlashMessages::addMessage(KT_I18N::translate('You are not allowed to send messages that contain external links.'));
		AddToLog('Attempt to include external links (' . mb_strimwidth($match[1], 0, 100, "...") . ') by: ' . $from_email, 'spam');
		$errors = true;
	} elseif (empty($recipients)) {
		$errors = true;
	}

	if (KT_Site::preference('USE_RECAPTCHA') && !KT_USER_ID) {
		if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
			// Google reCAPTCHA API secret key
			$secretKey = KT_Site::preference('RECAPTCHA_SECRET_KEY');

			// Verify the reCAPTCHA response
			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);

			// Decode json data
			$responseData = json_decode($verifyResponse);

            // Check reCAPTCHA response
            if ($responseData->success) {
                AddToLog('Google reCaptcha valid response from "' . $from_name . '"/"' . $from_email . '", response ="' . $responseData->success . '"', 'auth');
            } else {
                AddToLog('Failed Google reCaptcha response from "' . $from_name . '"/"' . $from_email . '"', 'spam');
                KT_FlashMessages::addMessage(KT_I18N::translate('Google reCaptcha robot verification failed, please try again.'));
                header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
                $captcha = false;
                exit;
			}
		}
	}

	if ($errors) {
		// Errors? Go back to the form.
		header(
			'Location: message.php' .
			'?to=' . rawurlencode((string) $to) .
			'&from_name=' . rawurlencode((string) $from_name) .
			'&from_email=' . rawurlencode((string) $from_email) .
			'&subject=' . rawurlencode((string) $subject) .
			'&body=' . rawurlencode((string) $body) .
			'&url=' . rawurlencode((string) $url)
		);
	} else {
		if ($termsConditions == '1') {
			// Robot. Display dummy 'message sent' message and record in log
			KT_FlashMessages::addMessage(KT_I18N::translate('The message was successfully sent to %s.', KT_Filter::escapeHtml($to)));
			AddToLog('Robot message caught by checkbox (from: ' . $from_email . ' subject: ' . $subject . ')', 'spam');
			header('Location: ' . $url);
		} else {
			// No errors.  Send the message.
			foreach ($recipients as $recipient) {
				$message         		= array();
				$message['to']   		= $recipient;
				$message['from_name']	= $from_name;
				$message['from_email']	= $from_email;
				$message['subject']		= $subject;
				$message['body']		= nl2br($body, false);
				$message['url']			= $url;

				if (addMessage($message)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The message was successfully sent to %s.', KT_Filter::escapeHtml($to)));
					AddToLog('Message sent FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'auth');
				} else {
					KT_FlashMessages::addMessage(KT_I18N::translate('The message was not sent.'));
					AddToLog('Unable to send a message. FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'error');
				}
			}
			header('Location: ' . KT_Filter::unescapeHtml($url));
		}
		return;
	}
}

$to			= KT_Filter::post('to', null, KT_Filter::get('to'));
$from_name 	= KT_Filter::post('from_name');
$from_email	= KT_Filter::post('from_email');
$subject	= KT_Filter::post('subject', null, KT_Filter::get('subject'));
$body		= KT_Filter::post('body');
$url		= KT_Filter::postUrl('url', KT_Filter::getUrl('url'));


// Only an administrator can use the distribution lists.
$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);
$controller->pageHeader();

$to_names = implode(KT_I18N::$list_separator, array_map(function($user) { return getUserFullName($user); }, recipients($to))); ?>

<div id="contact_page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<?php echo messageForm ($to, $from_name, $from_email, $subject, $body, $url, $to_names); ?>
</div>

<?php
