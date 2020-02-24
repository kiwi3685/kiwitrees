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

class notes_KT_Module extends KT_Module implements KT_Module_Tab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Notes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Notes” module */ KT_I18N::translate('A tab showing the notes attached to an individual.');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 40;
	}

	protected $noteCount = null;

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $NAV_NOTES, $controller;

		ob_start();
		?>
		<table class="facts_table">
			<tr>
				<td colspan="2" class="descriptionbox rela">
					<input id="checkbox_note2" type="checkbox">
					<label for="checkbox_note2"><?php echo KT_I18N::translate('Show all notes'); ?></label>
					<?php echo help_link('show_fact_sources'); ?>
				</td>
			</tr>
		<?php
		$globalfacts = $controller->getGlobalFacts();
		foreach ($globalfacts as $event) {
			$fact = $event->getTag();
			if ($fact=='NAME') {
				print_main_notes($event, 2);
			}
		}
		$otherfacts = $controller->getOtherFacts();
		foreach ($otherfacts as $event) {
			$fact = $event->getTag();
			if ($fact=='NOTE') {
				print_main_notes($event, 1);
			}
		}
		// 2nd to 5th level notes/sources
		$controller->record->add_family_facts(false);
		foreach ($controller->getIndiFacts() as $factrec) {
			for ($i=2; $i<6; $i++) {
				print_main_notes($factrec, $i);
			}
		}
		if ($this->get_note_count()==0) {
			echo '<tr><td id="no_tab2" colspan="2" class="facts_value">', KT_I18N::translate('There are no Notes for this individual.'), '</td></tr>';
		}
		//-- New Note Link
		if ($controller->record->canEdit()) {
			?>
		<tr>
			<td class="facts_label"><?php echo KT_Gedcom_Tag::getLabel('NOTE'); ?></td>
			<td class="facts_value">
				<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','NOTE'); return false;">
					<?php echo KT_I18N::translate('Add a note'); ?>
				</a>
				<?php echo help_link('add_note'); ?>
			</td>
		</tr>
		<tr>
			<td class="facts_label"><?php echo KT_Gedcom_Tag::getLabel('SHARED_NOTE'); ?></td>
			<td class="facts_value">
				<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','SHARED_NOTE'); return false;">
					<?php echo KT_I18N::translate('Add a shared note'); ?>
				</a>
				<?php echo help_link('add_shared_note'); ?>
			</td>
		</tr>
		<?php
		}
		?>
		</table>
		<br>
		<script>
			persistent_toggle("checkbox_note2", "tr.row_note2");
		</script>
		<?php
		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}

	function get_note_count() {
		global $controller;

		if ($this->noteCount===null) {
			$ct = preg_match_all("/\d NOTE /", $controller->record->getGedcomRecord(), $match, PREG_SET_ORDER);
			foreach ($controller->record->getSpouseFamilies() as $sfam)
			$ct += preg_match("/\d NOTE /", $sfam->getGedcomRecord());
			$this->noteCount = $ct;
		}
		return $this->noteCount;
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->get_note_count()>0;
	}
	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return $this->get_note_count()==0;
	}
	// Implement KT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// Implement KT_Module_Tab
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

}
