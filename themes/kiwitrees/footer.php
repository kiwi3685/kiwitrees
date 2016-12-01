<?php
// Footer for kiwitrees theme
//
// kiwitrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView and webtrees
// Copyright (C) 2002 to 2009 PGV Development Team
// Copyright (C) 2010 to 2013  webtrees Development Team.  All rights reserved.
//
// This is free software;you can redistribute it and/or modify
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
?>
</div><!-- close the content div -->

<?php if ($view != 'simple') { ?>
	<div id="footer">
		<?php if (contact_links() != '' && !array_key_exists('contact', WT_Module::getActiveModules())) echo contact_links(); ?>
		<p class="logo">
			<a href="<?php echo WT_WEBTREES_URL; ?>" target="_blank" rel="noopener noreferrer" title="<?php echo WT_WEBTREES_URL; ?>">
				<?php echo /*I18N: kiwitrees logo on page footer */ WT_I18N::translate('Powered by %s', WT_WEBTREES); ?><span>&trade;</span>
			</a>
		</p>
	</div>
<?php }
