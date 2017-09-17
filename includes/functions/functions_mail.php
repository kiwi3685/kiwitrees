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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
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
			if (get_user_setting($user_id,'sessiontime') > 0 && (KT_TIMESTAMP - get_user_setting($user_id,'sessiontime') > $sixmos)) {
				$recipients[$user_id] = $user_name;
			} elseif (!get_user_setting($user_id,'verified_by_admin') && (KT_TIMESTAMP - get_user_setting($user_id,'reg_timestamp') > $sixmos)) {
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
	} else {
		$recipients = array_filter([findByUserName($to)]);
	}

	return $recipients;
}

/**
 * Send a message to a user's inbox via email.
 *
 * @param string[] $message
 *
 * @return bool
 */
function addMessage($message) {
	global $KT_TREE;

	//Set html formatting
	if (KT_Site::preference('MAIL_FORMAT')) {
		$bold_on	= '<strong>';
		$bold_off	= '</strong>';
		$line		= '<hr>';
	} else {
		$bold_on 	= '';
		$bold_off 	= '';
		$line		= '--------------------------------';
	}

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

	// Send a copy of the message back to the sender.
		if ($sender) {
			// Switch to the sender’s language.
			KT_I18N::init(get_user_setting($sender, 'language'));
			// Message from a signed-in user
			$copy_email = KT_I18N::translate('You sent the following message to a user at %1$s:', strip_tags(KT_TREE_TITLE)) . ' ' . getUserFullName($recipient);
		} else {
			// Message from a visitor
			$copy_email = KT_I18N::translate('You sent the following message to an administrator at %1$s:', strip_tags(KT_TREE_TITLE));
		}

		$copy_email .=
			KT_Mail::EOL .
			KT_Mail::EOL .
			$bold_on . KT_I18N::translate('From') . ':  ' . $bold_off . $message['from_name'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Subject') . ':  ' . $bold_off . $message['subject'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Content') . ':  ' . $bold_off . KT_Mail::EOL .
			$message['body'] . KT_Mail::EOL .
			$line . KT_Mail::EOL;

		if (!empty($message['url'])) {
			$copy_email .=  KT_I18N::translate('This message was sent while viewing the following URL: ') . $message['url'] . KT_Mail::EOL;
		}

		$success = $success && KT_Mail::send(
			// “From:” header
			$KT_TREE,
			// “To:” header
			$sender_email,
			$sender_real_name,
			// “Reply-To:” header
			KT_Site::preference('SMTP_FROM_NAME'),
			$KT_TREE->tree_title,
			// Message subject
			KT_I18N::translate('%1$s message', strip_tags(KT_TREE_TITLE)) . ' - ' . $message['subject'],
			// Message content
			$copy_email
		);

	// Send the message to the recipient.
		// Switch to the recipient’s language.
		KT_I18N::init(get_user_setting($recipient, 'language'));
		if ($sender) {
			$original_email = /* I18N: %s is a person's name */ KT_I18N::translate('%s sent you the following message.', getUserFullName($sender));
		} else {
			if (!empty($message['from_name'])) {
				$original_email = /* I18N: %s is a person's name */ KT_I18N::translate('%s sent you the following message.', $message['from_name']);
			} else {
				$original_email = /* I18N: %s is a person's name */ KT_I18N::translate('%s sent you the following message.', $message['from']);
			}
		}

		$original_email .=
			KT_Mail::EOL .
			KT_Mail::EOL .
			$bold_on . KT_I18N::translate('From') . ':  ' . $bold_off . $message['from_email'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Subject') . ':  ' . $bold_off . $message['subject'] . KT_Mail::EOL .
			$bold_on . KT_I18N::translate('Content') . ':  ' . $bold_off . KT_Mail::EOL .
			$message['body'] . KT_Mail::EOL .
			$line . KT_Mail::EOL;

		// Add another footer - unless we are an admin
		if (!KT_USER_IS_ADMIN && !empty($message['url'])) {
			$original_email .= $bold_on . KT_I18N::translate('This message was sent while viewing the following URL: ') . $message['url'] . $bold_off . KT_Mail::EOL;
		}

		$success = $success && KT_Mail::send(
			// “From:” header
			$KT_TREE,
			// “To:” header
			getUserEmail($recipient),
			getUserFullName($recipient),
			// “Reply-To:” header
			$sender_email,
			$sender_real_name,
			// Message subject
			KT_I18N::translate('%1$s message', strip_tags(KT_TREE_TITLE)) . ' - ' . $message['subject'],
			// Message content
			$original_email
		);

	KT_I18N::init(KT_LOCALE); // restore language settings if needed

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
	$contact_user_id	= get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
	$webmaster_user_id	= get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
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
		$to_user_id = KT_I18N::translate('Support');
		$form_title_1 = '<h3>' . KT_I18N::translate('For further information') . '</h3>';
		$form_title_2 = '';
		$to_user_id_1 = '';
		$to_user_id_2 = '';
	} else {
		$form_count = 2;
		$to_user_id = '';
		$form_title_1 = '<h3>' . KT_I18N::translate('For technical support and information') . '</h3>';
		$to_user_id_1 = KT_I18N::translate('Technical help');
		$form_title_2 = '<h3>' . KT_I18N::translate('For help with genealogy questions') . '</h3>';
		$to_user_id_2 = KT_I18N::translate('Genealogy help');
	}
	switch ($form_count) {
		case 0:
			$form_title	= $form_title_1;
			if (!$to) {
				$to			= get_user_name(getUserID());
				$to_names	= getUserFullName(getUserID());
			} else {
				$to_names	= $to;
			}
		break;
		case 1:
			$form_title	= $form_title_1;
			$to			= get_user_name(get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID'));
			$to_names	= getUserFullName(get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID'));
		break;
		case 2:
			$form_title	= $form_title_2;
			$to			= get_user_name(get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID'));
			$to_names	= getUserFullName(get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID'));
		break;
	} ?>

	<form name="messageform" method="post">
		<input type="hidden" name="url" value="<?php echo KT_Filter::escapeHtml($url); ?>">
		<div id="contact_header">
			<?php if (!KT_USER_ID) { ?>
				<p>
					<small>
						<?php echo KT_I18N::translate('<b>Please Note:</b> Private information of living individuals will only be given to family relatives and close friends.  You will be asked to verify your relationship before you will receive any private data.  Sometimes information of dead persons may also be private.  If this is the case, it is because there is not enough information known about the person to determine whether they are alive or not and we probably do not have more information on this person.<br /><br />Before asking a question, please verify that you are inquiring about the correct person by checking dates, places, and close relatives.  If you are submitting changes to the genealogical data, please include the sources where you obtained the data.'); ?>
					</small>
				</p>
				<div class="option">
					<label for="from_name"><?php echo KT_I18N::translate('Your name'); ?></label>
					<input type="text" name="from_name" id="from_name" value="<?php echo KT_Filter::escapeHtml($from_name); ?>" required>
				</div>
				<div class="option">
					<p>
						<small>
							<?php echo KT_I18N::translate('Please provide your email address so that we may contact you in response to this message. If you do not provide your email address we will not be able to respond to your inquiry. Your email address will not be used in any other way besides responding to this inquiry.'); ?>
						</small>
					</p>
					<label for="from_email"><?php echo KT_I18N::translate('Email address'); ?></label>
					<input type="email" name="from_email" id="from_email" value="<?php echo $from_email; ?>" required >
				</div>
			<?php } ?>
		</div>
		<hr>
		<div id="contact_forms">
			<?php for ($i = 0; $i <= $form_count; $i++) { ?>
				<div class="contact_form">
					<?php echo $form_title; ?>
					<div class="option">
						<label for="to_name"><?php echo KT_I18N::translate('To'); ?></label>
						<input type="text" name="to_name" id="to_name" value="<?php echo $to_names; ?>">
						<input type="hidden" name="to" value="<?php echo KT_Filter::escapeHtml($to); ?>">
					</div>
					<div class="option">
						<label for="from_name"><?php echo KT_I18N::translate('Subject'); ?></label>
						<input type="text" name="subject" size="50" value="<?php echo KT_Filter::escapeHtml($subject); ?>">
					</div>
					<div class="option">
						<label for="body"><?php echo KT_I18N::translate('Body'); ?></label>
						<textarea class="html-edit" name="body" id="body"><?php echo KT_Filter::escapeHtml($body); ?></textarea>
					</div>
					<p id="save-cancel">
						<button class="btn btn-primary" type="submit">
							<i class="fa fa-envelope-o"></i>
							<?php echo KT_I18N::translate('Send'); ?>
						</button>
						<button class="btn btn-primary" type="button" onclick="window.close();">
							<i class="fa fa-times"></i>
							<?php echo KT_I18N::translate('Close'); ?>
						</button>
					</p>
				</div>
			<?php if ($form_count == 1) {
				exit;
			}

		} ?>
		</div>
	</form>
	<?php if (KT_USER_ID && get_user_setting(KT_USER_ID, 'contactmethod') === 'messaging') { ?>
		<p>
			<small>
				<?php echo KT_I18N::translate('When you send this message you will receive a copy sent via email to the address you provided.'); ?>
			</small>
		</p>
	<?php }

}
