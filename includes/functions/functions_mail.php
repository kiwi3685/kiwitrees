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
