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

define('WT_SCRIPT_NAME', 'message.php');
require './includes/session.php';

// Some variables are initialised from GET (so we can set initial values in URLs),
// but are submitted in POST so we can have long body text.

$subject    = WT_Filter::post('subject', null, WT_Filter::get('subject'));
$body       = WT_Filter::post('body');
$from_name  = WT_Filter::post('from_name');
$from_email = WT_Filter::post('from_email');
$action     = WT_Filter::post('action', 'compose|send', 'compose');
$to         = WT_Filter::post('to', null, WT_Filter::get('to'));
$method     = WT_Filter::post('method', 'messaging|mailto|none', WT_Filter::get('method', 'messaging|mailto|none', 'messaging'));
$url        = WT_Filter::postUrl('url', WT_Filter::getUrl('url'));

$to_user = get_user_id($to);

$controller = new WT_Controller_Simple();
$controller
	->restrictAccess($to_user || WT_USER_IS_ADMIN && ($to === 'all' || $to === 'last_6mo' || $to === 'never_logged'))
	->setPageTitle(WT_I18N::translate('Kiwitrees message'));

$errors = '';

// Is this message from a member or a visitor?
if (WT_USER_ID) {
	$from = WT_USER_NAME;
} else {
	// Visitors must provide a valid email address
	if ($from_email && (!preg_match('/(.+)@(.+)/', $from_email, $match) || function_exists('checkdnsrr') && checkdnsrr($match[2]) === false)) {
		$errors .= '<p class="ui-state-error">' . WT_I18N::translate('Please enter a valid email address.') . '</p>';
		$action = 'compose';
	}

	// Do not allow anonymous visitors to include links to external sites
	if (preg_match('/(?!' . preg_quote(WT_SERVER_NAME, '/') . ')(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $subject . $body, $match)) {
		$errors .=
			'<p class="ui-state-error">' . WT_I18N::translate('You are not allowed to send messages that contain external links.') . '</p>' .
			'<p class="ui-state-highlight">' . /* I18N: e.g. ‘You should delete the “http://” from “http://www.example.com” and try again.’ */ WT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.', $match[2], $match[1]) . '</p>' .
			AddToLog('Possible spam message from "' . $from_name . '"/"' . $from_email . '", subject="' . $subject . '", body="' . $body . '"', 'auth');
		$action = 'compose';
	}
	$from = $from_email;
}

// Ensure the user always visits this page twice - once to compose it and again to send it.
// This makes it harder for spammers.
switch ($action) {
case 'compose':
	$WT_SESSION->good_to_send = true;
	break;
case 'send':
	// Only send messages if we've come straight from the compose page.
	if (!$WT_SESSION->good_to_send) {
		AddToLog('Attempt to send message without visiting the compose page.  Spam attack?', 'auth');
		$action = 'compose';
	}
	if (!WT_Filter::checkCsrf()) {
		$action = 'compose';
	}
	unset($WT_SESSION->good_to_send);
	break;
}

switch ($action) {
case 'compose':
	$controller
		->pageHeader()
		->addInlineJavascript('
		function checkForm(frm) {
			var content = CKEDITOR.instances["comment"].getData();
			if (frm.subject.value === "") {
				alert("' . WT_I18N::translate('Please enter a message subject.') . '");
				document.messageform.subject.focus();
				return false;
			}
			if (frm.body.value === "" && content === "") {
				alert("' . WT_I18N::translate('Please enter some message text before sending.') . '");
				document.messageform.body.focus();
				return false;
			}
			return true;
		}
	');

	if (array_key_exists('ckeditor', WT_Module::getActiveModules()) && WT_Site::preference('MAIL_FORMAT') == "1") {
		ckeditor_WT_Module::enableBasicEditor($controller);
	} ?>

	<div id="message">
		<h3><?php echo WT_I18N::translate('Send a message'); ?></h3>
		<?php echo $errors;

		if (!WT_USER_ID) { ?>
			<p>
				<?php echo WT_I18N::translate('<b>Please Note:</b> Private information of living individuals will only be given to family relatives and close friends.  You will be asked to verify your relationship before you will receive any private data.  Sometimes information of dead persons may also be private.  If this is the case, it is because there is not enough information known about the person to determine whether they are alive or not and we probably do not have more information on this person.<br /><br />Before asking a question, please verify that you are inquiring about the correct person by checking dates, places, and close relatives.  If you are submitting changes to the genealogical data, please include the sources where you obtained the data.'); ?>
			</p>
		<?php }
		if ($to !== 'all' && $to !== 'last_6mo' && $to !== 'never_logged') { ?>
			<h5><?php echo WT_I18N::translate('This message will be sent to %s', '<em>' . getUserFullName($to_user) . '</em>'); ?></h5>
		<?php } ?>

		<form name="messageform" method="post" action="message.php" onsubmit="t = new Date(); document.messageform.time.value=t.toUTCString(); return checkForm(this);">
			<?php echo WT_Filter::getCsrf();
			if (!WT_USER_ID) { ?>
				<div class="option">
					<label for="from_name"><?php echo WT_I18N::translate('Your name'); ?></label>
					<input type="text" name="from_name" id="from_name" value="<?php echo WT_Filter::escapeHtml($from_name); ?>" required>
				</div>
				<div class="option">
					<small>
						<?php echo WT_I18N::translate('Please provide your email address so that we may contact you in response to this message. If you do not provide your email address we will not be able to respond to your inquiry. Your email address will not be used in any other way besides responding to this inquiry.'); ?>
					</small>
					<label for="from_name"><?php echo WT_I18N::translate('Email address'); ?></label>
					<input type="email" name="from_email" id="from_email" value="<?php echo WT_Filter::escapeHtml($from_email); ?>" required>
				</div>
			<?php } ?>
			<div class="option">
				<label for="from_name"><?php echo WT_I18N::translate('Subject'); ?></label>
				<input type="hidden" name="action" value="send">
				<input type="hidden" name="to" value="<?php echo WT_Filter::escapeHtml($to); ?>">
				<input type="hidden" name="time" value="">
				<input type="hidden" name="method" value="<?php echo $method; ?>">
				<input type="hidden" name="url" value="<?php echo WT_Filter::escapeHtml($url); ?>">
				<input type="text" name="subject" size="50" value="<?php echo WT_Filter::escapeHtml($subject); ?>">
			</div>
			<div class="option">
				<label for="body"><?php echo WT_I18N::translate('Body'); ?></label>
				<textarea class="html-edit" name="body" id="body"><?php echo WT_Filter::escapeHtml($body); ?></textarea>
			</div>
			<?php if ($method == 'messaging') { ?>
				<p>
					<?php echo WT_I18N::translate('When you send this message you will receive a copy sent via email to the address you provided.'); ?>
				</p>
			<?php } ?>
			<p id="save-cancel">
				<button class="btn btn-primary" type="submit">
					<i class="fa fa-envelope-o"></i>
					<?php echo WT_I18N::translate('Send'); ?>
				</button>
				<button class="btn btn-primary" type="button" onclick="window.close();">
					<i class="fa fa-times"></i>
					<?php echo WT_I18N::translate('Close'); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
	break;

case 'send':
	if ($from_email) {
		$from = $from_email;
	}

	$toarray = [$to];
	if ($to === 'all') {
		$toarray = [];
		foreach (WT_User::all() as $user) {
			$toarray[$user->getUserId()] = $user->getUserName();
		}
	}
	if ($to === 'never_logged') {
		$toarray = [];
		foreach (WT_User::all() as $user) {
			if (get_user_setting($user_id,'verified_by_admin') && get_user_setting($user_id,'reg_timestamp') > get_user_setting($user_id,'sessiontime')) {
				$toarray[$user->getUserId()] = $user->getUserName();
			}
		}
	}
	if ($to === 'last_6mo') {
		$toarray = [];
		$sixmos  = 60 * 60 * 24 * 30 * 6; //-- timestamp for six months
		foreach (WT_User::all() as $user) {
			if (get_user_setting($user_id,'sessiontime') > 0 && (WT_TIMESTAMP - get_user_setting($user_id,'sessiontime') > $sixmos)) {
				$toarray[$user->getUserId()] = $user->getUserName();
			} elseif (!get_user_setting($user_id,'verified_by_admin') && (WT_TIMESTAMP - get_user_setting($user_id,'reg_timestamp') > $sixmos)) {
				//-- not verified by registration past 6 months
				$toarray[$user->getUserId()] = $user->getUserName();
			}
		}
	}
	$i = 0;
	foreach ($toarray as $indexval => $to) {
		$message         = [];
		$message['to']   = $to;
		$message['from'] = $from;
		if (!empty($from_name)) {
			$message['from_name']  = $from_name;
			$message['from_email'] = $from_email;
		}
		$message['subject'] = $subject;
		$message['body']    = nl2br($body, false);
		$message['method']  = $method;
		$message['url']     = $url;
		if ($i > 0) {
			$message['no_from'] = true;
		}
		if (addMessage($message)) {
			WT_FlashMessages::addMessage(WT_I18N::translate('The message was successfully sent to %s', WT_Filter::escapeHtml($to)));
		} else {
			WT_FlashMessages::addMessage(WT_I18N::translate('The message was not sent.'));
			AddToLog('Unable to send a message. FROM:' . $from . ' TO:' . $to . ' (failed to send)', 'error');
		}
		$i++;
	}
	$controller
		->pageHeader()
		->addInlineJavascript('window.opener.location.reload(); window.close();');
	break;
}
