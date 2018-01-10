<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

class contact_KT_Module extends KT_Module implements KT_Module_Menu {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Contact');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “contact” module */ KT_I18N::translate('A contact page');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 160;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		}
	}

	// Extend class KT_Module_Menu
	public function getMenuTitle() {
		return KT_I18N::translate('Contact');
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;
		$ged_id	= KT_GED_ID;

		//-- main PAGES menu item
		$contact_user_id	= get_gedcom_setting($ged_id, 'CONTACT_USER_ID');
		$webmaster_user_id	= get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID');
		$supportLink		= user_contact_link($webmaster_user_id);
		if ($webmaster_user_id == $contact_user_id) {
			$contactLink = $supportLink;
		} else {
			$contactLink = user_contact_link($contact_user_id);
		}

		if ((!$contact_user_id && !$webmaster_user_id) || (!$supportLink && !$contactLink)) {
			return '';
		} else {
			$menu = new KT_Menu($this->getMenuTitle(), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;url=' . KT_SERVER_NAME . KT_SCRIPT_PATH . addslashes(urlencode(get_query_url())), 'menu-contact', 'down');
			$menu->addClass('menuitem', 'menuitem_hover', '');
			return $menu;
		}
	}

	private function show() {
		global $controller;
		require_once KT_ROOT . 'includes/functions/functions_mail.php';

		$controller = new KT_Controller_Page();
		$controller->setPageTitle($this->getTitle());

		if (array_key_exists('ckeditor', KT_Module::getActiveModules()) && KT_Site::preference('MAIL_FORMAT') == "1") {
			ckeditor_KT_Module::enableBasicEditor($controller);
		}

		// Send the message.
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$to         = KT_Filter::post('to', null, KT_Filter::get('to'));
			$from_name  = KT_Filter::post('from_name');
			$from_email = KT_Filter::post('from_email');
			$subject    = KT_Filter::post('subject', null, KT_Filter::get('subject'));
			$body       = KT_Filter::post('body');
			$url        = KT_Filter::postUrl('url', KT_Filter::getUrl('url'));

			// Only an administration can use the distribution lists.
			$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);

			$recipients = recipients($to);

			// Different validation for admin/user/visitor.
			$errors = false;
			if (KT_USER_ID) {
				$from_name  = getUserFullName(KT_USER_ID);
				$from_email = getUserEmail(KT_USER_ID);
			} elseif ($from_name === '' || $from_email === '') {
				$errors = true;
			} elseif (!preg_match('/@(.+)/', $from_email, $match) || function_exists('checkdnsrr') && !checkdnsrr($match[1])) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Please enter a valid email address.'));
				$errors = true;
			} elseif (preg_match('/(?!' . preg_quote(KT_SERVER_NAME, '/') . ')(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $subject . $body, $match)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('You are not allowed to send messages that contain external links.') . ' ' . /* I18N: e.g. ‘You should delete the “http://” from “http://www.example.com” and try again.’ */ KT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.', $match[2], $match[1]));
				$errors = true;
			} elseif (empty($recipients)) {
				$errors = true;
			}

			if ($errors) {
				// Errors? Go back to the form.
				header(
					'Location: message.php' .
					'?to=' . rawurlencode($to) .
					'&from_name=' . rawurlencode($from_name) .
					'&from_email=' . rawurlencode($from_email) .
					'&subject=' . rawurlencode($subject) .
					'&body=' . rawurlencode($body) .
					'&url=' . rawurlencode($url)
				);
			} else {
				// No errors.  Send the message.
				foreach ($recipients as $recipient) {
					$message         		= array();
					$message['to']   		= $to;
					$message['from_name']	= $from_name;
					$message['from_email']	= $from_email;
					$message['subject']		= $subject;
					$message['body']		= nl2br($body, false);
					$message['url']			= $url;

					if (addMessage($message)) {
						KT_FlashMessages::addMessage(KT_I18N::translate('The message was successfully sent to %s.', KT_Filter::escapeHtml($to)));
					} else {
						KT_FlashMessages::addMessage(KT_I18N::translate('The message was not sent.'));
						AddToLog('Unable to send a message. FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'error');
					}
				}

				header('Location: ' . $url);
			}

			return;
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
	<?php }

}
