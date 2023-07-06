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

class float_contacts_KT_Module extends KT_Module implements KT_Module_Config {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: The name of a module. Dropbox is a trademark.  Do not translate it. */KT_I18N::translate('Floating contact link'); //CHANGE THIS
	}

	// Extend class KT_Module
	public function getDescription() {
		return KT_I18N::translate('A floating link button for contact messaging');
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return false;

	}

	// Extend KT_Module
	public function modAction($mod_action) {
		return false;

	}

	// Extend KT_Module
	static function show() {
	?>
		<div id="floating_contact">
			<link type="text/css" href="<?php echo KT_STATIC_URL . KT_MODULES_DIR; ?>float_contacts/css/style.css" rel="stylesheet">
			<button>
				<a href="message.php?url=<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH . addslashes(rawurlencode(get_query_url())); ?>" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('Send Message'); ?>">
					<i class="fa fa-commenting-o"></i>
				</a>
			</button>
		</div>

	<?php }


}
