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

// Plugin name - this needs double quotes, as file is scanned/parsed by script
$plugin_name = "Ecclesiastical Dates"; /* I18N: Name of a plugin. */ KT_I18N::translate('Ecclesiastical Dates');

// DATA
/* List of non-movable feasts */
$non_movable = array (
	KT_I18N::translate('Lent')													=> KT_I18N::translate('40 week days between Ash Wednesday and Easter'),
	KT_I18N::translate('Advent')												=> KT_I18N::translate('Period from Advent Sunday to December 25'),
	KT_I18N::translate('Sexagesima Sunday')										=> KT_I18N::translate('Sunday after Septuagesima Sunday.'),
	KT_I18N::translate('Quinquagesima Sunday')									=> KT_I18N::translate('2nd Sunday after Septuagesima Sunday'),
	KT_I18N::translate('Shrove Tuesday')										=> KT_I18N::translate('day before Ash Wednesday.'),
	KT_I18N::translate('Passion Sunday')										=> KT_I18N::translate('2nd Sunday before Easter'),
	KT_I18N::translate('Palm Sunday')											=> KT_I18N::translate('Sunday before Easter'),
	KT_I18N::translate('Quasimodo (Low) Sunday')								=> KT_I18N::translate('Sunday after Easter'),
	KT_I18N::translate('Good Friday')											=> KT_I18N::translate('Friday before Easter'),
	KT_I18N::translate('Maundy Thursday')										=> KT_I18N::translate('Thursday before Easter'),
	KT_I18N::translate('Adorate dominum')										=> KT_I18N::translate('3rd Sunday after January 6'),
	KT_I18N::translate('Adrian (Canterbury)')									=> KT_I18N::translate('January 9'),
	KT_I18N::translate('Ad te levavi')											=> KT_I18N::translate('Advent Sunday'),
	KT_I18N::translate('Agatha')												=> KT_I18N::translate('February 5'),
	KT_I18N::translate('Agnes')													=> KT_I18N::translate('January 21'),
	KT_I18N::translate('Alban')													=> KT_I18N::translate('June 22 (or June in 1662 Prayer Book)'),
	KT_I18N::translate('Aldhelm')												=> KT_I18N::translate('May 25'),
	KT_I18N::translate('All Hallows')											=> KT_I18N::translate('November 1'),
	KT_I18N::translate('All Saints')											=> KT_I18N::translate('November 1'),
	KT_I18N::translate('All Souls')												=> KT_I18N::translate('November 2'),
	KT_I18N::translate('Alphege')												=> KT_I18N::translate('April 19'),
	KT_I18N::translate('Ambrose')												=> KT_I18N::translate('April 4'),
	KT_I18N::translate('Andrew')												=> KT_I18N::translate('November 30'),
	KT_I18N::translate('Anne')													=> KT_I18N::translate('July 26'),
	KT_I18N::translate('Annunciation')											=> KT_I18N::translate('March 25'),
	KT_I18N::translate('Ante Portram Latinam')									=> KT_I18N::translate('May 6'),
	KT_I18N::translate('Aspiciens a longe')										=> KT_I18N::translate('Advent Sunday'),
	KT_I18N::translate('Audoenus (Ouen)')										=> KT_I18N::translate('August 24 or 25'),
	KT_I18N::translate('Audrey (Ethelreda)')									=> KT_I18N::translate('October 17'),
	KT_I18N::translate('Augustine (Canterbury)')								=> KT_I18N::translate('May 26'),
	KT_I18N::translate('Augustine (Hippo)')										=> KT_I18N::translate('August 28'),
	KT_I18N::translate('Barnabas')												=> KT_I18N::translate('June 11'),
	KT_I18N::translate('Bartholomew')											=> KT_I18N::translate('August 24'),
	KT_I18N::translate('Bede, Venerable')										=> KT_I18N::translate('May 27'),
	KT_I18N::translate('Benedict')												=> KT_I18N::translate('March 21'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Translation of Benedict')	=> KT_I18N::translate('July 11'),
	KT_I18N::translate('Birinus')												=> KT_I18N::translate('December 3'),
	KT_I18N::translate('Blasius')												=> KT_I18N::translate('February 3'),
	KT_I18N::translate('Boniface')												=> KT_I18N::translate('June 5'),
	KT_I18N::translate('Botolph')												=> KT_I18N::translate('June 17'),
	KT_I18N::translate('Bricius')												=> KT_I18N::translate('November 13'),
	KT_I18N::translate('Candlemas')												=> KT_I18N::translate('February 2'),
	KT_I18N::translate('Canite Tuba')											=> KT_I18N::translate('4th Sunday in Advent'),
	KT_I18N::translate('Cantate domino')										=> KT_I18N::translate('4th Sunday after Easter'),
	KT_I18N::translate('Cathedra Petri')										=> KT_I18N::translate('February 22'),
	KT_I18N::translate('Catherine')												=> KT_I18N::translate('November 25'),
	KT_I18N::translate('Cecilia')												=> KT_I18N::translate('November 22'),
	KT_I18N::translate('Cena domini')											=> KT_I18N::translate('Thursday before Easter'),
	KT_I18N::translate('Chad (Cedde)')											=> KT_I18N::translate('March 2'),
	KT_I18N::translate('Christmas (Natale Domini)')								=> KT_I18N::translate('December 25'),
	KT_I18N::translate('Christopher')											=> KT_I18N::translate('July 25'),
	KT_I18N::translate('Circumcision')											=> KT_I18N::translate('January 1'),
	KT_I18N::translate('Clausum Pasche')										=> KT_I18N::translate('1st Sunday after Easter'),
	KT_I18N::translate('Clement')												=> KT_I18N::translate('November 23'),
	KT_I18N::translate('Cornelius+Cyprian')										=> KT_I18N::translate('September 14'),
	KT_I18N::translate('Corpus Christi')										=> KT_I18N::translate('Thursday after Trinity*'),
	KT_I18N::translate('Crispin and Crispinian')								=> KT_I18N::translate('October 25'),
	KT_I18N::translate('Cuthbert')												=> KT_I18N::translate('March 20'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;translation of Cuthbert')	=> KT_I18N::translate('September 4'),
	KT_I18N::translate('Cyprian and Justina')									=> KT_I18N::translate('September 26'),
	KT_I18N::translate('Daemon mutus')											=> KT_I18N::translate('3rd Sunday in Lent (after Ash Wednesday)'),
	KT_I18N::translate('Da pacem')												=> KT_I18N::translate('18th Sunday after Trinity*'),
	KT_I18N::translate('David')													=> KT_I18N::translate('March 1'),
	KT_I18N::translate('Deus in adiutorium')									=> KT_I18N::translate('12th Sunday after Trinity*'),
	KT_I18N::translate('Deus in loco sancto')									=> KT_I18N::translate('11th Sunday after Trinity*'),
	KT_I18N::translate('Deus qui errantibus')									=> KT_I18N::translate('3rd Sunday after Easter'),
	KT_I18N::translate('Dicit dominus')											=> KT_I18N::translate('23rd and 24th Sunday after Trinity*'),
	KT_I18N::translate('Dies cinerum')											=> KT_I18N::translate('Ash Wednesday'),
	KT_I18N::translate('Dies crucis adorande')									=> KT_I18N::translate('Good Friday'),
	KT_I18N::translate('Dies Mandati')											=> KT_I18N::translate('Maundy Thursday'),
	KT_I18N::translate('Dionysius, Rusticus, and Eleutherius')					=> KT_I18N::translate('October 9'),
	KT_I18N::translate('Domine, in tua misericordia')							=> KT_I18N::translate('1st Sunday after Trinity*'),
	KT_I18N::translate('Domine, ne longe')										=> KT_I18N::translate('Palm Sunday'),
	KT_I18N::translate('Dominus fortitudo')										=> KT_I18N::translate('6th Sunday after Trinity*'),
	KT_I18N::translate('Dominus illuminatio mea')								=> KT_I18N::translate('4th Sunday after Trinity*'),
	KT_I18N::translate('Dum, clamarem')											=> KT_I18N::translate('10th Sunday after Trinity*'),
	KT_I18N::translate('Epiphany')												=> KT_I18N::translate('January 6'),
	KT_I18N::translate('Dum medium silentium')									=> KT_I18N::translate(' Sunday in octave of Christmas or Sunday after January 1 when this falls on eve of Epiphany (Jan. 6)'),
	KT_I18N::translate('Dunstan')												=> KT_I18N::translate('May 19'),
	KT_I18N::translate('Eadburga (Winchester)')									=> KT_I18N::translate('June 15'),
	KT_I18N::translate('Ecce deus adiuvat')										=> KT_I18N::translate('9th Sunday after Trinity*'),
	KT_I18N::translate('Editha')												=> KT_I18N::translate('September 16'),
	KT_I18N::translate('Edmund (archbishop)')									=> KT_I18N::translate('November 16'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('June 9'),
	KT_I18N::translate('Edmund (king)')											=> KT_I18N::translate('November 20'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('April 29'),
	KT_I18N::translate('Edward the Confessor')									=> KT_I18N::translate('January 5'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('October 13 often called the feast of St. Edward in the quidene of Michaelmas'),
	KT_I18N::translate('Edward (king of Saxons)')								=> KT_I18N::translate('March 18'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation I')		=> KT_I18N::translate('February 18'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation II')		=> KT_I18N::translate('June 20'),
	KT_I18N::translate('Egidius (Giles)')										=> KT_I18N::translate('September 1'),
	KT_I18N::translate('Enurchus (Evurcius)')									=> KT_I18N::translate('September 7'),
	KT_I18N::translate('Esto mihi')												=> KT_I18N::translate('Sunday before Ash Wednesday (Quinquagesima)'),
	KT_I18N::translate('Ethelbert (king)')										=> KT_I18N::translate('May 20'),
	KT_I18N::translate('Ethelreda')												=> KT_I18N::translate('October 17'),
	KT_I18N::translate('Euphemia')												=> KT_I18N::translate('September 16'),
	KT_I18N::translate('Eustachius')											=> KT_I18N::translate('November 2'),
	KT_I18N::translate('Exaltation of the Cross')								=> KT_I18N::translate('September 14'),
	KT_I18N::translate('Exaudi domine')											=> KT_I18N::translate('Sunday in octave of Ascension or 5th Sunday after octave of Pentecost (Trinity)*'),
	KT_I18N::translate('Exsurge domine')										=> KT_I18N::translate('2nd Sunday before Ash Wednesday (Sexagesima)'),
	KT_I18N::translate('Fabian and Sebastian')									=> KT_I18N::translate('January 20'),
	KT_I18N::translate('Factus est dominus')									=> KT_I18N::translate('2nd Sunday after Trinity*'),
	KT_I18N::translate('Faith')													=> KT_I18N::translate('October 6'),
	KT_I18N::translate('Felicitas')												=> KT_I18N::translate('November 23'),
	KT_I18N::translate('Fransiscus')											=> KT_I18N::translate('October 4'),
	KT_I18N::translate('Gaudete in domino')										=> KT_I18N::translate('3rd Sunday in Advent'),
	KT_I18N::translate('George')												=> KT_I18N::translate('April 23'),
	KT_I18N::translate('Gregory')												=> KT_I18N::translate('March 12'),
	KT_I18N::translate('Grimbold')												=> KT_I18N::translate('July 8'),
	KT_I18N::translate('Gule of August')										=> KT_I18N::translate('August 1'),
	KT_I18N::translate('Guthlac')												=> KT_I18N::translate('April 11'),
	KT_I18N::translate('Hieronymous (Jerome)')									=> KT_I18N::translate('September 30'),
	KT_I18N::translate('Hilary')												=> KT_I18N::translate('January 13'),
	KT_I18N::translate('Hugh (bishop of Lincoln)')								=> KT_I18N::translate('November 17'),
	KT_I18N::translate('Inclina auram tuam')									=> KT_I18N::translate('15th Sunday after Trinity*'),
	KT_I18N::translate('In excelso throno')										=> KT_I18N::translate('1st Sunday after Epiphany'),
	KT_I18N::translate('In Monte tumba')										=> KT_I18N::translate('October 16'),
	KT_I18N::translate('Innocents')												=> KT_I18N::translate('December 28'),
	KT_I18N::translate('Invention of the Cross')								=> KT_I18N::translate('May 3'),
	KT_I18N::translate('Invocavit me')											=> KT_I18N::translate('1st Sunday in Lent'),
	KT_I18N::translate('In voluntate tua')										=> KT_I18N::translate('21st Sunday afterTrinity*'),
	KT_I18N::translate('Isti sunt dies')										=> KT_I18N::translate('Passion Sunday'),
	KT_I18N::translate('James')													=> KT_I18N::translate('July 25'),
	KT_I18N::translate('Jerome (Hieronymus)')									=> KT_I18N::translate('September 30'),
	KT_I18N::translate('John the Baptist')										=> KT_I18N::translate('June 24'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his beheading')			=> KT_I18N::translate('August 29'),
	KT_I18N::translate('John the Evangelist')									=> KT_I18N::translate('December 27'),
	KT_I18N::translate('Jubilate omnis terra')									=> KT_I18N::translate('3rd Sunday after Easter'),
	KT_I18N::translate('Judica me')												=> KT_I18N::translate('Passion Sunday'),
	KT_I18N::translate('Judoc')													=> KT_I18N::translate('December 13'),
	KT_I18N::translate('Justus es domine')										=> KT_I18N::translate('17th Sunday after the octave of Pentecost (Trinity)*'),
	KT_I18N::translate('Lady day (annunciation)')								=> KT_I18N::translate('March 25'),
	KT_I18N::translate('Laetare Jerusalem')										=> KT_I18N::translate('4th Sunday in Lent'),
	KT_I18N::translate('Lambert')												=> KT_I18N::translate('September 17'),
	KT_I18N::translate('Lammas')												=> KT_I18N::translate('August 1'),
	KT_I18N::translate('Laudus')												=> KT_I18N::translate('September 21'),
	KT_I18N::translate('Laurence')												=> KT_I18N::translate('August 10'),
	KT_I18N::translate('Leonard')												=> KT_I18N::translate('November 6'),
	KT_I18N::translate('Lucianus and Geminianus')								=> KT_I18N::translate('September 16'),
	KT_I18N::translate('Lucian')												=> KT_I18N::translate('January 8'),
	KT_I18N::translate('Lucy')													=> KT_I18N::translate('December 13'),
	KT_I18N::translate('Luke')													=> KT_I18N::translate('October 18'),
	KT_I18N::translate('Machutus')												=> KT_I18N::translate('November 15'),
	KT_I18N::translate('Margaret (queen of Scotland)')							=> KT_I18N::translate('July 8'),
	KT_I18N::translate('Margaret (virgin and martyr)')							=> KT_I18N::translate('July 20'),
	KT_I18N::translate('Mark')													=> KT_I18N::translate('April 25'),
	KT_I18N::translate('Martin')												=> KT_I18N::translate('November 11'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('July 4'),
	KT_I18N::translate('Mary, Blessed Virgin')									=> KT_I18N::translate('&nbsp;'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Annunciation (Lady day)')	=> KT_I18N::translate('March 25'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Assumption')				=> KT_I18N::translate('August 15'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Conception')				=> KT_I18N::translate('December 8'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nativity')				=> KT_I18N::translate('September 8'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Purification')			=> KT_I18N::translate('February 2'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitation')				=> KT_I18N::translate('July 2'),
	KT_I18N::translate('Mary Magdalene')										=> KT_I18N::translate('July 22'),
	KT_I18N::translate('Mathias')												=> KT_I18N::translate('February 24 (25 on leap years)'),
	KT_I18N::translate('Matthew')												=> KT_I18N::translate('September 21'),
	KT_I18N::translate('Maurice')												=> KT_I18N::translate('September 22'),
	KT_I18N::translate('Meliorus')												=> KT_I18N::translate('October 1'),
	KT_I18N::translate('Memento mei')											=> KT_I18N::translate('4th Sunday in Advent'),
	KT_I18N::translate('Michael')												=> KT_I18N::translate('September 29'),
	KT_I18N::translate('Mildred')												=> KT_I18N::translate('July 13'),
	KT_I18N::translate('Miserere mihi')											=> KT_I18N::translate('16th Sunday after Trinity*'),
	KT_I18N::translate('Misericordia domini')									=> KT_I18N::translate('2nd Sunday after Easter'),
	KT_I18N::translate('Name of Jesus')											=> KT_I18N::translate('August 7'),
	KT_I18N::translate('Nicholas')												=> KT_I18N::translate('December 6'),
	KT_I18N::translate('Nicomedes')												=> KT_I18N::translate('June 1'),
	KT_I18N::translate('Oculi')													=> KT_I18N::translate('3rd Sunday in Lent'),
	KT_I18N::translate('Omnes gentes')											=> KT_I18N::translate('7th Sunday after Trinity*'),
	KT_I18N::translate('Omnia quae fecisti')									=> KT_I18N::translate('20th Sunday after Trinity*'),
	KT_I18N::translate('Omnis terra')											=> KT_I18N::translate('2nd Sunday after Epiphany'),
	KT_I18N::translate('Osanna')												=> KT_I18N::translate('Palm Sunday'),
	KT_I18N::translate('O Sapientia')											=> KT_I18N::translate('December 16'),
	KT_I18N::translate('Osmund')												=> KT_I18N::translate('December 4'),
	KT_I18N::translate('Oswald (bishop)')										=> KT_I18N::translate('February 28'),
	KT_I18N::translate('Oswald (king)')											=> KT_I18N::translate('August 5'),
	KT_I18N::translate('Patrick')												=> KT_I18N::translate('March 17'),
	KT_I18N::translate('Paul, Conversion of')									=> KT_I18N::translate('January 25'),
	KT_I18N::translate('Perpetua')												=> KT_I18N::translate('March 7'),
	KT_I18N::translate('Peter and Paul')										=> KT_I18N::translate('June 29'),
	KT_I18N::translate('Peter and Vincula')										=> KT_I18N::translate('August 1'),
	KT_I18N::translate('Philip and James')										=> KT_I18N::translate('May 1'),
	KT_I18N::translate('Populus Sion')											=> KT_I18N::translate('2nd Sunday in Advent'),
	KT_I18N::translate('Prisca')												=> KT_I18N::translate('January 18'),
	KT_I18N::translate('Priscus')												=> KT_I18N::translate('September 1'),
	KT_I18N::translate('Protector noster')										=> KT_I18N::translate('14th Sunday after Trinity*'),
	KT_I18N::translate('Quasimodo')												=> KT_I18N::translate('1st Sunday after Easter'),
	KT_I18N::translate('Reddite quae sunt')										=> KT_I18N::translate('23rd Sunday after*'),
	KT_I18N::translate('Remigius, Germanus, and Vedastus Reminiscere')			=> KT_I18N::translate('2nd Sunday in Lent'),
	KT_I18N::translate('Reminiscere')											=> KT_I18N::translate('2nd Sunday in Lent'),
	KT_I18N::translate('Respice domine')										=> KT_I18N::translate('13th Sunday after Pentecost'),
	KT_I18N::translate('Respice in me')											=> KT_I18N::translate('3rd Sunday after Trinity*'),
	KT_I18N::translate('Richard')												=> KT_I18N::translate('April 3'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('July 15'),
	KT_I18N::translate('Rorate celi')											=> KT_I18N::translate('4th Sunday in Advent'),
	KT_I18N::translate('Salus populi')											=> KT_I18N::translate('19th Sunday after Pentecost'),
	KT_I18N::translate('Scholastica')											=> KT_I18N::translate('February 10'),
	KT_I18N::translate('Si iniquitates')										=> KT_I18N::translate('22nd Sunday after Trinity*'),
	KT_I18N::translate('Silvester')												=> KT_I18N::translate('December 31'),
	KT_I18N::translate('Simon and Jude')										=> KT_I18N::translate('October 28'),
	KT_I18N::translate('Sitientes')												=> KT_I18N::translate('Saturday before Passion Sunday'),
	KT_I18N::translate('Stephen')												=> KT_I18N::translate('December 26'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his invention')			=> KT_I18N::translate('August 3'),
	KT_I18N::translate('Suscepius deus')										=> KT_I18N::translate('8th Sunday after Trinity*'),
	KT_I18N::translate('Swithun')												=> KT_I18N::translate('July 2'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('July 3'),
	KT_I18N::translate('Thomas the Apostle')									=> KT_I18N::translate('December 21'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('July 3'),
	KT_I18N::translate('Thomas Becket')											=> KT_I18N::translate('December 29'),
	KT_I18N::translate('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;his translation')			=> KT_I18N::translate('July 7'),
	KT_I18N::translate('Timotheus and Symphorianus')							=> KT_I18N::translate('August 22'),
	KT_I18N::translate('Transfiguration')										=> KT_I18N::translate('August 6'),
	KT_I18N::translate('Urban')													=> KT_I18N::translate('May 25'),
	KT_I18N::translate('Valentine')												=> KT_I18N::translate('February 14'),
	KT_I18N::translate('Vincent')												=> KT_I18N::translate('January 22'),
	KT_I18N::translate('Viri Galilei')											=> KT_I18N::translate('Ascension Day'),
	KT_I18N::translate('Vocem jucunditatis')									=> KT_I18N::translate('5th Sunday after Easter'),
	KT_I18N::translate('Wilfrid')												=> KT_I18N::translate('January 19'),
);

// HELP //

$help1 = htmlspecialchars(addslashes(KT_I18N::translate('<div id="popup"><p>There are several different areas. They operate as follows.</p><p><b>1. Calculation of major moveable feasts.</b></p><p>Since the ecclesiastical calendar is based on lunar rather than solar cycles, certain key holidays (feasts) occur on different days each year. The method of calculating these feasts has also changed since the council of Nicea (325 A.D.). The button labeled "Calculate Holidays" calculates the dates of seven major feasts for the year entered in the field labeled "Year", and displays the results below this field. The only restrictions are that the number entered in the field "Year" must be an integer (no fractions) greater than zero. However, the holidays generated are only valid for dates since 325 A.D. (Early Christian and Roman dating is another story). Also, for purposes of calculation, I have assumed that the ecclesiastical year begins on January first, even though this standard was only gradually accepted. If you are working with early monastic documents you might want to consider that dates from December 25th through March may be "off" by one year. To a Benedictine, for instance (to whom the year began on December 25th), the feast of the Innocents in 1450 would be December 28th, 1450, while to others it might be December 28th, 1449. In fact, before 1582, most calendars did not have the year begin on January 1st, even though the calculation of the moveable feasts acted as if it did. In England, the year "began" either on December 25th, or, more frequently on March 25th (Lady Day), until 1752. These vagaries are not something I wanted to include in the calculations, since they often varied quite a bit. The calculations for the holidays will take into account the days dropped from the calendar when the "New style" was adopted, since these affect the month and day of Easter. Conventions about the beginning of the year are easily corrected for.</p><p><b>2. Old and New style dating and Day of the week</b></p><p>When the Pope Gregory revised the calendar in 1582, a certain number of days were omitted from the calendar at a particular time, resulting in two separate styles of dating. England persisted in using the "Old Style" until 1752, because of religious differences. The Old Style also often dated the beginning of the year from March 25 rather than January 1, and this area of the site takes this difference into account. Thus March 8, 1735, Old Style is really March 19, 1736 in the New Style! Occasionally, particularly in dating material between 1582 and 1755 or so, it becomes necessary to convert back and forth.</p><p>Here is a more detailed description of the transition year (in England): 1752.</p><p>The area called "Convert Old Style to New Style" converts the old style date to a new style date and returns an answer. The area called "Convert New Style to Old Style" converts the new style date to an old style date and enters it in the "Old style" fields. You must enter an integer greater than zero for the year.</p><p>You can also calculate the day of the week for both old and new style dates.</p><p>Remember that these forms assume that the year begins on March 25th. If you want to plug in dates derived from the "Ecclesiastical Holidays" area, be aware that you will have to subtract 1 from the year for all dates between January 1st and March 24th.</p><p><b>NOTE FOR STUDENTS OF CONTINENTAL HISTORY</b></p><p>Don\'t try to use this site to calculate dates of documents between 1588 and 1752 because England is a special case during these years.</p></p>')), ENT_QUOTES);

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
		<h1>' . KT_I18N::translate('Enter the year in question') . '</h1>
		<form name="EasterCalculator">
			<label class="label_ec year">' . KT_I18N::translate('Christian Year') . '*
				<input type="text" name="input" value="1492" size="7">
				<p class="note">* ' . KT_I18N::translate('The Ecclesiastical year begins on January 1.') . '</p>
			</label>
			<input class="button_ec" type="button" name="CalculateHolidays" value="' . KT_I18N::translate('Calculate Holidays') . '" onclick="CalculateEaster()">
			<label class="label_ec">' . KT_I18N::translate('Easter') . '
				<input type="text" size="23" name="Easter" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Septuagesima') . '
				<input type="text" size="23" name="Septuagesima" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Ash Wednesday') . ':
				<input type="text" size="23" name="Ash" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Ascension') . '
				<input type="text" size="23" name="Ascension" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Pentecost') . '
				<input type="text" size="23" name="Pentecost" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Trinity Sunday') . '
				<input type="text" size="23" name="Trinity" maxlength="150">
			</label>
			<label class="label_ec">' . KT_I18N::translate('Advent Sunday') . '
				<input type="text" size="23" name="Advent" maxlength="150">
			</label>
		</form>
		<div class="non_movable">
			<h1>' . KT_I18N::translate('Full list of moveable and fixed holidays') . '</h1>
			<div class="non_movable_list">
				<p class="note">' . KT_I18N::translate('The "octave" of any holiday = eight days after the holiday- counting the holiday itself. Thus the octave of a Sunday is the following Sunday.') . '</p>';
				foreach ($non_movable as $e_event => $e_date) {
					$html.= '
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:30%;clear:left;;">'. $e_event. '</div>
						<div style="border-bottom: 1px solid #ccc;float:left;white-space:nowrap;padding:5px 0;width:70%;">'. $e_date. '</div>';
				}

			$html.= '
				</div>
				<p class="note">* ' . KT_I18N::translate('After 1570, subtract one week.') . '</p>
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
			p = "<?php echo KT_I18N::translate('April'); ?> " + p
			//   + " E=" + E + " P=" +P + " L=" + L + " l=" + l + " C=" + C
		} else {
			p = "<?php echo KT_I18N::translate('March'); ?> " + p
			// + " E=" + E + " P=" +P + " L=" + L + " l=" + l
		}
		H = Marchdate - 46
		if (H>0) {H = "<?php echo KT_I18N::translate('March'); ?> " + H}
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
			H = "<?php echo KT_I18N::translate('February'); ?> " + H
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
			if (S > 0) {S = "<?php echo KT_I18N::translate('February'); ?> " + S}
		else {
			S = S + 31
			S = "<?php echo KT_I18N::translate('January'); ?> " + S}
			A = Marchdate + 39
		if (A<62) {	A=A-31
			A = "<?php echo KT_I18N::translate('April'); ?> " + A
		} else {
			A=A-61
			if (A>31)
			{A=A-31
			A = "<?php echo KT_I18N::translate('June'); ?> " + A}
			else
			{A="<?php echo KT_I18N::translate('May'); ?> " + A}
		}
		Pent = Marchdate + 49
		if (Pent<92.5) {
			Pent = Pent - 61
			Pent = "<?php echo KT_I18N::translate('May'); ?> " + Pent
		} else {	Pent = Pent - 92
			Pent = "<?php echo KT_I18N::translate('June'); ?> " + Pent
		}
		T = Marchdate + 56
		if (T<93) {
			T = T-61
			T = "<?php echo KT_I18N::translate('May'); ?> " + T
		} else {	T = T - 92
			T = "<?php echo KT_I18N::translate('June'); ?> " + T
		}

		Advent = Marchdate + 1
		Advent = Advent % 7
		Advent = Advent + 27
		if (Advent<31) {
			Advent = "<?php echo KT_I18N::translate('November'); ?> " + Advent
		} else {		Advent = Advent - 30
			Advent = "<?php echo KT_I18N::translate('December'); ?> " + Advent
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
