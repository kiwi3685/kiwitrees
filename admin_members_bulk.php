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

define('KT_SCRIPT_NAME', 'admin_members_bulk.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Send broadcast messages'))
	->pageHeader();

?>
<div id="users_bulk">
	<p>
		<a href="message.php?to=all&amp;method=messaging" target="_blank">
			<?php echo KT_I18N::translate('Send message to all users'); ?>
		</a>
	</p>
	<p>
		<a href="message.php?to=never_logged&amp;method=messaging" target="_blank">
			<?php echo KT_I18N::translate('Send message to users who have never logged in'); ?>
		</a>
	</p>
	<p>
		<a href="message.php?to=last_6mo&amp;method=messaging" target="_blank">
			<?php echo KT_I18N::translate('Send message to users who have not logged in for 6 months'); ?>
		</a>
	</p>
</div>
