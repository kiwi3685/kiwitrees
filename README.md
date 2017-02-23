#Kiwitrees
## Contents

1. [Introduction](#introduction)
1. [License](#license)
1. [Requirements](#requirements)
1. [Installing](#installing)
1. [Translations](#translations)
1. [Contributing](#contributing)


##Introduction
Kiwitrees is among the web’s leading online collaborative genealogy applications. The project website is [kiwitrees.net](http://kiwitrees.net/).

It works from standard GEDCOM files, and is therefore compatible with every major desktop application. It aims to to be efficient and effective by using the right combination of third-party tools, design techniques and open standards.

**For more information visit the website [FAQ pages](http://kiwitrees.net/faqs/).**

##License
* **Kiwitrees: Web based Family History software**
* **Copyright (&copy;) 2012 to 2017 kiwitrees.net**

Derived from webtrees (www.webtrees.net)

Copyright (&copy;) 2010 to 2012 webtrees development team

Derived from PhpGedView (phpgedview.sourceforge.net)

Copyright (&copy;) 2002 to 2010 PGV Development Team

All rights reserved.

Kiwitrees is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by The Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Kiwitrees.  If not, see http://www.gnu.org/licenses.

**[Back to top](#kiwitrees)**

##Requirements
### A webserver
*   Apache and IIS are the most common types. There are no requirements to use a specific type or version.
*   Approximately 65MB of disk space for the application files, plus whatever is needed for your multi-media files, GEDCOM files and database.

### PHP
*   PHP 5.3.3 or later. Note that many web hosts offer *both* PHP4 and PHP5, typically with PHP4 as the default. If this is the case, you will be able to switch between the two using a control panel or a configuration file. Refer to your web host's support documentation for details. <span style="color: #008000;">_**PHP 7+** can also be used. However testing on this platform is not complete so bugs may still exist. Please use the **support forum** to report and discuss these. They will be fixed as soon as we are aware of any new ones._</span>
    *   PHP should be configured with the PHP/PDO library for MySQL. This is a server configuration option. It is enabled by default on most hosts. See [http://php.net/pdo](http://php.net/pdo)
    *   PHP should be configured to allow sufficient server resources (memory and execution time) for the size of your system. Typical requirements are:
        *   Small systems (500 individuals): 16-32MB, 10-20 seconds
        *   Medium systems (5000 individuals): 32-64MB, 20-40 seconds
        *   Large systems (50000 individuals): 64-128MB, 40-80 seconds

### MySQL
*   MySQL 5.0.13 or later. Note that kiwitrees can share a single database with other applications by choosing a unique table prefix during configuration. If the number of databases is not restricted, you can set up a database purely for use by kiwitrees and create a separate user and password for only your genealogy.

### A compatible internet browser.
*   Kiwitrees supports the use of most current versions of open-source browsers such as Firefox, Chrome, and Safari. We will do our best to support others such as Opera and Internet Explorer, though not their earlier versions. Currently many things do not work well in IE7, and some not in IE8 either. **We strongly recommend anyone using these obsolete browsers upgrade as soon as possible.** We are also aware that IE and Opera browsers provide poor RTL language support generally, so cannot recommend those for sites requiring RTL languages.
*   To view sites that contain both left-to-right and right-to-left text (e.g. English data on Hebrew pages), you will need to use a browser that provides support for the HTML5 dir="auto" attribute. At present, only browsers based on the WebKit engine (Chrome and Safari) have this. The Gecko (Firefox) and Presto (Opera) engines promise to provide this soon.
*   HTML Frames. Note that kiwitrees uses cookies to track login sessions. Sites that make kiwitrees pages available inside an HTML Frames will encounter problems with login for versions 7, 8, and 9 of Internet Explorer. IE users should review the Privacy settings Tools / Internet Options for more details.

**[Back to top](#kiwitrees)**

## Installing
All you need to install kiwitrees is a webserver with PHP and MySQL. Almost every web hosting service provides these, but be sure to confirm that those supplied meet or exceed the minimum system requirements. Download the latest version of kiwitrees available from <span style="color: #857d50;">**[the downloads page](http://kiwitrees.net/services/downloads/)**</span>

1.  Unzip the files and upload them to an empty directory on your web server.
2.  Open your web browser and type the URL for your kiwitrees site (for example, http://www.yourserver.com/kiwitrees) into the address bar.
3.  The kiwitrees setup wizard will start automatically. Simply follow the steps, answering each question as you proceed.

That's it! However, before you can use kiwitrees, you need one (or possibly more) GEDCOM (family tree) files. If you have been doing your research using a desktop program such as Family Tree Maker, you can use it's "save as GEDCOM" function to create a GEDCOM file. If you are starting from scratch, then kiwitrees can create a GEDCOM file for you. So, after installation, you'll be directed to the GEDCOM (family tree) administration page, where you'll need to select one of the following options:

1.  UPLOAD a GEDCOM file from your local machine
2.  ADD a GEDCOM file from your server, (if your GEDCOM file is too large to upload, you can copy it to the kiwitrees/data folder, and load it from there)
3.  CREATE a new, empty GEDCOM file

There are _lots_ of configuration options. You'll probably want to review the privacy settings first. Don't worry too much about all the other options - the defaults are good for most people. If you get stuck, there's lots of built-in help and you can get friendly advice from the <span style="color: #857d50;">**help**</span> forum.

**[Back to top](#kiwitrees)**

## Translations
* [Transifex](https://www.transifex.com/projects/p/kiwitrees/)

**[Back to top](#kiwitrees)**

##Contributing
Either visit the [project form](http://kiwitrees.net/forums/) or open an issue on [Github](https://github.com/kiwi3685/kiwitrees) first to discuss potential changes/additions.

**[Back to top](#kiwitrees)**
