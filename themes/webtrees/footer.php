<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
?>
</div><!-- close the content div -->

<?php if ($view != 'simple') { ?>
	<div id="footer">
		<?php if (!array_key_exists('contact', KT_Module::getActiveModules())){ ?>
			<div class="contact_links">
                <a href="message.php?url=<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH . addslashes(rawurlencode(get_query_url())); ?>" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('Send Message'); ?>">
					<?php echo KT_I18N::translate('If you have any questions or comments please contact us'); ?>
					<i class="fa-envelope-o"></i>
				</a>
			</div>
		<?php } ?>
		<p class="logo">
			<a href="<?php echo KT_KIWITREES_URL; ?>" target="_blank" rel="noopener noreferrer" title="<?php echo KT_KIWITREES_URL; ?>">
				<?php echo /*I18N: kiwitrees logo on page footer */ KT_I18N::translate('Powered by %s', KT_KIWITREES); ?><span>&trade;</span>
			</a>
		</p>
	</div>
<?php }
