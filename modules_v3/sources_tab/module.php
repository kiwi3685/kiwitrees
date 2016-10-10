<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
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

class sources_tab_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Sources');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Sources” module */ WT_I18N::translate('A tab showing the sources linked to an individual.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 30;
	}

	// Implement WT_Module_Tab
	public function defaultAccessLevel() {
		return false;
	}

	protected $sourceCount = null;

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $NAV_SOURCES, $controller;

		ob_start();
		?>
		<table class="facts_table">
			<tr>
				<td colspan="2" class="descriptionbox rela">
					<input id="checkbox_sour2" type="checkbox">
					<label for="checkbox_sour2"><?php echo WT_I18N::translate('Show all sources'), help_link('show_fact_sources'); ?></label>
				</td>
			</tr>
			<?php
			$otheritems = $controller->getOtherFacts();
				foreach ($otheritems as $event) {
					if ($event->getTag()=='SOUR') {
						print_main_sources($event, 1);
					}
			}
			// 2nd level sources [ 1712181 ]
			$controller->record->add_family_facts(false);
			foreach ($controller->getIndiFacts() as $event) {
				print_main_sources($event, 2);
			}
			if ($this->get_source_count()==0) echo "<tr><td id=\"no_tab3\" colspan=\"2\" class=\"facts_value\">".WT_I18N::translate('There are no Source citations for this individual.')."</td></tr>";
			//-- New Source Link
			if ($controller->record->canEdit()) {
			?>
				<tr>
					<td class="facts_label"><?php echo WT_Gedcom_Tag::getLabel('SOUR'); ?></td>
					<td class="facts_value">
					<a href="#" onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','SOUR'); return false;"><?php echo WT_I18N::translate('Add a source citation'); ?></a>
					<?php echo help_link('add_source'); ?>
					</td>
				</tr>
			<?php
			}
		?>
		</table>
		<br>
		<script>
			persistent_toggle("checkbox_sour2", "tr.row_sour2");
		</script>
		<?php
		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}

	function get_source_count() {
		global $controller;

		if ($this->sourceCount===null) {
			$ct = preg_match_all("/\d SOUR @(.*)@/", $controller->record->getGedcomRecord(), $match, PREG_SET_ORDER);
			foreach ($controller->record->getSpouseFamilies() as $sfam)
				$ct += preg_match("/\d SOUR /", $sfam->getGedcomRecord());
			$this->sourceCount = $ct;
		}
		return $this->sourceCount;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return WT_USER_CAN_EDIT || $this->get_source_count()>0;
	}
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return $this->get_source_count()==0;
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
