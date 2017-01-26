<?php
/*
Conversion for automatic config in kiwitrees
*/

require './includes/session.php';

#Vars - written at 2017-01-26
$dbhost = $dbconfig['dbhost']; // kiwitrees modification
$dbname = $dbconfig['dbname']; // kiwitrees modification
$dbuser = $dbconfig['dbuser']; // kiwitrees modification
$dbpass = $dbconfig['dbpass']; // kiwitrees modification
$dbport = $dbconfig['dbport']; // kiwitrees modification
$dbsocket="";
$compression=1;
$backup_path="C:\Program Files (x86)\Ampps\www\kiwitrees\MySQLDumper/work/backup/";
$logdatei="C:\Program Files (x86)\Ampps\www\kiwitrees\MySQLDumper/work/log/mysqldump_perl.log.gz";
$completelogdatei="C:\Program Files (x86)\Ampps\www\kiwitrees\MySQLDumper/work/log/mysqldump_perl.complete.log.gz";
$sendmail_call="/usr/lib/sendmail -t -oi -oem";
$nl="\n";
$cron_dbindex=-3;
$cron_printout=1;
$cronmail=0;
$cronmail_dump=0;
$cronmailto="";
$cronmailto_cc="";
$cronmailfrom="";
$cron_use_sendmail=1;
$cron_smtp="localhost";
$cron_smtp_port="25";
@cron_db_array=("kiwitrees","meijberg","performance_schema","webtrees");
@cron_dbpraefix_array=("","","","");
@cron_command_before_dump=("","","","");
@cron_command_after_dump=("","","","");
@ftp_server=("","","");
@ftp_port=(21,21,21);
@ftp_mode=(0,0,0);
@ftp_user=("","","");
@ftp_pass=("","","");
@ftp_dir=("","","");
@ftp_timeout=(30,30,30);
@ftp_useSSL=(0,0,0);
@ftp_transfer=(0,0,0);
$mp=0;
$multipart_groesse=1048576;
$email_maxsize=3145728;
$auto_delete=0;
$max_backup_files=3;
$perlspeed=10000;
$optimize_tables_beforedump=0;
$logcompression=1;
$log_maxsize=1048576;
$complete_log=1;
$my_comment="";
?>
