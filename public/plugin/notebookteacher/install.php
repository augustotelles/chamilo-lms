<?php

/* For license terms, see /license.txt */

/**
 * This script is included by main/admin/settings.lib.php and generally
 * includes things to execute in the main database (settings_current table).
 */
require_once __DIR__.'/config.php';
if (!api_is_platform_admin()) {
    die('You must have admin permissions to install plugins');
}
NotebookTeacherPlugin::create()->install();
