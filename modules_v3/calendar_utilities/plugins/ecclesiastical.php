<?php
// Plugin for calendar_utilities module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team. All rights reserved.
//
// Modifications Copyright (c) 2010 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Ecclesiastical Dates"; /* I18N: Name of a plugin. */ WT_I18N::translate('Ecclesiastical Dates');

// DATA
/* List of non-movable feasts */
$non_movable = array (
	'Lent'																=>'40 week days between Ash Wednesday and Easter',
	'Advent'															=>'Period from Advent Sunday to December 25',
	'Sexagesima Sunday'													=>'Sunday after Septuagesima Sunday.',
	'Quinquagesima Sunday'												=>'2nd Sunday after Septuagesima Sunday',
	'Shrove Tuesday'													=>'day before Ash Wednesday.',
	'Passion Sunday'													=>'2nd Sunday before Easter',
	'Palm Sunday'														=>'Sunday before Easter',
	'Quasimodo (Low) Sunday'											=>'Sunday after Easter',
	'Good Friday'														=>'Friday before Easter',
	'Maundy Thursday'													=>'Thursday before Easter',
	'Adorate dominum'													=>'3rd Sunday after January 6',
	'Adrian (Canterbury)'												=>'January 9',
	'Ad te levavi'														=>'Advent Sunday',
	'Agatha'															=>'February 5',
	'Agnes'																=>'January 21',
	'Alban'																=>'June 22 (or June in 1662 Prayer Book)',
	'Aldhelm'															=>'May 25',
	'All Hallows'														=>'November 1',
	'All Saints'														=>'November 1',
	'All Souls'															=>'November 2',
	'Alphege'															=>'April 19',
	'Ambrose'															=>'April 4',
	'Andrew'															=>'November 30',
	'Anne'																=>'July 26',
	'Annunciation'														=>'March 25',
	'Ante Portram Latinam'												=>'May 6',
	'Aspiciens a longe'													=>'Advent Sunday',
	'Audoenus (Ouen)'													=>'August 24 or 25',
	'Audrey (Ethelreda)'												=>'October 17',
	'Augustine (Canterbury)'											=>'May 26',
	'Augustine (Hippo)'													=>'August 28',
	'Barnabas'															=>'June 11',
	'Bartholomew'														=>'August 24',
	'Bede, Venerable'													=>'May 27',
	'Benedict'															=>'March 21',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Translation of Benedict'				=>'July 11',
	'Birinus'															=>'December 3',
	'Blasius'															=>'February 3',
	'Boniface'															=>'June 5',
	'Botolph'															=>'June 17',
	'Bricius'															=>'November 13',
	'Candlemas'															=>'February 2',
	'Canite Tuba'														=>'4th Sunday in Advent',
	'Cantate domino'													=>'4th Sunday after Easter',
	'Cathedra Petri'													=>'February 22',
	'Catherine'															=>'November 25',
	'Cecilia'															=>'November 22',
	'Cena domini'														=>'Thursday before Easter',
	'Chad (Cedde)'														=>'March 2',
	'Christmas (Natale Domini)'											=>'December 25',
	'Christopher'														=>'July 25',
	'Circumcision'														=>'January 1',
	'Clausum Pasche'													=>'1st Sunday after Easter',
	'Clement'															=>'November 23',
	'Cornelius+Cyprian'													=>'September 14',
	'Corpus Christi'													=>'Thursday after Trinity*',
	'Crispin and Crispinian'											=>'October 25',
	'Cuthbert'															=>'March 20',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;translation of Cuthbert'				=>'September 4',
	'Cyprian and Justina'												=>'September 26',
	'Daemon mutus'														=>'3rd Sunday in Lent (after Ash Wednesday)',
	'Da pacem'															=>'18th Sunday after Trinity*',
	'David'																=>'March 1',
	'Deus in adiutorium'												=>'12th Sunday after Trinity*',
	'Deus in loco sancto'												=>'11th Sunday after Trinity*',
	'Deus qui errantibus'												=>'3rd Sunday after Easter',
	'Dicit dominus'														=>'23rd and 24th Sunday after Trinity*',
	'Dies cinerum'														=>'Ash Wednesday',
	'Dies crucis adorande'												=>'Good Friday',
	'Dies Mandati'														=>'Maundy Thursday',
	'Dionysius, Rusticus, and Eleutherius'								=>'October 9',
	'Domine, in tua misericordia'										=>'1st Sunday after Trinity*',
	'Domine, ne longe'													=>'Palm Sunday',
	'Dominus fortitudo'													=>'6th Sunday after Trinity*',
	'Dominus illuminatio mea'											=>'4th Sunday after Trinity*',
	'Dum, clamarem'														=>'10th Sunday after Trinity*',
	'Epiphany'															=>'January 6',
	'Dum medium silentium'												=>' Sunday in octave of Christmas or Sunday after January 1 when this falls on eve of Epiphany (Jan. 6)',
	'Dunstan'															=>'May 19',
	'Eadburga (Winchester)'												=>'June 15',
	'Ecce deus adiuvat'													=>'9th Sunday after Trinity*',
	'Editha'															=>'September 16',
	'Edmund (archbishop)'												=>'November 16',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'June 9',
	'Edmund (king)'														=>'November 20',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'April 29',
	'Edward the Confessor'												=>'January 5',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'October 13 often called the feast of St. Edward in the quidene of Michaelmas',
	'Edward (king of Saxons)'											=>'March 18',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation I'					=>'February 18',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation II'					=>'June 20',
	'Egidius (Giles)'													=>'September 1',
	'Enurchus (Evurcius)'												=>'September 7',
	'Esto mihi'															=>'Sunday before Ash Wednesday (Quinquagesima)',
	'Ethelbert (king)'													=>'May 20',
	'Ethelreda'															=>'October 17',
	'Euphemia'															=>'September 16',
	'Eustachius'														=>'November 2',
	'Exaltation of the Cross'											=>'September 14',
	'Exaudi domine'														=>'Sunday in octave of Ascension or 5th Sunday after octave of Pentecost (Trinity)*',
	'Exsurge domine'													=>'2nd Sunday before Ash Wednesday (Sexagesima)',
	'Fabian and Sebastian'												=>'January 20',
	'Factus est dominus'												=>'2nd Sunday after Trinity*',
	'Faith'																=>'October 6',
	'Felicitas'															=>'November 23',
	'Fransiscus'														=>'October 4',
	'Gaudete in domino'													=>'3rd Sunday in Advent',
	'George'															=>'April 23',
	'Gregory'															=>'March 12',
	'Grimbold'															=>'July 8',
	'Gule of August'													=>'August 1',
	'Guthlac'															=>'April 11',
	'Hieronymous (Jerome)'												=>'September 30',
	'Hilary'															=>'January 13',
	'Hugh (bishop of Lincoln)'											=>'November 17',
	'Inclina auram tuam'												=>'15th Sunday after Trinity*',
	'In excelso throno'													=>'1st Sunday after Epiphany',
	'In Monte tumba'													=>'October 16',
	'Innocents'															=>'December 28',
	'Invention of the Cross'											=>'May 3',
	'Invocavit me'														=>'1st Sunday in Lent',
	'In voluntate tua'													=>'21st Sunday afterTrinity*',
	'Isti sunt dies'													=>'Passion Sunday',
	'James'																=>'July 25',
	'Jerome (Hieronymus)'												=>'September 30',
	'John the Baptist'													=>'June 24',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his beheading'						=>'August 29',
	'John the Evangelist'												=>'December 27',
	'Jubilate omnis terra'												=>'3rd Sunday after Easter',
	'Judica me'															=>'Passion Sunday',
	'Judoc'																=>'December 13',
	'Justus es domine'													=>'17th Sunday after the octave of Pentecost (Trinity)*',
	'Lady day (annunciation)'											=>'March 25',
	'Laetare Jerusalem'													=>'4th Sunday in Lent',
	'Lambert'															=>'September 17',
	'Lammas'															=>'August 1',
	'Laudus'															=>'September 21',
	'Laurence'															=>'August 10',
	'Leonard'															=>'November 6',
	'Lucianus and Geminianus'											=>'September 16',
	'Lucian'															=>'January 8',
	'Lucy'																=>'December 13',
	'Luke'																=>'October 18',
	'Machutus'															=>'November 15',
	'Margaret (queen of Scotland)'										=>'July 8',
	'Margaret (virgin and martyr)'										=>'July 20',
	'Mark'																=>'April 25',
	'Martin'															=>'November 11',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'July 4',
	'Mary, Blessed Virgin'												=>'&nbsp;',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Annunciation (Lady day)'				=>'March 25',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Assumption'							=>'August 15',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Conception'							=>'December 8',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nativity'							=>'September 8',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Purification'						=>'February 2',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitation'							=>'July 2',
	'Mary Magdalene'													=>'July 22',
	'Mathias'															=>'February 24 (25 on leap years)',
	'Matthew'															=>'September 21',
	'Maurice'															=>'September 22',
	'Meliorus'															=>'October 1',
	'Memento mei'														=>'4th Sunday in Advent',
	'Michael'															=>'September 29',
	'Mildred'															=>'July 13',
	'Miserere mihi'														=>'16th Sunday after Trinity*',
	'Misericordia domini'												=>'2nd Sunday after Easter',
	'Name of Jesus'														=>'August 7',
	'Nicholas'															=>'December 6',
	'Nicomedes'															=>'June 1',
	'Oculi'																=>'3rd Sunday in Lent',
	'Omnes gentes'														=>'7th Sunday after Trinity*',
	'Omnia quae fecisti'												=>'20th Sunday after Trinity*',
	'Omnis terra'														=>'2nd Sunday after Epiphany',
	'Osanna'															=>'Palm Sunday',
	'O Sapientia'														=>'December 16',
	'Osmund'															=>'December 4',
	'Oswald (bishop)'													=>'February 28',
	'Oswald (king)'														=>'August 5',
	'Patrick'															=>'March 17',
	'Paul, Conversion of'												=>'January 25',
	'Perpetua'															=>'March 7',
	'Peter and Paul'													=>'June 29',
	'Peter and Vincula'													=>'August 1',
	'Philip and James'													=>'May 1',
	'Populus Sion'														=>'2nd Sunday in Advent',
	'Prisca'															=>'January 18',
	'Priscus'															=>'September 1',
	'Protector noster'													=>'14th Sunday after Trinity*',
	'Quasimodo'															=>'1st Sunday after Easter',
	'Reddite quae sunt'													=>'23rd Sunday after*',
	'Remigius, Germanus, and Vedastus Reminiscere'						=>'2nd Sunday in Lent',
	'Reminiscere'														=>'2nd Sunday in Lent',
	'Respice domine'													=>'13th Sunday after Pentecost',
	'Respice in me'														=>'3rd Sunday after Trinity*',
	'Richard'															=>'April 3',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'July 15',
	'Rorate celi'														=>'4th Sunday in Advent',
	'Salus populi'														=>'19th Sunday after Pentecost',
	'Scholastica'														=>'February 10',
	'Si iniquitates'													=>'22nd Sunday after Trinity*',
	'Silvester'															=>'December 31',
	'Simon and Jude'													=>'October 28',
	'Sitientes'															=>'Saturday before Passion Sunday',
	'Stephen'															=>'December 26',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his invention'						=>'August 3',
	'Suscepius deus'													=>'8th Sunday after Trinity*',
	'Swithun'															=>'July 2',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'July 3',
	'Thomas the Apostle'												=>'December 21',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'July 3',
	'Thomas Becket'														=>'December 29',
	'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation'						=>'July 7',
	'Timotheus and Symphorianus'										=>'August 22',
	'Transfiguration'													=>'August 6',
	'Urban'																=>'May 25',
	'Valentine'															=>'February 14',
	'Vincent'															=>'January 22',
	'Viri Galilei'														=>'Ascension Day',
	'Vocem jucunditatis'												=>'5th Sunday after Easter',
	'Wilfrid'															=>'January 19'
);

// HELP //

$help1 = htmlspecialchars(addslashes(WT_I18N::translate('<div id="popup"><p>There are several different areas. They operate as follows.</p><p><b>1. Calculation of major moveable feasts.</b></p><p>Since the ecclesiastical calendar is based on lunar rather than solar cycles, certain key holidays (feasts) occur on different days each year. The method of calculating these feasts has also changed since the council of Nicea (325 A.D.). The button labeled "Calculate Holidays" calculates the dates of seven major feasts for the year entered in the field labeled "Year", and displays the results below this field. The only restrictions are that the number entered in the field "Year" must be an integer (no fractions) greater than zero. However, the holidays generated are only valid for dates since 325 A.D. (Early Christian and Roman dating is another story). Also, for purposes of calculation, I have assumed that the ecclesiastical year begins on January first, even though this standard was only gradually accepted. If you are working with early monastic documents you might want to consider that dates from December 25th through March may be "off" by one year. To a Benedictine, for instance (to whom the year began on December 25th), the feast of the Innocents in 1450 would be December 28th, 1450, while to others it might be December 28th, 1449. In fact, before 1582, most calendars did not have the year begin on January 1st, even though the calculation of the moveable feasts acted as if it did. In England, the year "began" either on December 25th, or, more frequently on March 25th (Lady Day), until 1752. These vagaries are not something I wanted to include in the calculations, since they often varied quite a bit. The calculations for the holidays will take into account the days dropped from the calendar when the "New style" was adopted, since these affect the month and day of Easter. Conventions about the beginning of the year are easily corrected for.</p><p><b>2. Old and New style dating and Day of the week</b></p><p>When the Pope Gregory revised the calendar in 1582, a certain number of days were omitted from the calendar at a particular time, resulting in two separate styles of dating. England persisted in using the "Old Style" until 1752, because of religious differences. The Old Style also often dated the beginning of the year from March 25 rather than January 1, and this area of the site takes this difference into account. Thus March 8, 1735, Old Style is really March 19, 1736 in the New Style! Occasionally, particularly in dating material between 1582 and 1755 or so, it becomes necessary to convert back and forth.</p><p>Here is a more detailed description of the transition year (in England): 1752.</p><p>The area called "Convert Old Style to New Style" converts the old style date to a new style date and returns an answer. The area called "Convert New Style to Old Style" converts the new style date to an old style date and enters it in the "Old style" fields. You must enter an integer greater than zero for the year.</p><p>You can also calculate the day of the week for both old and new style dates.</p><p>Remember that these forms assume that the year begins on March 25th. If you want to plug in dates derived from the "Ecclesiastical Holidays" area, be aware that you will have to subtract 1 from the year for all dates between January 1st and March 24th.</p><p><b>NOTE FOR STUDENTS OF CONTINENTAL HISTORY</b></p><p>Don\'t try to use this site to calculate dates of documents between 1588 and 1752 because England is a special case during these years.</p></p>')), ENT_QUOTES);

$help2 = 'http://en.wikisource.org/wiki/1911_Encyclop%C3%A6dia_Britannica/Calendar/Ecclesiastical_Calendar_-_Easter';

// DISPLAY

$html.= '
	<style>
		#ecclesiastic h1 {font-size:15px; font-weight:bold;}
		#ecclesiastic {color:#555; font-size:13px;}
		#ecclesiastic form{overflow:hidden;}		
		#acknowledgement{background:#fff; border:1px solid #c0c0c0; border-radius:8px; float:right; font-size:12px; width:250px; margin:0 30px; padding:10px;}
		#acknowledgement p{padding:5px;}
		#acknowledgement i{cursor:pointer; color:blue; margin:0; vertical-align:baseline;}
		#ecclesiastic .label_ec{clear:left; float:left; font-size:14px; padding:5px; width:320px;}
		#ecclesiastic .year{width:234px;}
		#ecclesiastic .label_ec input{color:#555; float:right; font-size:13px;}
		#ecclesiastic .note{font-size:11px; font-style:italic; padding:5px; white-space:nowrap;}
		#ecclesiastic .non_movable{clear:both;margin-top:40px;}
		#ecclesiastic .non_movable_list{border:1px solid black; max-width:95%; height:200px; overflow:auto; padding:10px;}
		#popup{font-size:12px;}
		#popup p{padding:5px;}
	</style>
	<div id="ecclesiastic">
		<div id="acknowledgement">
			<p>This page is based on work done by Ian MacInnes <span class="note">(imacinnes@albion.edu)</span> at his website <a href="http://people.albion.edu/imacinnes/calendar/Welcome.html" target="blank"><b>Ian\'s English Calendar</b></a></p>
			<p>
				His site is intended to replace quick reference handbooks of dates for those interested in English history, literature, and genealogy. It is also accurate for European history outside of England, with the exception of the period 1582-1752. Students of Continental documents will need to follow 
				<i title="Help with English calendar" onclick="modalNotes(\''.$help1.'\', \''.WT_I18N::translate('Help with English calendar').'\')">this link </i>
				if they wish to date documents from this period.
			<p>
				<a title="Easter Formulae" href="'.$help2.'" target="_blank"><i>The formulae for calculating Easter are derived from the 11th edition Encyclopedia Brittanica.</i></a>
			</p>
		</div>
		<h1>Enter the year in question:</h1>
		<form name="EasterCalculator">
			<label class="label_ec year">Christian Year*
				<input type="text" name="input" value="1492" size="7">
				<p class="note">* The Ecclesiastical year begins on January 1.</p>
			</label>
			<input class="button_ec" type="button" name="CalculateHolidays" value="Calculate Holidays" onclick="CalculateEaster()">
			<label class="label_ec">Easter:
				<input type="text" size="23" name="Easter" maxlength="150">
			</label>
			<label class="label_ec">Septuagesima:
				<input type="text" size="23" name="Septuagesima" maxlength="150">
			</label>
			<label class="label_ec">Ash Wednesday:
				<input type="text" size="23" name="Ash" maxlength="150">
			</label>
			<label class="label_ec">Ascension
				<input type="text" size="23" name="Ascension" maxlength="150">
			</label>
			<label class="label_ec">Pentecost
				<input type="text" size="23" name="Pentecost" maxlength="150">
			</label>
			<label class="label_ec">Trinity Sunday
				<input type="text" size="23" name="Trinity" maxlength="150">
			</label>
			<label class="label_ec">Advent Sunday
				<input type="text" size="23" name="Advent" maxlength="150">
			</label>
		</form>
		<div class="non_movable">
			<h1>Full list of moveable and fixed holidays</h1>
			<div class="non_movable_list">
				<p class="note">The "octave" of any holiday = eight days after the holiday- counting the holiday itself. Thus the octave of a Sunday is the following Sunday.</p>';
				foreach ($non_movable as $e_event => $e_date) {
					$html.= '
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:30%;clear:left;;">'. $e_event. '</div>
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:70%;">'. $e_date. '</div>';
				}

			$html.= '
				</div>
				<p class="note">* After 1570, subtract one week.</p>
			</div>
		</div>
';

// SCRIPTS //
?>

<script language="JavaScript"><!--
	function upperMe() {
		document.converter.Easter.value  = document.converter.input.value.toUpperCase()
	}
	function CalculateEaster() {
		var Year
		var L
		var P
		var l
		var p
		var E
		var Marchdate
		var H
		var Leapyear 
		var Leapyearcentury
		var Leapyearfourcentury
		var S
		var Advent
		var Pent 
		var T
		var A
		var Temp
		Year=parseInt(document.EasterCalculator.input.value)
		Leapyear = Year/4
		Leapyear = Leapyear - parseInt(Year/4)
		Leapyearcentury = Year/100
		Leapyearcentury = Leapyearcentury - parseInt(Year/100)
		Leapyearfourcentury = Year/400
		Leapyearfourcentury = Leapyearfourcentury - parseInt(Year/400)
		E=Year + 1
		E= E % 19
		if (E<1) {E=19}
		E = 11*E
		if (Year<1753) {E = E-3} else {E=E-10}
		E = E % 30
		if (Year>1752) {
			E = E - parseInt(Year/100) +16 + parseInt(Year/400) - 4
			var C
			C = parseInt(Year/100) - 15
			C = parseInt(C/3)
			E = E + C
		}
		if (E<24) {
			P=24-E
			l = 27 -E
			l = l % 7
		} else {
			P=54-E
			l = 57 - E
			l = l % 7
		}
		if (Year<1753) {
			L= 3 - Year - parseInt(Year/4)
			L= L % 7
			L = L+7
		} else {
			L = 6 - Year - parseInt(Year/4)
			L = L + parseInt(Year/100) -16 -parseInt(Year/400) +4
			L = L % 7
			L = L+7
		}
		if (L-l<0) {L=L+7}
		p = P + L - l
		p = p + 21
		Marchdate = p
		if (p>31) {
			p = p - 31
			p = "April " + p 
			//   + " E=" + E + " P=" +P + " L=" + L + " l=" + l + " C=" + C
		} else {
			p = "March " + p 
			// + " E=" + E + " P=" +P + " L=" + L + " l=" + l
		}
		H = Marchdate - 46
		if (H>0) {H = "March " + H}
		else {
			if (Leapyear<0.1) {
				if (Year>1752) {
					if (Leapyearcentury<0.01) {
						if (Leapyearfourcentury<0.01) {H = H + 29}
						else {
							H =H + 28}
					}
					else {
						H = H + 29
					}
				}
				else {H = H + 29}
			}
			else {H = H + 28}
			H = "February " + H
		}
		S = Marchdate - 63
		if (Leapyear<0.1) {
			if (Year>1752) {
				if (Leapyearcentury<0.01) {
					if (Leapyearfourcentury<0.01) {S = S + 29}
				else {
						S = S + 28}
				} else {
					S = S + 29}
			} else {
				S = S + 29}
		} else {
			S = S + 28}
			if (S > 0) {S = "February " + S}
		else {
			S = S + 31
			S = "January " + S}
			A = Marchdate + 39
		if (A<62) {	A=A-31
			A = "April " + A
		} else {
			A=A-61
			if (A>31) 
			{A=A-31
			A = "June " + A}
			else 
			{A="May " + A}
		}
		Pent = Marchdate + 49
		if (Pent<92.5) {
			Pent = Pent - 61
			Pent = "May " + Pent
		} else {	Pent = Pent - 92
			Pent = "June " + Pent
		}
		T = Marchdate + 56
		if (T<93) {
			T = T-61
			T = "May " + T
		} else {	T = T - 92
			T = "June " + T
		}

		Advent = Marchdate + 1
		Advent = Advent % 7
		Advent = Advent + 27
		if (Advent<31) {
			Advent = "November " + Advent
		} else {		Advent = Advent - 30
			Advent = "December " + Advent
		}
		document.EasterCalculator.Easter.value = p
		document.EasterCalculator.Septuagesima.value = S
		document.EasterCalculator.Ash.value = H
		document.EasterCalculator.Ascension.value = A
		document.EasterCalculator.Pentecost.value = Pent
		document.EasterCalculator.Trinity.value = T
		document.EasterCalculator.Advent.value = Advent
	}
// --></script>
