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

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Regnal Years"; /* I18N: Name of a plugin. */ KT_I18N::translate('Regnal Years');

// HELP //
$help1 = htmlspecialchars(addslashes(KT_I18N::translate('<div id="popup"><p>There are several different areas. They operate as follows.</p><p><b>1. Calculation of major moveable feasts.</b></p><p>Since the ecclesiastical calendar is based on lunar rather than solar cycles, certain key holidays (feasts) occur on different days each year. The method of calculating these feasts has also changed since the council of Nicea (325 A.D.). The button labeled "Calculate Holidays" calculates the dates of seven major feasts for the year entered in the field labeled "Year", and displays the results below this field. The only restrictions are that the number entered in the field "Year" must be an integer (no fractions) greater than zero. However, the holidays generated are only valid for dates since 325 A.D. (Early Christian and Roman dating is another story). Also, for purposes of calculation, I have assumed that the ecclesiastical year begins on January first, even though this standard was only gradually accepted. If you are working with early monastic documents you might want to consider that dates from December 25th through March may be "off" by one year. To a Benedictine, for instance (to whom the year began on December 25th), the feast of the Innocents in 1450 would be December 28th, 1450, while to others it might be December 28th, 1449. In fact, before 1582, most calendars did not have the year begin on January 1st, even though the calculation of the moveable feasts acted as if it did. In England, the year "began" either on December 25th, or, more frequently on March 25th (Lady Day), until 1752. These vagaries are not something I wanted to include in the calculations, since they often varied quite a bit. The calculations for the holidays will take into account the days dropped from the calendar when the "New style" was adopted, since these affect the month and day of Easter. Conventions about the beginning of the year are easily corrected for.</p><p><b>2. Old and New style dating and Day of the week</b></p><p>When the Pope Gregory revised the calendar in 1582, a certain number of days were omitted from the calendar at a particular time, resulting in two separate styles of dating. England persisted in using the "Old Style" until 1752, because of religious differences. The Old Style also often dated the beginning of the year from March 25 rather than January 1, and this area of the site takes this difference into account. Thus March 8, 1735, Old Style is really March 19, 1736 in the New Style! Occasionally, particularly in dating material between 1582 and 1755 or so, it becomes necessary to convert back and forth.</p><p>Here is a more detailed description of the transition year (in England): 1752.</p><p>The area called "Convert Old Style to New Style" converts the old style date to a new style date and returns an answer. The area called "Convert New Style to Old Style" converts the new style date to an old style date and enters it in the "Old style" fields. You must enter an integer greater than zero for the year.</p><p>You can also calculate the day of the week for both old and new style dates.</p><p>Remember that these forms assume that the year begins on March 25th. If you want to plug in dates derived from the "Ecclesiastical Holidays" area, be aware that you will have to subtract 1 from the year for all dates between January 1st and March 24th.</p><p><b>NOTE FOR STUDENTS OF CONTINENTAL HISTORY</b></p><p>Don\'t try to use this site to calculate dates of documents between 1588 and 1752 because England is a special case during these years.</p></p>')), ENT_QUOTES);

$help2 = 'http://en.wikisource.org/wiki/1911_Encyclop%C3%A6dia_Britannica/Calendar/Ecclesiastical_Calendar_-_Easter';

// DISPLAY
$html.='
	<style type="text/css">
		#regnal form {font-size: 1em;}
		#regnal p {padding:0;}
		#regnal label.main {text-align:left; width:auto; font-size: 1em; font-weight:bold; display:block;}
		#regnal label.secondary {text-align:left; width:auto; font-size: 0.9em; margin:10px; line-height:40px;}
		#acknowledgement{background:#fff; border:1px solid #c0c0c0; border-radius:8px; float:right; font-size:12px; width:250px; margin:0 30px; padding:10px;}
		#acknowledgement p{padding:5px;}
		#acknowledgement i{cursor:pointer; color:blue; margin:0; vertical-align:baseline;}
		#regnal	.note{font-style:italic; padding:5px; white-space:normal; font-weight:normal;}
	</style>
	<div id="regnal">' .
		/* I18N: Help comments for a calendar utility */
		KT_I18N::translate('<p>Dates based on regnal years refer to the year of the current monarch\'s reign and usually have the format "year + monarch" (e.g. "4 Mary"). This page will take a date in regnal years and return an ordinary date. For instance, if you enter 6/11 Elizabeth I, Regnal year 1, you will get the year 1559 because June 1st in her first regnal year occurred in 1559.</p>') . '
		<div id="acknowledgement">' .
			/* I18N: Acknowledgement of origin for a calendar utility */
			KT_I18N::translate('
				<p>
					This page is based on work done by Ian MacInnes <span class="note">(imacinnes@albion.edu)</span> at his website <a href="http://people.albion.edu/imacinnes/calendar/Welcome.html" target="blank"><b>Ian\'s English Calendar</b></a></p>
				<p>
					His site is intended to replace quick reference handbooks of dates for those interested in English history, literature, and genealogy. It is also accurate for European history outside of England, with the exception of the period 1582-1752. Students of Continental documents who wish to date documents from this period will need to follow this link
				</p>
				<p>
					<i title="Help with English calendar" onclick="modalNotes(\'%1s\', \'Help with English calendar\')">Help with English calendar</i>
				</p>
				<p>
					<a title="Easter Formulae" href="%2s" target="_blank" rel="noopener noreferrer">
						<i>The formulae for calculating Easter are derived from the 11th edition Encyclopedia Brittanica</i>
					</a>
				</p>
			', $help1, $help2) . '
		</div>
		<form name="RegnalYear">
			<label class="main">' . KT_I18N::translate('Enter the month and day') . '</label>
				<label class="secondary">' . KT_I18N::translate('Month number') . '</label>
				<select name="Month" size="1">';
					for ($i=1; $i<13; ++$i) {
						$html .= '<option value="' . $i . '"';
						if ($i == 1) $html .= ' selected';
						$html .= '>' . $i;
					}
				$html .= '</select>
				<label class="secondary">' . KT_I18N::translate('Day') . '</label>
				<select name="Day" size="1">';
					for ($i=1; $i<32; ++$i) {
						$html .= '<option value="' . $i . '"';
						if ($i == 1) $html .= ' selected';
						$html .= '>' . $i;
					}
				$html .= '</select>
			</label>
			<label class="main">' . KT_I18N::translate('Enter the Monarch and Regnal Year') . '
				<label class="secondary">' . KT_I18N::translate('Monarch') . ':</label>
				<select name="Monarch" size="1">
					<option selected value="William I">' . KT_I18N::translate('William I') . '
					<option value="WilliamII">' . KT_I18N::translate('William II') . '
					<option value="HenryI">' . KT_I18N::translate('Henry I') . '
					<option value="Stephen">' . KT_I18N::translate('Stephen') . '
					<option value="HenryII">' . KT_I18N::translate('Henry II') . '
					<option value="RichardI">' . KT_I18N::translate('Richard I') . '
					<option value="John">' . KT_I18N::translate('John') . '
					<option value="HenryIII">' . KT_I18N::translate('Henry III') . '
					<option value="EdwardI">' . KT_I18N::translate('Edward I') . '
					<option value="EdwardII">' . KT_I18N::translate('Edward II') . '
					<option value="EdwardIII">' . KT_I18N::translate('Edward III') . '
					<option value="RichardII">' . KT_I18N::translate('Richard II') . '
					<option value="HenryIV">' . KT_I18N::translate('Henry IV') . '
					<option value="HenryV">' . KT_I18N::translate('Henry V') . '
					<option value="HenryVI">' . KT_I18N::translate('Henry VI') . '
					<option value="EdwardIV">' . KT_I18N::translate('Edward IV') . '
					<option value="EdwardV">' . KT_I18N::translate('Edward V') . '
					<option value="RichardIII">' . KT_I18N::translate('Richard III') . '
					<option value="HenryVII">' . KT_I18N::translate('Henry VII') . '
					<option value="HenryVIII">' . KT_I18N::translate('Henry VIII') . '
					<option value="EdwardVI">' . KT_I18N::translate('Edward VI') . '
					<option value="Jane">' . KT_I18N::translate('Jane') . '
					<option value="Mary">' . KT_I18N::translate('Mary') . '
					<option value="ElizabethI">' . KT_I18N::translate('Elizabeth I') . '
					<option value="JamesI">' . KT_I18N::translate('James I') . '
					<option value="CharlesI">' . KT_I18N::translate('Charles I') . '
					<option value="CharlesII">' . KT_I18N::translate('Charles II') . '
					<option value="JamesII">' . KT_I18N::translate('James II') . '
					<option value="WilliamandMary">' . KT_I18N::translate('William and Mary (William III)') . '
					<option value="Anne">' . KT_I18N::translate('Anne') . '
					<option value="GeorgeI">' . KT_I18N::translate('George I') . '
					<option value="GeorgeII">' . KT_I18N::translate('George II') . '
					<option value="GeorgeIII">' . KT_I18N::translate('George III') . '
					<option value="GeorgeIV">' . KT_I18N::translate('George IV') . '
					<option value="William IV">' . KT_I18N::translate('William IV') . '
					<option value="Victoria">' . KT_I18N::translate('Victoria') . '
					<option value="EdwardVII">' . KT_I18N::translate('Edward VII') . '
					<option value="GeorgeV">' . KT_I18N::translate('George V') . '
					<option value="EdwardVIII">' . KT_I18N::translate('Edward VIII') . '
					<option value="GeorgeVI">' . KT_I18N::translate('George VI') . '
					<option value="ElizabethII">' . KT_I18N::translate('Elizabeth II') . '
				</select>
			</label>
			<label class="main">' . KT_I18N::translate('Regnal Year') . '
				<label class="secondary">
					<input type="text" name="regnalyear" size="2" maxlength="2">
				</label>
			</label>
			<p>
				<input class="button_ec" type="button" value="' . KT_I18N::translate('Calculate Year') . '" name="CalculateChristianYear" onclick="Calculate()">
				<input type="text" name="Answer" size="10" maxlength="10" readonly>
			</p>
			<p class="note">' . KT_I18N::translate('Double years such as &quot;1324/5&quot; reflect Old Style dating (see note below).') . '</p>
			<label class="main">' . KT_I18N::translate('Notes') . '
				<ol>
					<li class="note">' . KT_I18N::translate('Keep in mind that a monarch\'s last regnal year is cut short by death or deposition and may not include all dates.') . '</li>
					<li class="note">' . KT_I18N::translate('Also, remember that while the so-called &quot;Christian year&quot; began on January 1, the legal year began on March 25. For example, January 1 in Elizabeth I\'s 1st regnal year occurred in the Christian year1559, but legally the year was still 1558, and would be until March 25. From January 1 to March 24, the convention is to note both years with a slash between them, e.g. &quot;1558/9.&quot;') . '</li>
					<li class="note">' . KT_I18N::translate('Remember also that from 1582 onward, English dates (Old Style) will not correspond with Continental dates.') . '</li>
				</ol>
			</label>
		</form>
	</div>
';
?>
<!-- SCRIPTS -->
 <script><!--
	function Calculate() {

		var Month=parseInt(document.RegnalYear.Month.value)
		var Day=parseInt(document.RegnalYear.Day.value)
		var Monarch = document.RegnalYear.Monarch.value
		var regnalyear=parseInt(document.RegnalYear.regnalyear.value)

		if (isNaN(regnalyear)){
			alert("<?php echo KT_I18N::translate('You must enter a regnal year'); ?>")
			regnalyear = "error"
		}


		if (Monarch == "William I"){
			if (regnalyear > 21){
				alert("<?php echo KT_I18N::translate('William I only reigned for 21 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1065
			if (Month == 12){
				if (Day < 25){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 12) {
				regnalyear = regnalyear + 1
			}
		}

	//alert(regnalyear)
		if (Monarch == "WilliamII"){
			if (regnalyear > 13){
				alert("<?php echo KT_I18N::translate('William II only reigned for 13 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1086
			if (Month == 9){
				if (Day < 26){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 9) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryI"){
			if (regnalyear > 36){
				alert("<?php echo KT_I18N::translate('Henry I only reigned for 36 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1099
			if (Month == 8){
				if (Day < 5){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 8) {
				regnalyear = regnalyear + 1
			}
		}


		if (Monarch == "Stephen"){
			if (regnalyear > 19){
				alert("<?php echo KT_I18N::translate('Stephen only reigned for 19 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1134
			if (Month == 12){
				if (Day < 22){regnalyear = regnalyear +1
				}
			}
			if (Month < 12) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryII"){
			if (regnalyear > 35){
				alert("<?php echo KT_I18N::translate('Henry II only reigned for 35 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1153
			if (Month == 12){
				if (Day < 19){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 12) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "RichardI"){
			if (regnalyear > 10){
				alert("<?php echo KT_I18N::translate('Richard I only reigned for 10 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1188
			if (Month == 9){
				if (Day < 3){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 9) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryIII"){
			if (regnalyear > 57){
				alert("<?php echo KT_I18N::translate('Henry III only reigned for 57 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1215
			if (Month == 10){
				if (Day < 28){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 10) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardI"){
			if (regnalyear > 35){
				alert("<?php echo KT_I18N::translate('Edward I only reigned for 35 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1271
			if (Month == 11){
				if (Day < 20){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 11) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardII"){
			if (regnalyear > 20){
				alert("<?php echo KT_I18N::translate('Edward II only reigned for 20 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1306
			if (Month == 7){
				if (Day < 8){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 7) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardIII"){
			if (regnalyear > 51){
				alert("<?php echo KT_I18N::translate('Edward III only reigned for 51 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1326
			if (Month == 1){
				if (Day < 25){
					regnalyear = regnalyear +1
				}
			}
		}

		if (Monarch == "RichardII"){
			if (regnalyear > 23){
				alert("<?php echo KT_I18N::translate('Richard II only reigned for 23 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1376
			if (Month == 6){
				if (Day < 22){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryIV"){
			if (regnalyear > 14){
				alert("<?php echo KT_I18N::translate('Henry IV only reigned for 14 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1398
			if (Month == 9){
				if (Day < 30){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 9) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryV"){
			if (regnalyear > 10){
				alert("<?php echo KT_I18N::translate('Henry V only reigned for 10 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1412
			if (Month == 3){
				if (Day < 21){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 3) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryVI"){
			if (regnalyear > 39){
				alert("Henry VI only reigned for 39 years")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1421
			if (Month == 8){
				regnalyear = regnalyear +1
			}
			if (Month < 8) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardIV"){
			if (regnalyear > 23){
				alert("<?php echo KT_I18N::translate('Edward IV only reigned for 23 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1460
			if (Month == 3){
				if (Day < 4){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 3) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardV"){
			if (regnalyear > 1){
				alert("<?php echo KT_I18N::translate('Edward V only reigned for 1 year'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1482
			if (Month == 4){
				if (Day < 9){
					alert("<?php echo KT_I18N::translate('Edward V\'s reign lasted from 9 April to 25 June'); ?>")
					regnalyear = "error"
				}
			}
			if (Month < 4) {
				alert("<?php echo KT_I18N::translate('Edward V\'s reign lasted from 9 April to 25 June'); ?>")
				regnalyear = "error"
			}
			if (Month == 6){
				if (Day > 25){
					alert("<?php echo KT_I18N::translate('Edward V\'s reign lasted from 9 April to 25 June'); ?>")
					regnalyear = "error"
				}
			}
			if (Month > 6){
				alert("<?php echo KT_I18N::translate('Edward V\'s reign lasted from 9 April to 25 June'); ?>")
				regnalyear = "error"
			}
		}


		if (Monarch == "RichardIII"){
			if (regnalyear > 3){
				alert("<?php echo KT_I18N::translate('Richard III only reigned for 3 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1482
			if (Month == 6){
				if (Day < 26){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryVII"){
			if (regnalyear > 24){
				alert("<?php echo KT_I18N::translate('Henry VII only reigned for 24 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1484
			if (Month == 8){
				if (Day < 22){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 8) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "HenryVIII"){
			if (regnalyear > 38){
				alert("<?php echo KT_I18N::translate('Henry VIII only reigned for 38 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1508
			if (Month == 4){
				if (Day < 22){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 4) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardVI"){
			if (regnalyear > 7){
				alert("<?php echo KT_I18N::translate('Edward VI only reigned for 7 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1546
			if (Month == 1){
				if (Day < 28){
					regnalyear = regnalyear +1
				}
			}
		}

		if (Monarch == "ElizabethI"){
			if (regnalyear > 45){
				alert("<?php echo KT_I18N::translate('Elizabeth I only reigned for 45 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1557
			if (Month == 11){
				if (Day < 17){
					regnalyear = regnalyear +1

				}
			}
			if (Month < 11) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "JamesI"){
			if (regnalyear > 23){
				alert("<?php echo KT_I18N::translate('James I had only 23 regnal years as King of England'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1602
			if (Month == 3){
				if (Day < 24){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 3) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "CharlesI"){
			if (regnalyear > 24){
				alert("<?php echo KT_I18N::translate('Charles I only reigned for 24 years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1624
			if (Month == 3){
				if (Day < 27){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 3) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "CharlesII"){
			if (regnalyear > 37){
				alert("<?php echo KT_I18N::translate('Charles II only had 37 regnal years'); ?>")
				regnalyear = "error"
			}
			regnalyear = regnalyear + 1648
			if (Month == 1){
				if (Day < 30){
					regnalyear = regnalyear +1
				}
			}
		}

		if (Monarch == "JamesII"){
			if (regnalyear > 4){
				alert("<?php echo KT_I18N::translate('James II only reigned for 4 years'); ?>")
			regnalyear = "error"
				}
			regnalyear = regnalyear + 1684
			if (Month == 2){
				if (Day < 6){
					regnalyear = regnalyear +1
				}
			}
			if (Month == 1) {
				regnalyear = regnalyear + 1
			}
		}
		if (Monarch == "Mary"){
			if (regnalyear > 6){
				alert("<?php echo KT_I18N::translate('Mary only reigned for 4 years'); ?>")
				regnalyear = "error"
			}
			alert("<?php echo KT_I18N::translate('This site calculates the regnal years for Mary, ignoring Philip and his regnal years. The calculation of regnal years between July 9, 1553 and November 17, 1558 is complicated by Mary\'s marriage to Philip.'); ?>")
			regnalyear = regnalyear + 1552
			if (Month == 7){
				if (Day < 6){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 7) {
				regnalyear = regnalyear + 1
			}
		}

	if (Monarch == "William and Mary"){
			if (regnalyear > 14){
				alert("<?php echo KT_I18N::translate('William only reigned for 14 years (the first six with Mary)'); ?>")
				return
			}
			regnalyear = regnalyear + 1688
			if (Month == 2){
				if (Day < 13){
					regnalyear = regnalyear +1
				}
			}
			if (Month == 1) {
				regnalyear = regnalyear + 1
			}
		}
		if (Monarch == "Anne"){
			if (regnalyear > 13){
				alert("<?php echo KT_I18N::translate('Anne only reigned for 13 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1701
			if (Month == 3){
				if (Day < 8){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 3) {
				regnalyear = regnalyear + 1
			}
		}

	if (Monarch == "GeorgeI"){
			if (regnalyear > 13){
				alert("<?php echo KT_I18N::translate('George I only reigned for 13 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1713
			if (Month < 8) {
				regnalyear = regnalyear + 1
			}
		}
	if (Monarch == "GeorgeII")
		{
			if (regnalyear > 34){
				alert("<?php echo KT_I18N::translate('George II only reigned for 34 years.'); ?>")
				return
			}
			if (regnalyear > 26){
				regnalyear = regnalyear + 1726
			if (Month == 6){
				if (Day < 22){
					regnalyear = regnalyear +1}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1}
			}

			if (regnalyear < 27){
				regnalyear = regnalyear + 1726
			if (Month == 6){
				if (Day < 11){
					regnalyear = regnalyear +1}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1}
			}
		}
		if (Monarch == "GeorgeIII"){
			if (regnalyear > 60){
				alert("<?php echo KT_I18N::translate('George III only reigned for 60 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1759
			if (Month == 10){
				if (Day < 25){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 10) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "GeorgeIV"){
			if (regnalyear > 11){
				alert("<?php echo KT_I18N::translate('George III only reigned for 11 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1819
			if (Month == 1){
				if (Day < 29){
					regnalyear = regnalyear +1
				}
			}
		}

		if (Monarch == "William IV"){
			if (regnalyear > 7){
				alert("<?php echo KT_I18N::translate('William IV only reigned for 7 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1829
			if (Month == 6){
				if (Day < 26){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "Victoria"){
			if (regnalyear > 64){
				alert("<?php echo KT_I18N::translate('Victoria only reigned for 64 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1836
			if (Month == 6){
				if (Day < 20){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 6) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardVII"){
			if (regnalyear > 10){
				alert("<?php echo KT_I18N::translate('Edward VII only reigned for 10 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1900
			if (Month == 1){
				if (Day < 22){
					regnalyear = regnalyear +1
				}
			}
		}

		if (Monarch == "GeorgeV"){
			if (regnalyear > 26){
				alert("<?php echo KT_I18N::translate('George V only reigned for 26 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1909
			if (Month == 5){
				if (Day < 6){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 5) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "GeorgeVI"){
			if (regnalyear > 16){
				alert("<?php echo KT_I18N::translate('George VI only reigned for 16 years.'); ?>")
				return
			}
			regnalyear = regnalyear + 1935
			if (Month == 12){
				if (Day < 11){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 12) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "ElizabethII"){
			regnalyear = regnalyear + 1951
			if (Month == 2){
				if (Day < 6){
					regnalyear = regnalyear +1
				}
			}
			if (Month < 2) {
				regnalyear = regnalyear + 1
			}
		}

		if (Monarch == "EdwardVIII"){
			alert("<?php echo KT_I18N::translate('Edward VIII only reigned from Jan 20 to December 11, 1936.'); ?>")
			regnalyear = 1936
		}

		if (Monarch == "John") {
			alert("<?php echo KT_I18N::translate('John was crowned on Ascension day in 1199. His regnal years run from one Ascension day to the next and are thus highly irregular. Use the Ecclesiastical calendar page to calculate Ascension day'); ?>")
			regnalyear = "error"
		}

		if (Monarch == "Jane") {
			alert("<?php echo KT_I18N::translate('Jane only ruled from July 6th to July 19th, 1553'); ?>")
			regnalyear = "error"
		}

		if (regnalyear < 1753 ){
			if (Month == 3){
				if (Day < 25){
					var NS = regnalyear
					NS = NS % 10
					regnalyear = regnalyear -1
					regnalyear = regnalyear + "/" + NS
				}
			}

			if (Month < 3){
				var NS = regnalyear % 10
				if (NS == 0) { NS = regnalyear % 100
				}
					if (NS == 0) { NS = regnalyear
				}
				regnalyear = regnalyear -1
				regnalyear = regnalyear + "/" + NS
			}
		}
		document.RegnalYear.Answer.value = regnalyear
	}
// --></script>
