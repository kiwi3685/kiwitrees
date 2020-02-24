<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

require_once KT_ROOT . 'library/swiftmailer/lib/swift_required.php';

/**
 * Send mail messages.
 */
class KT_Mail {
	const EOL = "<br>\r\n"; // End-of-line that works for both TEXT and HTML messages

	/**
	 * Send an external email message
	 * Caution! gmail may rewrite the "From" header unless you have added the address to your account.
	 *
	 * @param KT_Tree	$tree
	 * @param string	$to_email
	 * @param string	$to_name
	 * @param string	$replyto_email
	 * @param string	$replyto_name
	 * @param string	$subject
	 * @param string	$message
	 *
	 * @return bool
	 */
	public static function send(KT_Tree $tree, $to_email, $to_name, $replyto_email, $replyto_name, $subject, $message) {
		try {
			// Swiftmailer uses the PHP default tmp directory.  On some servers, this
			// is outside the open_basedir list.  Therefore we must set one explicitly.
			KT_File::mkdir(KT_DATA_DIR . 'mail_tmp');

			Swift_Preferences::getInstance()->setTempDir(KT_DATA_DIR . 'mail_tmp');

			$mail = Swift_Message::newInstance()
				->setSubject($subject)
				->setFrom(KT_Site::preference('SMTP_FROM_NAME'), $tree->tree_title)
				->setTo($to_email, $to_name)
				->setReplyTo($replyto_email, $replyto_name)
				->setBody($message, 'text/html')
				->addPart(KT_Filter::unescapeHtml($message), 'text/plain');

			Swift_Mailer::newInstance(self::transport())->send($mail);
		} catch (\ErrorException $ex) {
			AddToLog('Mail ->' . $message . '<-', 'error');

			return false;
		}

		return true;
	}

	/**
	 * Send an automated system message (such as a password reminder) from a tree to a user.
	 *
	 * @param KT_Tree $tree
	 * @param string  $user
	 * @param string  $subject
	 * @param string  $message
	 *
	 * @return bool
	 */
	public static function systemMessage(KT_Tree $tree, $user, $subject, $message) {
		return self::send(
			$tree,
			getUserEmail($user), getUserFullName($user),
			KT_Site::preference('SMTP_FROM_NAME'), $tree->tree_title,
			$subject,
			$message
		);
	}

	/**
	 * Create a transport mechanism for sending mail
	 *
	 * @return Swift_Transport
	 */
	public static function transport() {
		switch (KT_Site::preference('SMTP_ACTIVE')) {
		case 'internal':
			return Swift_MailTransport::newInstance();
		case 'sendmail':
			return Swift_SendmailTransport::newInstance();
		case 'external':
            $transport = Swift_SmtpTransport::newInstance()
                ->setHost(KT_Site::preference('SMTP_HOST'))
                ->setPort(KT_Site::preference('SMTP_PORT'))
                ->setLocalDomain(KT_Site::preference('SMTP_HELO'));

            if (KT_Site::preference('SMTP_AUTH')) {
                $transport
                    ->setUsername(KT_Site::preference('SMTP_AUTH_USER'))
                    ->setPassword(KT_Site::preference('SMTP_AUTH_PASS'));
            }

            if (KT_Site::preference('SMTP_SSL') !== 'none') {
                $transport->setEncryption(KT_Site::preference('SMTP_SSL'));
            }

			return $transport;
		default:
			// For testing
			return Swift_NullTransport::newInstance();
		}
	}
}
