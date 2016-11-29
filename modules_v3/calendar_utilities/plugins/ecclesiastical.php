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
// Copyright (C) 2002 to 2010 PGV Development Team
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
	WT_I18N::translate('Lent')													=> WT_I18N::translate('40 week days between Ash Wednesday and Easter'),
	WT_I18N::translate('Advent')												=> WT_I18N::translate('Period from Advent Sunday to December 25'),
	WT_I18N::translate('Sexagesima Sunday')										=> WT_I18N::translate('Sunday after Septuagesima Sunday.'),
	WT_I18N::translate('Quinquagesima Sunday')									=> WT_I18N::translate('2nd Sunday after Septuagesima Sunday'),
	WT_I18N::translate('Shrove Tuesday')										=> WT_I18N::translate('day before Ash Wednesday.'),
	WT_I18N::translate('Passion Sunday')										=> WT_I18N::translate('2nd Sunday before Easter'),
	WT_I18N::translate('Palm Sunday')											=> WT_I18N::translate('Sunday before Easter'),
	WT_I18N::translate('Quasimodo (Low) Sunday')								=> WT_I18N::translate('Sunday after Easter'),
	WT_I18N::translate('Good Friday')											=> WT_I18N::translate('Friday before Easter'),
	WT_I18N::translate('Maundy Thursday')										=> WT_I18N::translate('Thursday before Easter'),
	WT_I18N::translate('Adorate dominum')										=> WT_I18N::translate('3rd Sunday after January 6'),
	WT_I18N::translate('Adrian (Canterbury)')									=> WT_I18N::translate('January 9'),
	WT_I18N::translate('Ad te levavi')											=> WT_I18N::translate('Advent Sunday'),
	WT_I18N::translate('Agatha')												=> WT_I18N::translate('February 5'),
	WT_I18N::translate('Agnes')													=> WT_I18N::translate('January 21'),
	WT_I18N::translate('Alban')													=> WT_I18N::translate('June 22 (or June in 1662 Prayer Book)'),
	WT_I18N::translate('Aldhelm')												=> WT_I18N::translate('May 25'),
	WT_I18N::translate('All Hallows')											=> WT_I18N::translate('November 1'),
	WT_I18N::translate('All Saints')											=> WT_I18N::translate('November 1'),
	WT_I18N::translate('All Souls')												=> WT_I18N::translate('November 2'),
	WT_I18N::translate('Alphege')												=> WT_I18N::translate('April 19'),
	WT_I18N::translate('Ambrose')												=> WT_I18N::translate('April 4'),
	WT_I18N::translate('Andrew')												=> WT_I18N::translate('November 30'),
	WT_I18N::translate('Anne')													=> WT_I18N::translate('July 26'),
	WT_I18N::translate('Annunciation')											=> WT_I18N::translate('March 25'),
	WT_I18N::translate('Ante Portram Latinam')									=> WT_I18N::translate('May 6'),
	WT_I18N::translate('Aspiciens a longe')										=> WT_I18N::translate('Advent Sunday'),
	WT_I18N::translate('Audoenus (Ouen)')										=> WT_I18N::translate('August 24 or 25'),
	WT_I18N::translate('Audrey (Ethelreda)')									=> WT_I18N::translate('October 17'),
	WT_I18N::translate('Augustine (Canterbury)')								=> WT_I18N::translate('May 26'),
	WT_I18N::translate('Augustine (Hippo)')										=> WT_I18N::translate('August 28'),
	WT_I18N::translate('Barnabas')												=> WT_I18N::translate('June 11'),
	WT_I18N::translate('Bartholomew')											=> WT_I18N::translate('August 24'),
	WT_I18N::translate('Bede, Venerable')										=> WT_I18N::translate('May 27'),
	WT_I18N::translate('Benedict')												=> WT_I18N::translate('March 21'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Translation of Benedict')	=> WT_I18N::translate('July 11'),
	WT_I18N::translate('Birinus')												=> WT_I18N::translate('December 3'),
	WT_I18N::translate('Blasius')												=> WT_I18N::translate('February 3'),
	WT_I18N::translate('Boniface')												=> WT_I18N::translate('June 5'),
	WT_I18N::translate('Botolph')												=> WT_I18N::translate('June 17'),
	WT_I18N::translate('Bricius')												=> WT_I18N::translate('November 13'),
	WT_I18N::translate('Candlemas')												=> WT_I18N::translate('February 2'),
	WT_I18N::translate('Canite Tuba')											=> WT_I18N::translate('4th Sunday in Advent'),
	WT_I18N::translate('Cantate domino')										=> WT_I18N::translate('4th Sunday after Easter'),
	WT_I18N::translate('Cathedra Petri')										=> WT_I18N::translate('February 22'),
	WT_I18N::translate('Catherine')												=> WT_I18N::translate('November 25'),
	WT_I18N::translate('Cecilia')												=> WT_I18N::translate('November 22'),
	WT_I18N::translate('Cena domini')											=> WT_I18N::translate('Thursday before Easter'),
	WT_I18N::translate('Chad (Cedde)')											=> WT_I18N::translate('March 2'),
	WT_I18N::translate('Christmas (Natale Domini)')								=> WT_I18N::translate('December 25'),
	WT_I18N::translate('Christopher')											=> WT_I18N::translate('July 25'),
	WT_I18N::translate('Circumcision')											=> WT_I18N::translate('January 1'),
	WT_I18N::translate('Clausum Pasche')										=> WT_I18N::translate('1st Sunday after Easter'),
	WT_I18N::translate('Clement')												=> WT_I18N::translate('November 23'),
	WT_I18N::translate('Cornelius+Cyprian')										=> WT_I18N::translate('September 14'),
	WT_I18N::translate('Corpus Christi')										=> WT_I18N::translate('Thursday after Trinity*'),
	WT_I18N::translate('Crispin and Crispinian')								=> WT_I18N::translate('October 25'),
	WT_I18N::translate('Cuthbert')												=> WT_I18N::translate('March 20'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;translation of Cuthbert')	=> WT_I18N::translate('September 4'),
	WT_I18N::translate('Cyprian and Justina')									=> WT_I18N::translate('September 26'),
	WT_I18N::translate('Daemon mutus')											=> WT_I18N::translate('3rd Sunday in Lent (after Ash Wednesday)'),
	WT_I18N::translate('Da pacem')												=> WT_I18N::translate('18th Sunday after Trinity*'),
	WT_I18N::translate('David')													=> WT_I18N::translate('March 1'),
	WT_I18N::translate('Deus in adiutorium')									=> WT_I18N::translate('12th Sunday after Trinity*'),
	WT_I18N::translate('Deus in loco sancto')									=> WT_I18N::translate('11th Sunday after Trinity*'),
	WT_I18N::translate('Deus qui errantibus')									=> WT_I18N::translate('3rd Sunday after Easter'),
	WT_I18N::translate('Dicit dominus')											=> WT_I18N::translate('23rd and 24th Sunday after Trinity*'),
	WT_I18N::translate('Dies cinerum')											=> WT_I18N::translate('Ash Wednesday'),
	WT_I18N::translate('Dies crucis adorande')									=> WT_I18N::translate('Good Friday'),
	WT_I18N::translate('Dies Mandati')											=> WT_I18N::translate('Maundy Thursday'),
	WT_I18N::translate('Dionysius, Rusticus, and Eleutherius')					=> WT_I18N::translate('October 9'),
	WT_I18N::translate('Domine, in tua misericordia')							=> WT_I18N::translate('1st Sunday after Trinity*'),
	WT_I18N::translate('Domine, ne longe')										=> WT_I18N::translate('Palm Sunday'),
	WT_I18N::translate('Dominus fortitudo')										=> WT_I18N::translate('6th Sunday after Trinity*'),
	WT_I18N::translate('Dominus illuminatio mea')								=> WT_I18N::translate('4th Sunday after Trinity*'),
	WT_I18N::translate('Dum, clamarem')											=> WT_I18N::translate('10th Sunday after Trinity*'),
	WT_I18N::translate('Epiphany')												=> WT_I18N::translate('January 6'),
	WT_I18N::translate('Dum medium silentium')									=> WT_I18N::translate(' Sunday in octave of Christmas or Sunday after January 1 when this falls on eve of Epiphany (Jan. 6)'),
	WT_I18N::translate('Dunstan')												=> WT_I18N::translate('May 19'),
	WT_I18N::translate('Eadburga (Winchester)')									=> WT_I18N::translate('June 15'),
	WT_I18N::translate('Ecce deus adiuvat')										=> WT_I18N::translate('9th Sunday after Trinity*'),
	WT_I18N::translate('Editha')												=> WT_I18N::translate('September 16'),
	WT_I18N::translate('Edmund (archbishop)')									=> WT_I18N::translate('November 16'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('June 9'),
	WT_I18N::translate('Edmund (king)')											=> WT_I18N::translate('November 20'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('April 29'),
	WT_I18N::translate('Edward the Confessor')									=> WT_I18N::translate('January 5'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('October 13 often called the feast of St. Edward in the quidene of Michaelmas'),
	WT_I18N::translate('Edward (king of Saxons)')								=> WT_I18N::translate('March 18'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation I')		=> WT_I18N::translate('February 18'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation II')		=> WT_I18N::translate('June 20'),
	WT_I18N::translate('Egidius (Giles)')										=> WT_I18N::translate('September 1'),
	WT_I18N::translate('Enurchus (Evurcius)')									=> WT_I18N::translate('September 7'),
	WT_I18N::translate('Esto mihi')												=> WT_I18N::translate('Sunday before Ash Wednesday (Quinquagesima)'),
	WT_I18N::translate('Ethelbert (king)')										=> WT_I18N::translate('May 20'),
	WT_I18N::translate('Ethelreda')												=> WT_I18N::translate('October 17'),
	WT_I18N::translate('Euphemia')												=> WT_I18N::translate('September 16'),
	WT_I18N::translate('Eustachius')											=> WT_I18N::translate('November 2'),
	WT_I18N::translate('Exaltation of the Cross')								=> WT_I18N::translate('September 14'),
	WT_I18N::translate('Exaudi domine')											=> WT_I18N::translate('Sunday in octave of Ascension or 5th Sunday after octave of Pentecost (Trinity)*'),
	WT_I18N::translate('Exsurge domine')										=> WT_I18N::translate('2nd Sunday before Ash Wednesday (Sexagesima)'),
	WT_I18N::translate('Fabian and Sebastian')									=> WT_I18N::translate('January 20'),
	WT_I18N::translate('Factus est dominus')									=> WT_I18N::translate('2nd Sunday after Trinity*'),
	WT_I18N::translate('Faith')													=> WT_I18N::translate('October 6'),
	WT_I18N::translate('Felicitas')												=> WT_I18N::translate('November 23'),
	WT_I18N::translate('Fransiscus')											=> WT_I18N::translate('October 4'),
	WT_I18N::translate('Gaudete in domino')										=> WT_I18N::translate('3rd Sunday in Advent'),
	WT_I18N::translate('George')												=> WT_I18N::translate('April 23'),
	WT_I18N::translate('Gregory')												=> WT_I18N::translate('March 12'),
	WT_I18N::translate('Grimbold')												=> WT_I18N::translate('July 8'),
	WT_I18N::translate('Gule of August')										=> WT_I18N::translate('August 1'),
	WT_I18N::translate('Guthlac')												=> WT_I18N::translate('April 11'),
	WT_I18N::translate('Hieronymous (Jerome)')									=> WT_I18N::translate('September 30'),
	WT_I18N::translate('Hilary')												=> WT_I18N::translate('January 13'),
	WT_I18N::translate('Hugh (bishop of Lincoln)')								=> WT_I18N::translate('November 17'),
	WT_I18N::translate('Inclina auram tuam')									=> WT_I18N::translate('15th Sunday after Trinity*'),
	WT_I18N::translate('In excelso throno')										=> WT_I18N::translate('1st Sunday after Epiphany'),
	WT_I18N::translate('In Monte tumba')										=> WT_I18N::translate('October 16'),
	WT_I18N::translate('Innocents')												=> WT_I18N::translate('December 28'),
	WT_I18N::translate('Invention of the Cross')								=> WT_I18N::translate('May 3'),
	WT_I18N::translate('Invocavit me')											=> WT_I18N::translate('1st Sunday in Lent'),
	WT_I18N::translate('In voluntate tua')										=> WT_I18N::translate('21st Sunday afterTrinity*'),
	WT_I18N::translate('Isti sunt dies')										=> WT_I18N::translate('Passion Sunday'),
	WT_I18N::translate('James')													=> WT_I18N::translate('July 25'),
	WT_I18N::translate('Jerome (Hieronymus)')									=> WT_I18N::translate('September 30'),
	WT_I18N::translate('John the Baptist')										=> WT_I18N::translate('June 24'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his beheading')			=> WT_I18N::translate('August 29'),
	WT_I18N::translate('John the Evangelist')									=> WT_I18N::translate('December 27'),
	WT_I18N::translate('Jubilate omnis terra')									=> WT_I18N::translate('3rd Sunday after Easter'),
	WT_I18N::translate('Judica me')												=> WT_I18N::translate('Passion Sunday'),
	WT_I18N::translate('Judoc')													=> WT_I18N::translate('December 13'),
	WT_I18N::translate('Justus es domine')										=> WT_I18N::translate('17th Sunday after the octave of Pentecost (Trinity)*'),
	WT_I18N::translate('Lady day (annunciation)')								=> WT_I18N::translate('March 25'),
	WT_I18N::translate('Laetare Jerusalem')										=> WT_I18N::translate('4th Sunday in Lent'),
	WT_I18N::translate('Lambert')												=> WT_I18N::translate('September 17'),
	WT_I18N::translate('Lammas')												=> WT_I18N::translate('August 1'),
	WT_I18N::translate('Laudus')												=> WT_I18N::translate('September 21'),
	WT_I18N::translate('Laurence')												=> WT_I18N::translate('August 10'),
	WT_I18N::translate('Leonard')												=> WT_I18N::translate('November 6'),
	WT_I18N::translate('Lucianus and Geminianus')								=> WT_I18N::translate('September 16'),
	WT_I18N::translate('Lucian')												=> WT_I18N::translate('January 8'),
	WT_I18N::translate('Lucy')													=> WT_I18N::translate('December 13'),
	WT_I18N::translate('Luke')													=> WT_I18N::translate('October 18'),
	WT_I18N::translate('Machutus')												=> WT_I18N::translate('November 15'),
	WT_I18N::translate('Margaret (queen of Scotland)')							=> WT_I18N::translate('July 8'),
	WT_I18N::translate('Margaret (virgin and martyr)')							=> WT_I18N::translate('July 20'),
	WT_I18N::translate('Mark')													=> WT_I18N::translate('April 25'),
	WT_I18N::translate('Martin')												=> WT_I18N::translate('November 11'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('July 4'),
	WT_I18N::translate('Mary, Blessed Virgin')									=> WT_I18N::translate('&nbsp;'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Annunciation (Lady day)')	=> WT_I18N::translate('March 25'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Assumption')				=> WT_I18N::translate('August 15'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Conception')				=> WT_I18N::translate('December 8'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nativity')				=> WT_I18N::translate('September 8'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Purification')			=> WT_I18N::translate('February 2'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitation')				=> WT_I18N::translate('July 2'),
	WT_I18N::translate('Mary Magdalene')										=> WT_I18N::translate('July 22'),
	WT_I18N::translate('Mathias')												=> WT_I18N::translate('February 24 (25 on leap years)'),
	WT_I18N::translate('Matthew')												=> WT_I18N::translate('September 21'),
	WT_I18N::translate('Maurice')												=> WT_I18N::translate('September 22'),
	WT_I18N::translate('Meliorus')												=> WT_I18N::translate('October 1'),
	WT_I18N::translate('Memento mei')											=> WT_I18N::translate('4th Sunday in Advent'),
	WT_I18N::translate('Michael')												=> WT_I18N::translate('September 29'),
	WT_I18N::translate('Mildred')												=> WT_I18N::translate('July 13'),
	WT_I18N::translate('Miserere mihi')											=> WT_I18N::translate('16th Sunday after Trinity*'),
	WT_I18N::translate('Misericordia domini')									=> WT_I18N::translate('2nd Sunday after Easter'),
	WT_I18N::translate('Name of Jesus')											=> WT_I18N::translate('August 7'),
	WT_I18N::translate('Nicholas')												=> WT_I18N::translate('December 6'),
	WT_I18N::translate('Nicomedes')												=> WT_I18N::translate('June 1'),
	WT_I18N::translate('Oculi')													=> WT_I18N::translate('3rd Sunday in Lent'),
	WT_I18N::translate('Omnes gentes')											=> WT_I18N::translate('7th Sunday after Trinity*'),
	WT_I18N::translate('Omnia quae fecisti')									=> WT_I18N::translate('20th Sunday after Trinity*'),
	WT_I18N::translate('Omnis terra')											=> WT_I18N::translate('2nd Sunday after Epiphany'),
	WT_I18N::translate('Osanna')												=> WT_I18N::translate('Palm Sunday'),
	WT_I18N::translate('O Sapientia')											=> WT_I18N::translate('December 16'),
	WT_I18N::translate('Osmund')												=> WT_I18N::translate('December 4'),
	WT_I18N::translate('Oswald (bishop)')										=> WT_I18N::translate('February 28'),
	WT_I18N::translate('Oswald (king)')											=> WT_I18N::translate('August 5'),
	WT_I18N::translate('Patrick')												=> WT_I18N::translate('March 17'),
	WT_I18N::translate('Paul, Conversion of')									=> WT_I18N::translate('January 25'),
	WT_I18N::translate('Perpetua')												=> WT_I18N::translate('March 7'),
	WT_I18N::translate('Peter and Paul')										=> WT_I18N::translate('June 29'),
	WT_I18N::translate('Peter and Vincula')										=> WT_I18N::translate('August 1'),
	WT_I18N::translate('Philip and James')										=> WT_I18N::translate('May 1'),
	WT_I18N::translate('Populus Sion')											=> WT_I18N::translate('2nd Sunday in Advent'),
	WT_I18N::translate('Prisca')												=> WT_I18N::translate('January 18'),
	WT_I18N::translate('Priscus')												=> WT_I18N::translate('September 1'),
	WT_I18N::translate('Protector noster')										=> WT_I18N::translate('14th Sunday after Trinity*'),
	WT_I18N::translate('Quasimodo')												=> WT_I18N::translate('1st Sunday after Easter'),
	WT_I18N::translate('Reddite quae sunt')										=> WT_I18N::translate('23rd Sunday after*'),
	WT_I18N::translate('Remigius, Germanus, and Vedastus Reminiscere')			=> WT_I18N::translate('2nd Sunday in Lent'),
	WT_I18N::translate('Reminiscere')											=> WT_I18N::translate('2nd Sunday in Lent'),
	WT_I18N::translate('Respice domine')										=> WT_I18N::translate('13th Sunday after Pentecost'),
	WT_I18N::translate('Respice in me')											=> WT_I18N::translate('3rd Sunday after Trinity*'),
	WT_I18N::translate('Richard')												=> WT_I18N::translate('April 3'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('July 15'),
	WT_I18N::translate('Rorate celi')											=> WT_I18N::translate('4th Sunday in Advent'),
	WT_I18N::translate('Salus populi')											=> WT_I18N::translate('19th Sunday after Pentecost'),
	WT_I18N::translate('Scholastica')											=> WT_I18N::translate('February 10'),
	WT_I18N::translate('Si iniquitates')										=> WT_I18N::translate('22nd Sunday after Trinity*'),
	WT_I18N::translate('Silvester')												=> WT_I18N::translate('December 31'),
	WT_I18N::translate('Simon and Jude')										=> WT_I18N::translate('October 28'),
	WT_I18N::translate('Sitientes')												=> WT_I18N::translate('Saturday before Passion Sunday'),
	WT_I18N::translate('Stephen')												=> WT_I18N::translate('December 26'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his invention')			=> WT_I18N::translate('August 3'),
	WT_I18N::translate('Suscepius deus')										=> WT_I18N::translate('8th Sunday after Trinity*'),
	WT_I18N::translate('Swithun')												=> WT_I18N::translate('July 2'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('July 3'),
	WT_I18N::translate('Thomas the Apostle')									=> WT_I18N::translate('December 21'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('July 3'),
	WT_I18N::translate('Thomas Becket')											=> WT_I18N::translate('December 29'),
	WT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> WT_I18N::translate('July 7'),
	WT_I18N::translate('Timotheus and Symphorianus')							=> WT_I18N::translate('August 22'),
	WT_I18N::translate('Transfiguration')										=> WT_I18N::translate('August 6'),
	WT_I18N::translate('Urban')													=> WT_I18N::translate('May 25'),
	WT_I18N::translate('Valentine')												=> WT_I18N::translate('February 14'),
	WT_I18N::translate('Vincent')												=> WT_I18N::translate('January 22'),
	WT_I18N::translate('Viri Galilei')											=> WT_I18N::translate('Ascension Day'),
	WT_I18N::translate('Vocem jucunditatis')									=> WT_I18N::translate('5th Sunday after Easter'),
	WT_I18N::translate('Wilfrid')												=> WT_I18N::translate('January 19'),
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
		#ecclesiastic .label_ec{clear:left; float:left; font-size:14px; padding:5px; width:480px;}
		#ecclesiastic .year{width:234px;}
		#ecclesiastic .label_ec input{color:#555; float:right; font-size:13px;}
		#ecclesiastic .note{clear: both; font-size:11px; font-style:italic; padding:5px; white-space:nowrap;}
		#ecclesiastic .non_movable{clear:both;margin-top:40px;}
		#ecclesiastic .non_movable_list{border:1px solid black; max-width:95%; height:200px; overflow:auto; padding:10px;}
		#popup{font-size:12px;}
		#popup p{padding:5px;}
	</style>
	<div id="ecclesiastic">
		<div id="acknowledgement">' .
			/* I18N: Acknowledgement of origin for a calendar utility */
			WT_I18N::translate('
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
		<h1>' . WT_I18N::translate('Enter the year in question') . '</h1>
		<form name="EasterCalculator">
			<label class="label_ec year">' . WT_I18N::translate('Christian Year') . '*
				<input type="text" name="input" value="1492" size="7">
				<p class="note">* ' . WT_I18N::translate('The Ecclesiastical year begins on January 1.') . '</p>
			</label>
			<input class="button_ec" type="button" name="CalculateHolidays" value="' . WT_I18N::translate('Calculate Holidays') . '" onclick="CalculateEaster()">
			<label class="label_ec">' . WT_I18N::translate('Easter') . '
				<input type="text" size="23" name="Easter" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Septuagesima') . '
				<input type="text" size="23" name="Septuagesima" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Ash Wednesday') . ':
				<input type="text" size="23" name="Ash" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Ascension') . '
				<input type="text" size="23" name="Ascension" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Pentecost') . '
				<input type="text" size="23" name="Pentecost" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Trinity Sunday') . '
				<input type="text" size="23" name="Trinity" maxlength="150">
			</label>
			<label class="label_ec">' . WT_I18N::translate('Advent Sunday') . '
				<input type="text" size="23" name="Advent" maxlength="150">
			</label>
		</form>
		<div class="non_movable">
			<h1>' . WT_I18N::translate('Full list of moveable and fixed holidays') . '</h1>
			<div class="non_movable_list">
				<p class="note">' . WT_I18N::translate('The "octave" of any holiday = eight days after the holiday- counting the holiday itself. Thus the octave of a Sunday is the following Sunday.') . '</p>';
				foreach ($non_movable as $e_event => $e_date) {
					$html.= '
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:30%;clear:left;;">'. $e_event. '</div>
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:70%;">'. $e_date. '</div>';
				}

			$html.= '
				</div>
				<p class="note">* ' . WT_I18N::translate('After 1570, subtract one week.') . '</p>
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
			p = "<?php echo WT_I18N::translate('April'); ?> " + p
			//   + " E=" + E + " P=" +P + " L=" + L + " l=" + l + " C=" + C
		} else {
			p = "<?php echo WT_I18N::translate('March'); ?> " + p
			// + " E=" + E + " P=" +P + " L=" + L + " l=" + l
		}
		H = Marchdate - 46
		if (H>0) {H = "<?php echo WT_I18N::translate('March'); ?> " + H}
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
			H = "<?php echo WT_I18N::translate('February'); ?> " + H
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
			if (S > 0) {S = "<?php echo WT_I18N::translate('February'); ?> " + S}
		else {
			S = S + 31
			S = "<?php echo WT_I18N::translate('January'); ?> " + S}
			A = Marchdate + 39
		if (A<62) {	A=A-31
			A = "<?php echo WT_I18N::translate('April'); ?> " + A
		} else {
			A=A-61
			if (A>31)
			{A=A-31
			A = "<?php echo WT_I18N::translate('June'); ?> " + A}
			else
			{A="<?php echo WT_I18N::translate('May'); ?> " + A}
		}
		Pent = Marchdate + 49
		if (Pent<92.5) {
			Pent = Pent - 61
			Pent = "<?php echo WT_I18N::translate('May'); ?> " + Pent
		} else {	Pent = Pent - 92
			Pent = "<?php echo WT_I18N::translate('June'); ?> " + Pent
		}
		T = Marchdate + 56
		if (T<93) {
			T = T-61
			T = "<?php echo WT_I18N::translate('May'); ?> " + T
		} else {	T = T - 92
			T = "<?php echo WT_I18N::translate('June'); ?> " + T
		}

		Advent = Marchdate + 1
		Advent = Advent % 7
		Advent = Advent + 27
		if (Advent<31) {
			Advent = "<?php echo WT_I18N::translate('November'); ?> " + Advent
		} else {		Advent = Advent - 30
			Advent = "<?php echo WT_I18N::translate('December'); ?> " + Advent
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
