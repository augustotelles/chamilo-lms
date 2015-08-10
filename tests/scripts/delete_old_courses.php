<?php
/**
 * This script synchronize the exercise score (track_e_exercises.exe_result)
 * with the LP score result (lp_item_view.score).
 * This script works only if 1 there's one attempt
 */

exit;

$creation = '2014-01-01';
$access = '2014-07-01';

require_once '../../main/inc/global.inc.php';

if (PHP_SAPI !== 'cli') {
    die('This script can only be executed from the command line');
}

$tableExercise = Database::get_main_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tableCourse = Database::get_main_table(TABLE_MAIN_COURSE);

$sql = "SELECT
            id, code, directory, db_name, creation_date, last_visit
        FROM $tableCourse c
        WHERE creation_date < '$creation' AND last_visit < '$access'
        ORDER by code
";
echo $sql.PHP_EOL;

$result = Database::query($sql);
$items = Database::store_result($result, 'ASSOC');
$total = 0;
$count = 0;
if (!empty($items)) {
    foreach ($items as $item) {
        $size = exec('du -sh '.__DIR__.'/../../courses/'.$item['directory']);
        echo "Course ".$item['code'].'('.$item['id'].') created on '.$item['creation_date'].' and last used on '.$item['last_visit'].' uses '.substr($size, 0, 8).PHP_EOL;
        if (substr($size, 0, 4) == '160K' or substr($size, 0, 4) == '9,1M') {
            CourseManager::delete_course($item['code']);
            // The normal procedure moves the course directory to archive, so
            // delete it there as well
            echo('rm -rf '.__DIR__.'/../../archive/'.$item['directory'].'_*').PHP_EOL;
            exec('rm -rf '.__DIR__.'/../../archive/'.$item['directory'].'_*');
            // The normal procedure also created a database dump, but it is
            // stored in the course folder, so no issue there...       
            if (substr($size, 0, 4) == '160K') {
                $total += 160;
            }
            if (substr($size, 0, 4) == '9,1M') {
                $total += 9100;
            }
            $count ++;
            if ($count%100 == 0) {
                echo '### Until now: '.$total.'K in '.$count.' courses'.PHP_EOL;
            }
        }
    }
}
echo $total.'K in '.$count.' courses'.PHP_EOL;
