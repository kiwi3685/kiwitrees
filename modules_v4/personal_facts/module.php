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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class personal_facts_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ WT_I18N::translate('Facts and events');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Facts and events” module */ WT_I18N::translate('A tab showing the facts and events of an individual.');
	}

	// Extend class WT_Module_Tab
	public function defaultAccessLevel() {
		return false;
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 10;
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $SHOW_RELATIVES_EVENTS, $controller;

		if ($SHOW_RELATIVES_EVENTS) :
			$controller->record->add_family_facts();
		endif;

		ob_start();
		echo '<table class="facts_table">';
		$indifacts = $controller->getIndiFacts();
		if (count($indifacts) == 0) {
			echo '<tr><td colspan="2" class="facts_value">', WT_I18N::translate('There are no Facts for this individual.'), '</td></tr>';
		}?>
			<tr id="row_top">
				<td colspan="2" class="descriptionbox rela">
					<?php if ($SHOW_RELATIVES_EVENTS) : ?>
						<input id="checkbox_rela_facts" type="checkbox">
						<label for="checkbox_rela_facts"><?php echo WT_I18N::translate('Events of close relatives'); ?></label>
					<?php endif; ?>
					<?php if (file_exists(WT_Site::preference('INDEX_DIRECTORY') . 'histo.' . WT_LOCALE . '.php')) : ?>
						<input id="checkbox_histo" type="checkbox">
						<label for="checkbox_histo"><?php echo WT_I18N::translate('Historical facts'); ?></label>
					<?php endif; ?>
				</td>
			</tr>
		<?php
		foreach ($indifacts as $fact) {
			if ($fact->getParentObject() instanceof WT_Family) {
				// Print all family facts
				print_fact($fact, $controller->record);
			} else {
				// Individual/reference facts (e.g. CHAN, IDNO, RFN, AFN, REFN, RIN, _UID) can be shown in the sidebar
				if (!array_key_exists('extra_info', WT_Module::getActiveSidebars()) || !extra_info_WT_Module::showFact($fact)) {
					print_fact($fact, $controller->record);
				}

			}
		}
		//-- new fact link
		if ($controller->record->canEdit()) {
			print_add_new_fact($controller->record->getXref(), $indifacts, 'INDI');
		} ?>
		</table>
		<script>
			persistent_toggle("checkbox_rela_facts", "tr.row_rela");
			persistent_toggle("checkbox_histo", "tr.row_histo");
		</script>
		<?php
		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return true;
	}

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

}
