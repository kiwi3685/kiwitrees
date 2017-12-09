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

define('KT_SCRIPT_NAME', 'hourglass.php');
require './includes/session.php';

$controller = new KT_Controller_Hourglass();
$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		sizeLines();
	')
	->setupJavascript();

echo '<div id="hourglass-page">';
echo '<h2>', KT_I18N::translate('Hourglass chart of %s', $controller->name), '</h2>';

$gencount=0;
?>
<!-- // NOTE: Start form and table -->
	<form method="get" name="people" action="?">
	<input type="hidden" name="show_full" value="<?php echo $controller->show_full; ?>">
	<table class="list_table"><tr>

		<!-- // NOTE: Root ID -->
	<td class="descriptionbox">
	<?php echo KT_I18N::translate('Individual'); ?>
	</td>
	<td class="optionbox">
	<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="rootid" id="rootid" size="3" value="<?php echo $controller->pid; ?>">
	<?php echo print_findindi_link('pid'); ?>
	</td>

	<!-- // NOTE: Show Details -->
	<td class="descriptionbox">
	<?php echo KT_I18N::translate('Show Details'); ?>
	</td>
	<td class="optionbox">
	<input type="checkbox" value="<?php
	if ($controller->show_full) echo "1\" checked=\"checked\" onclick=\"document.people.show_full.value='0';";
	else echo "0\" onclick=\"document.people.show_full.value='1';"; ?>"
	>
	</td>

	<!-- // NOTE: Submit button -->
	<td rowspan="3" class="topbottombar vmiddle">
	<input type="submit" value="<?php echo KT_I18N::translate('View'); ?>">
	</td></tr>

	<!-- // NOTE: Generations -->
	<tr><td class="descriptionbox" >
	<?php echo KT_I18N::translate('Generations'); ?>
	</td>
	<td class="optionbox">
	<select name="generations">
	<?php
	for ($i=2; $i<=$MAX_DESCENDANCY_GENERATIONS; $i++) {
		echo "<option value=\"".$i."\"" ;
		if ($i == $controller->generations) echo " selected=\"selected\"";
		echo ">".KT_I18N::number($i)."</option>";
	}
	?>
	</select>
	</td>

	<!-- // NOTE: Show spouses -->
	<td class="descriptionbox">
	<?php echo KT_I18N::translate('Show spouses'), help_link('show_spouse'); ?>
	</td>
	<td class="optionbox">
	<input type="checkbox" value="1" name="show_spouse"
	<?php
	if ($controller->show_spouse) echo " checked=\"checked\""; ?>>
	</td></tr>

	<!-- // NOTE: Box width -->
	<tr><td class="descriptionbox">
	<?php echo KT_I18N::translate('Box width'); ?>
	</td>
	<td class="optionbox"><input type="text" size="3" name="box_width" value="<?php echo $controller->box_width; ?>">
	<b>%</b>
	</td>

	<!-- // NOTE: Empty field -->
	<td class="descriptionbox">&nbsp;</td><td class="optionbox">&nbsp;</td></tr>

	<!-- // NOTE: End table and form -->
	</table></form>

	<!-- // NOTE: Close table header -->
	</td></tr></table>
	<div id="hourglass_chart" <?php echo "style=\"width:98%; z-index:1;\""; ?> >
		<table cellspacing="0" cellpadding="0" border="0"><tr>
		<!-- // descendancy -->
		<td valign="middle">
		<?php
		$controller->print_descendency(KT_Person::getInstance($controller->pid), 1); ?>
		</td>
		<!-- // pedigree -->
		<td valign="middle">
		<?php
		$controller->print_person_pedigree(KT_Person::getInstance($controller->pid), 1); ?>
		</td>
		</tr></table>
	</div><!-- close #hourglass_chart -->
</div> <!-- close #hourglass-page -->
