<?php
/* ----------------------------------------------------------------------

   MyOOS [Dumper]
   http://www.oos-shop.de/

   Copyright (c) 2013 - 2022 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   MySqlDumper
   http://www.mysqldumper.de

   Copyright (C)2004-2011 Daniel Schlichtholz (admin@mysqldumper.de)
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

if (!defined('MOD_VERSION')) {
    exit('No direct access.');
}

if ($update->newVersionAvailable() && $check_update === true) {
    // Install new update
    echo '<p align="center"><a href="main.php">&lt;&lt; Home</a></p>';

    echo $lang['L_NEW_MOD_VERSION'] . ': ' . $update->getLatestVersion() . '<br>';
    echo $lang['L_INSTALLING_UPDATES'] . ': <br>';
    /*
        echo '<pre>';
        var_dump(array_map(function ($version) {
            return (string) $version;
        }, $update->getVersionsToUpdate()));
        echo '</pre>';
    */
    // Optional - empty log file
    $f = fopen($config['paths']['log'] . 'update.log', 'rb+');
    if ($f !== false) {
        ftruncate($f, 0);
        fclose($f);
    }

    /*
        // Optional Callback function - on each version update
        function eachUpdateFinishCallback($updatedVersion)
        {
            echo '<h3>CALLBACK for version ' . $updatedVersion . '</h3>';
        }
        $update->onEachUpdateFinish('eachUpdateFinishCallback');

        // Optional Callback function - on each version update
        function onAllUpdateFinishCallbacks($updatedVersions)
        {
            echo '<h3>CALLBACK for all updated versions:</h3>';
            echo '<ul>';
            foreach ($updatedVersions as $v) {
                echo '<li>' . $v . '</li>';
            }
            echo '</ul>';
        }
        $update->setOnAllUpdateFinishCallbacks('onAllUpdateFinishCallbacks');
    */

    // This call will only simulate an update.
    // Set the first argument (simulate) to "false" to install the update
    // i.e. $update->update(false);
    $result = $update->update(false);

    if ($result === true) {
        echo $lang['L_UPDATE_SUCCESSFUL'] . '<br>';
    } else {
        echo $lang['L_UPDATE_FAILED'] . ': ' . $result . '!<br>';

        if ($result = AutoUpdate::ERROR_SIMULATE) {
            echo '<pre>';
            var_dump($update->getSimulationResults());
            echo '</pre>';
        }
    }
} else {
    echo $lang['L_UP_TO_DATE']. '<br>';
}

echo 'Log:<br>';
echo nl2br(file_get_contents($config['paths']['log'] . '/update.log'));

echo '<p align="center"><a href="main.php">&lt;&lt; Home</a></p>';
