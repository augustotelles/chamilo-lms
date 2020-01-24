<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Loader;

use Chamilo\PluginBundle\MigrationMoodle\Interfaces\LoaderInterface;

/**
 * Class CourseModulesScormLoader.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Loader
 */
class CourseModulesScormLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(array $incomingData)
    {
        $tblLpMain = \Database::get_course_table(TABLE_LP_MAIN);

        $resultDisplayOrder = \Database::query(
            "SELECT
                CASE WHEN MAX(display_order) > 0 THEN MAX(display_order) + 1 ELSE 1 END AS display_order
                FROM $tblLpMain WHERE c_id = {$incomingData['c_id']}"
        );
        $row = \Database::fetch_assoc($resultDisplayOrder);
        $displayOrder = $row['display_order'];

        $now = api_get_utc_datetime();
        $courseInfo = api_get_course_info_by_id($incomingData['c_id']);
        $userId = 1;

        $incomingData['path'] = str_replace('.zip', '/.', $incomingData['path']);
        $incomingData['use_max_score'] = $incomingData['use_max_score'] == 100;

        $params = array_merge(
            $incomingData,
            [
                'lp_type' => 2,
                'description' => '',
                'force_commit' => 0,
                'default_view_mod' => 'embedded',
                'default_encoding' => 'UTF-8',
                'js_lib' => 'scorm_api.php',
                'display_order' => $displayOrder,
                'session_id' => 0,
                'content_maker' => '',
                'content_license' => '',
                'debug' => 0,
                'theme' => '',
                'preview_image' => '',
                'author' => '',
                'prerequisite' => 0,
                'seriousgame_mode' => 0,
                'autolaunch' => 0,
                'category_id' => 0,
                'max_attempts' => 0,
                'subscribe_users' => 0,
                'created_on' => $now,
                'modified_on' => $now,
                'publicated_on' => $now,
            ]
        );

        $lpId = \Database::insert($tblLpMain, $params);

        if ($lpId) {
            \Database::query("UPDATE $tblLpMain SET id = iid WHERE iid = $lpId");

            api_item_property_update($courseInfo, TOOL_LEARNPATH, $lpId, 'LearnpathAdded', $userId);
            api_item_property_update($courseInfo, TOOL_LEARNPATH, $lpId, 'visible', $userId);
        }

        return $lpId;
    }
}
