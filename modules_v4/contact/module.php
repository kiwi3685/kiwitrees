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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class contact_WT_Module extends WT_Module implements WT_Module_Menu {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Contact');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “contact” module */ WT_I18N::translate('A contact page');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 160;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		}
	}

	// Extend class WT_Module_Menu
	public function getMenuTitle() {
		return WT_I18N::translate('Contact');
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;
		$ged_id	= WT_GED_ID;

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
			$menu = new WT_Menu($this->getMenuTitle(), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;url=' . addslashes(urlencode(get_query_url())), 'menu-contact', 'down');
			$menu->addClass('menuitem', 'menuitem_hover', '');
			return $menu;
		}
	}

	private function show() {
		global $controller;
		$subject    = WT_Filter::post('subject', null, WT_Filter::get('subject'));
		$body       = WT_Filter::post('body');
		$from_name  = WT_Filter::post('from_name');
		$from_email = WT_Filter::post('from_email');
		$action     = WT_Filter::post('action', 'compose|send', 'compose');
		$to         = WT_Filter::post('to', null, WT_Filter::get('to'));
		$method     = WT_Filter::post('method', 'messaging|mailto|none', WT_Filter::get('method', 'messaging|mailto|none', 'messaging'));
		$url        = WT_Filter::postUrl('url', WT_Filter::getUrl('url'));
		$ged_id		= WT_GED_ID;
		$errors		= '';
		$html		= '';

		$contact_user_id	= get_gedcom_setting($ged_id, 'CONTACT_USER_ID');
		$webmaster_user_id	= get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID');
		$supportLink		= user_contact_link($webmaster_user_id);
		if ($webmaster_user_id == $contact_user_id) {
			$contactLink = $supportLink;
		} else {
			$contactLink = user_contact_link($contact_user_id);
		}

		if ((!$contact_user_id && !$webmaster_user_id) || (!$supportLink && !$contactLink)) {
			$form_count = 0;
			$form_title_1 = '';
			$form_title_2 = '';
			$to_user_id = '';
		}

		if (($supportLink == $contactLink) || ($contact_user_id == '') || ($webmaster_user_id == '')) {
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

		// Is this message from a member or a visitor?
		if (WT_USER_ID) {
			$from = WT_USER_NAME;
		} else {
			// Visitors must provide a valid email address
			if ($from_email && (!preg_match("/(.+)@(.+)/", $from_email, $match) || function_exists('checkdnsrr') && checkdnsrr($match[2])===false)) {
				$errors.='<p class="ui-state-error">' . WT_I18N::translate('Please enter a valid email address.') . ' </p>';
				$action='compose';
			}

			// Do not allow anonymous visitors to include links to external sites
			if (preg_match('/(?!' . preg_quote(WT_SERVER_NAME, '/') . ' )(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $subject.$body, $match)) {
				$errors.=
					'<p class="ui-state-error">' . WT_I18N::translate('You are not allowed to send messages that contain external links.') . ' </p>' .
					'<p class="ui-state-highlight">' . /* I18N: e.g. ‘You should delete the “http://” from “http://www.example.com” and try again.” */ WT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.' . $match[2], $match[1]).'</p>' .
				AddToLog('Possible spam message from "' . $from_name . '"/"' . $from_email . '", IP="' . $WT_REQUEST->getClientIp() . ' " subject="' . $subject . '", body="' . $body . '"', 'error');
				$action='compose';
			}
			$from = $from_email;
		}

		$controller = new WT_Controller_Page();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader();
		$html .= '
		<div id="contact_page" style="margin: 12px;">
			<h2>' . $controller->getPageTitle() . '</h2>';

			// Ensure the user always visits this page twice - once to compose it and again to send it.
			// This makes it harder for spammers.
			switch ($action) {
				case 'compose':
					$controller
						->addInlineJavascript('
						function checkForm(frm) {
							if (frm.subject.value=="") {
								alert("' . WT_I18N::translate('Please enter a message subject.') . ' ");
								document.messageform.subject.focus();
								return false;
							}
							if (frm.body.value=="") {
								alert("' . WT_I18N::translate('Please enter some message text before sending.') . ' ");
								document.messageform.body.focus();
								return false;
							}
							return true;
						}
					');

					if (array_key_exists('ckeditor', WT_Module::getActiveModules()) && WT_Site::preference('MAIL_FORMAT') == "1") {
						ckeditor_WT_Module::enableBasicEditor($controller);
					}

					$html .= $errors;

					$html .= '<form class="message_form" name="messageform" method="post" action="module.php?mod=' . $this->getName() . '&mod_action=show" onsubmit="t = new Date(); document.messageform.time.value=t.toUTCString(); return checkForm(this);">';
						if (!WT_USER_ID) {
							$html .= '<div class="message_note">
								<p>' . WT_I18N::translate('<b>Please Note:</b> Private information of living individuals will only be given to family relatives and close friends. You will be asked to verify your relationship before you will receive any private data. Sometimes information of dead persons may also be private. If this is the case, it is because there is not enough information known about the person to determine whether they are alive or not and we probably do not have more information on this person.<br /><br />Before asking a question, please verify that you are inquiring about the correct person by checking dates, places, and close relatives. If you are submitting changes to the genealogical data, please include the sources where you obtained the data.') . '</p>
								<label for "from_name" style="display: block; font-weight: 900;">' . WT_I18N::translate('Your Name:'). '</label>
								<input type="text" name="from_name" id="from_name" size="40" value="' . WT_Filter::escapeHtml($from_name). '" required>
								<label for "from_email" style="display: block; font-weight: 900;">' . WT_I18N::translate('Email Address:'). '</label>
								<input type="email" name="from_email" id="from_email" size="40" value="' . WT_Filter::escapeHtml($from_email). '" required>
								<p>' . WT_I18N::translate('Please provide your email address so that we may contact you in response to this message.	If you do not provide your email address we will not be able to respond to your inquiry.	Your email address will not be stored or used in any other way than responding to this inquiry.') . '</p>
								<hr>
							</div>';
						}
					$html .= '<div id="contact_forms">';
						for ($i = 1; $i <= $form_count; $i++) {
							$form_title	= $form_title_1;
							$to			= get_user_name(get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID'));
							$to_name	= getUserFullName(get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID'));
							if ($i > 1) {
								$form_title	= $form_title_2;
								$to			= get_user_name(get_gedcom_setting($ged_id, 'CONTACT_USER_ID'));
								$to_name	= getUserFullName(get_gedcom_setting($ged_id, 'CONTACT_USER_ID'));
							}
								$html .= WT_Filter::getCsrf();
								$html .= '<div class="contact_form">';
								$html .= $form_title;
								$html .= '<p>' . WT_I18N::translate('This message will be sent to %s', '<b>' . $to_name . '</b>') . '</p>';
								$html .= '
									<label for "subject' . $i . '" style="display: block; font-weight: 900;">' . WT_I18N::translate('Subject:'). '</label>
										<input type="hidden" name="action" value="send">
										<input type="hidden" name="to" value="' . WT_Filter::escapeHtml($to). '">
										<input type="hidden" name="time" value="">
										<input type="hidden" name="method" value="' . $method. '">
										<input type="hidden" name="url" value="' . WT_Filter::escapeHtml($url). '">
										<input type="text" name="subject" id="subject' . $i . '" value="' . WT_Filter::escapeHtml($subject). '" style="padding: 5px 3px; font-size: 1.2em; width: 100%;">
									<label for "body' . $i . '" style="display: block; font-weight: 900;">' . WT_I18N::translate('Body:'). '</label>
										<textarea class="html-edit" name="body" id="body' . $i . '" rows="7" style="padding: 5px 3px; font-size: 1.2em; width: 100%;">' . WT_Filter::escapeHtml($body). '</textarea>
									<div class="btn btn-primary" style="display: inline-block;margin:10px auto;">
										<button type="submit" value="value="' . WT_I18N::translate('Send'). '">' . WT_I18N::translate('Send'). '</button>
									</div>
								</div>';
						}
						if ($method == 'messaging') {
							$html .= '
							<p class="message_form" style="clear:both; width: 600px; margin:auto;" >' .
								WT_I18N::translate('When you send this message you will receive a copy sent via email to the address you provided.') . '
							</p>';
						}
					$html .= '</div>
					</form>';
				break;

			case 'send':
				if ($from_email) {
					$from = $from_email;
				}
				$message = array();
				$message['to'] = $to;
				$message['from'] = $from;
				if (!empty($from_name)) {
					$message['from_name'] = $from_name;
					$message['from_email'] = $from_email;
				}
				$message['subject'] = $subject;
				$message['body'] = $body;
				$message['method'] = $method;
				$message['url'] = $url;
				if (!addMessage($message)) {
					AddToLog('Unable to send message. FROM:' . $from . ' TO:' . $to . ' (failed to send)', 'error');
				}
				if ($url) {
					$return_to = $url;
				} else {
					$return_to = 'module.php?mod=' . $this->getName() . ' &mod_action=show';
				}
				$controller->addInlineJavascript('window.location.href="' . $return_to . ' ";');
			break;
		}

	$html .= '</div>';

		echo $html;
	}
}
