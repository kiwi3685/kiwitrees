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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

/**
 * Add a message to a user's inbox
 *
 * @param string[] $message
 *
 * @return bool
 */
function addMessage($message) {
	global $WT_TREE;

	$success = true;

	$sender    = get_user_id($message['from']);
	$recipient = get_user_id($message['to']);

	// Sender may not be a Kiwitrees user
	if ($sender) {
		$sender_email     = getUserEmail($sender);
		$sender_real_name = getUserFullName($sender);
	} else {
		$sender_email     = $message['from'];
		$sender_real_name = $message['from_name'];
	}

	// Send a copy of the copy message back to the sender.
	// Switch to the sender’s language.
	if ($sender) {
		WT_I18N::init(get_user_setting($sender, 'language'));
	}

	$copy_email = $message['body'];
	if (!empty($message['url'])) {
		$copy_email .=
			WT_Mail::EOL . WT_Mail::EOL . '--------------------------------------' . WT_Mail::EOL .
			WT_I18N::translate('This message was sent while viewing the following URL: ') . $message['url'] . WT_Mail::EOL;
	}

	if ($sender) {
		// Message from a signed-in user
		$copy_email = WT_I18N::translate('You sent the following message to a user at %1$s:', strip_tags(WT_TREE_TITLE)) . ' ' . getUserFullName($recipient) . WT_Mail::EOL . WT_Mail::EOL . $copy_email;
	} else {
		// Message from a visitor
		$copy_email  = WT_I18N::translate('You sent the following message to an administrator at %1$s:', strip_tags(WT_TREE_TITLE)) . WT_Mail::EOL . WT_Mail::EOL . WT_Mail::EOL . $copy_email;
	}

	$success = $success && WT_Mail::send(
		// “From:” header
		$WT_TREE,
		// “To:” header
		$sender_email,
		$sender_real_name,
		// “Reply-To:” header
		WT_Site::preference('SMTP_FROM_NAME'),
		$WT_TREE->tree_title,
		// Message body
		WT_I18N::translate('%1$s message', strip_tags(WT_TREE_TITLE)) . ' - ' . $message['subject'],
		$copy_email
	);

	// Switch to the recipient’s language.
	WT_I18N::init(get_user_setting($recipient, 'language'));
	if (isset($message['from_name'])) {
		$message['body'] =
			WT_I18N::translate('From') . ':  ' . $message['from_name'] . WT_Mail::EOL .
			WT_I18N::translate('Email address') . ':  ' . $message['from_email'] . WT_Mail::EOL .
			WT_I18N::translate('Content') . ':  ' . $message['body'];
	}

	// Add another footer - unless we are an admin
	if (!WT_USER_IS_ADMIN) {
		if (!empty($message['url'])) {
			$message['body'] .=
			WT_Mail::EOL . WT_Mail::EOL .
				'--------------------------------------' . WT_Mail::EOL .
				WT_I18N::translate('This message was sent while viewing the following URL: ') . $message['url'] . WT_Mail::EOL;
		}
	}

	if ($sender) {
		$original_email = /* I18N: %s is a person's name */ WT_I18N::translate('%s sent you the following message.', getUserFullName($sender));
	} else {
		if (!empty($message['from_name'])) {
			$original_email = /* I18N: %s is a person's name */ WT_I18N::translate('%s sent you the following message.', $message['from_name']);
		} else {
			$original_email = /* I18N: %s is a person's name */ WT_I18N::translate('%s sent you the following message.', $message['from']);
		}
	}
	$original_email .= WT_Mail::EOL . WT_Mail::EOL . $message['body'];

	$success = $success && WT_Mail::send(
		// “From:” header
			$WT_TREE,
			// “To:” header
			getUserEmail($recipient),
			getUserFullName($recipient),
			// “Reply-To:” header
			$sender_email,
			$sender_real_name,
			// Message body
			WT_I18N::translate('%1$s message', strip_tags(WT_TREE_TITLE)) . ' - ' . $message['subject'],
			$original_email
		);

	WT_I18N::init(WT_LOCALE); // restore language settings if needed

	return $success;
}

/**
 * Convert a username (or mailing list name) into an array of recipients.
 *
 * @param $to
 *
 * @return $recipients[]
 */
function recipients($to) {
	$recipients = [];
	if ($to === 'all') {
		$recipients = [];
		foreach (get_all_users() as $user_id=>$user_name) {
			$recipients[$user_id] = $user_name;
		}
	} elseif ($to === 'last_6mo') {
		$recipients = [];
		$sixmos  = 60 * 60 * 24 * 30 * 6; //-- timestamp for six months
		foreach (get_all_users() as $user_id=>$user_name) {
			if (get_user_setting($user_id,'sessiontime') > 0 && (WT_TIMESTAMP - get_user_setting($user_id,'sessiontime') > $sixmos)) {
				$recipients[$user_id] = $user_name;
			} elseif (!get_user_setting($user_id,'verified_by_admin') && (WT_TIMESTAMP - get_user_setting($user_id,'reg_timestamp') > $sixmos)) {
				//-- not verified by registration past 6 months
				$recipients[$user_id] = $user_name;
			}
		}
	} elseif ($to === 'never_logged') {
		$recipients = [];
		foreach (get_all_users() as $user_id=>$user_name) {
			if (get_user_setting($user_id,'verified_by_admin') && get_user_setting($user_id,'reg_timestamp') > get_user_setting($user_id,'sessiontime')) {
				$recipients[$user_id] = $user_name;
			}
		}
	}

	return $recipients;
}

/**
 * Add a message to a user's inbox, send it to them via email, or both.
 *
 * @param Tree   $tree
 * @param string $sender_name
 * @param string $sender_email
 * @param User   $recipient
 * @param string $subject
 * @param string $body
 * @param string $url
 *
 * @return bool
 */
function deliverMessage(WT_Tree $tree, $sender_email, $sender_name, $recipient, $subject, $body, $url) {
	$success = true;
	$hr      = '--------------------------------------------------';
	$body    = nl2br($body, false);
	$body_cc = WT_I18N::translate('You sent the following message to a kiwitrees user:') . ' ' . getUserFullName($recipient) . WT_Mail::EOL . $hr . WT_Mail::EOL . $body;

	WT_I18N::init(get_user_setting($recipient, 'language', WT_LOCALE));

	$body = /* I18N: %s is a person's name */ WT_I18N::translate('%s sent you the following message.', $sender_email) . WT_Mail::EOL . WT_Mail::EOL . $body;

	if ($url !== 'index.php') {
		$body .= WT_Mail::EOL . $hr . WT_Mail::EOL . WT_I18N::translate('This message was sent while viewing the following URL: ') . $url . WT_Mail::EOL;

	}

	// Send via email
	if (in_array(get_user_setting($recipient, 'contactmethod'), ['messaging', 'mailto', 'none'])) {
		$success = $success && WT_Mail::send(
			// “From:” header
			$tree,
			// “To:” header
			$sender_email,
			$sender_name,
			// “Reply-To:” header
			Site::getPreference('SMTP_FROM_NAME'),
			$tree->tree_title,
			// Message body
			WT_I18N::translate('Kiwitrees message') . ' - ' . $subject,
			$body
		);
	}

	WT_I18N::init(WT_LOCALE);

	return $success;
}

/**
 * Create message form.
 *
 * @param string $to
 * @param string $from_name
 * @param string $from_email
 * @param string $subject
 * @param string $body
 * @param string $url
 * @param string $to_names
 *
 * @return string
 */
function messageForm ($to, $from_name, $from_email, $subject, $body, $url, $to_names) {
	$contact_user_id	= get_gedcom_setting(WT_GED_ID, 'CONTACT_USER_ID');
	$webmaster_user_id	= get_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID');
	$supportLink		= user_contact_link($webmaster_user_id);

	if ($webmaster_user_id == $contact_user_id) {
		$contactLink = $supportLink;
	} else {
		$contactLink = user_contact_link($contact_user_id);
	}

	if ((!$contact_user_id && !$webmaster_user_id) || (!$supportLink && !$contactLink) || $to) {
		$form_count = 0;
		$form_title_1 = '';
		$form_title_2 = '';
		$to_user_id = '';
	} elseif (($supportLink == $contactLink) || ($contact_user_id == '') || ($webmaster_user_id == '') || !$to) {
		$form_count = 1;
		$to_user_id = WT_I18N::translate('Support');
		$form_title_1 = '<h3>' . WT_I18N::translate('For further information') . '</h3>';
		$form_title_2 = '';
		$to_user_id_1 = '';
		$to_user_id_2 = '';
	} else {
		$form_count = 2;
		$to_user_id = '';
		$form_title_1 = '<h3>' . WT_I18N::translate('For technical support and information') . '</h3>';
		$to_user_id_1 = WT_I18N::translate('Technical help');
		$form_title_2 = '<h3>' . WT_I18N::translate('For help with genealogy questions') . '</h3>';
		$to_user_id_2 = WT_I18N::translate('Genealogy help');
	}
//print_r($form_count);
	switch ($form_count) {
		case 0:
			$form_title	= $form_title_1;
			$to			= get_user_name(getUserID());
			$to_name	= getUserFullName(getUserID());
		break;
		case 1:
			$form_title	= $form_title_1;
			$to			= get_user_name(get_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID'));
			$to_name	= getUserFullName(get_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID'));
		break;
		case 2:
			$form_title	= $form_title_2;
			$to			= get_user_name(get_gedcom_setting(WT_GED_ID, 'CONTACT_USER_ID'));
			$to_name	= getUserFullName(get_gedcom_setting(WT_GED_ID, 'CONTACT_USER_ID'));
		break;
	} ?>

	<form name="messageform" method="post">
		<div id="contact_header">
			<?php if (!WT_USER_ID) { ?>
				<p>
					<small>
						<?php echo WT_I18N::translate('<b>Please Note:</b> Private information of living individuals will only be given to family relatives and close friends.  You will be asked to verify your relationship before you will receive any private data.  Sometimes information of dead persons may also be private.  If this is the case, it is because there is not enough information known about the person to determine whether they are alive or not and we probably do not have more information on this person.<br /><br />Before asking a question, please verify that you are inquiring about the correct person by checking dates, places, and close relatives.  If you are submitting changes to the genealogical data, please include the sources where you obtained the data.'); ?>
					</small>
				</p>
			<?php } ?>
			<?php WT_Filter::getCsrf(); ?>
			<input type="hidden" name="url" value="<?php echo WT_Filter::escapeHtml($url); ?>">
			<?php if (!WT_USER_ID) { ?>
				<div class="option">
					<label for="from_name"><?php echo WT_I18N::translate('Your name'); ?></label>
					<input type="text" name="from_name" id="from_name" value="<?php echo WT_Filter::escapeHtml($from_name); ?>" required>
				</div>
				<div class="option">
					<p>
						<small>
							<?php echo WT_I18N::translate('Please provide your email address so that we may contact you in response to this message. If you do not provide your email address we will not be able to respond to your inquiry. Your email address will not be used in any other way besides responding to this inquiry.'); ?>
						</small>
					</p>
					<label for="from_name"><?php echo WT_I18N::translate('Email address'); ?></label>
					<input type="email" name="from_email" id="from_email" value="<?php echo WT_Filter::escapeHtml($from_email); ?>" required>
				</div>
			<?php } ?>
		</div>
		<hr>
		<div id="contact_forms">
			<?php for ($i = 0; $i <= $form_count; $i++) { ?>
				<div class="contact_form">
					<?php echo $form_title; ?>
					<div class="option">
						<input type="hidden" name="to" value="<?php echo WT_Filter::escapeHtml($to); ?>">
						<label for="to_name"><?php echo WT_I18N::translate('To'); ?></label>
						<input type="text" name="to_name" id="to_name" value="<?php echo $to_name; ?>">
					</div>
					<div class="option">
						<label for="from_name"><?php echo WT_I18N::translate('Subject'); ?></label>
						<input type="text" name="subject" size="50" value="<?php echo WT_Filter::escapeHtml($subject); ?>">
					</div>
					<div class="option">
						<label for="body"><?php echo WT_I18N::translate('Body'); ?></label>
						<textarea class="html-edit" name="body" id="body"><?php echo WT_Filter::escapeHtml($body); ?></textarea>
					</div>
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
				</div>
			<?php if ($form_count == 1) {
				exit;
			}

		} ?>
		</div>
	</form>
	<?php if (WT_USER_ID && get_user_setting(WT_USER_ID, 'contactmethod') === 'messaging') { ?>
		<p>
			<small>
				<?php echo WT_I18N::translate('When you send this message you will receive a copy sent via email to the address you provided.'); ?>
			</small>
		</p>
	<?php }

}
