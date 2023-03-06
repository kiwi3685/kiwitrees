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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_user_contacts_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('User contacts');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “User contact” module */ KT_I18N::translate('Links for contacting any user direct via email');
	}

	// Implement KT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 15;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {
		$id			= $this->getName();
		$class		= $this->getName();
		$title		= $this->getTitle();
		$content	= '';

		//list all users for inter-user communication, only when logged in, and there is more than one user -->
		if (KT_USER_ID) {
			$content .= '<div id="contact_page">
				<form name="messageform" action="message.php">
					<input type="hidden" name="url" value="' . KT_Filter::escapeHtml(KT_SERVER_NAME . KT_SCRIPT_PATH . get_query_url()) . '">
					<div class="contact_form">
						<div class="option">
							<label for="to_name">' . KT_I18N::translate('To') . '</label>
							<!-- list all users for inter-user communication, only when logged in, and there is more than one user -->
							<select name="to">';
								if (get_user_count() > 1) {
									$content .= '<option value="">' . KT_I18N::translate('Select') . '</option>';
								}
								foreach (get_all_users() as $user_id => $user_name) {
								// don't list yourself; unverified users; or users with contact method = none //
									if ($user_id != KT_USER_ID && get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'contactmethod') != 'none') {
										$content .= '<option value="' . $user_name . '">
											<span dir="auto">' . htmlspecialchars(getUserFullName($user_id)) . '</span> - <span dir="auto">' . $user_name . '</span>
										</option>';
									}
								}
							$content .= '</select>
						</div>
						<p id="save-cancel">
							<button class="btn btn-primary" type="submit">
								<i class="fa fa-pencil-square-o"></i>' .
								KT_I18N::translate('Write message') . '
							</button>
						</p>
					</div>
				</form>
			</div>';
		} else {
			$content .= KT_I18N::translate('This feature is for registered members only.');
		}

		if ($template) {
			require KT_THEME_DIR . 'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Widget
	public function configureBlock($widget_id) {
		return false;
	}

}
