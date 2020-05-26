<?php
/* For license terms, see /license.txt */

/**
 * This script initiates a video conference session, calling the BigBlueButton API.
 */
require_once __DIR__.'/../../vendor/autoload.php';

$course_plugin = 'bbb'; //needed in order to load the plugin lang variables
require_once __DIR__.'/config.php';

$logInfo = [
    'tool' => 'Videoconference',
];
Event::registerLog($logInfo);

$tool_name = get_lang('Videoconference');
$tpl = new Template($tool_name);

$vmIsEnabled = false;
$host = '';
$salt = '';
$isGlobal = isset($_GET['global']) ? true : false;
$isGlobalPerUser = isset($_GET['user_id']) ? (int) $_GET['user_id'] : false;
$interface = isset($_GET['interface']) ? (int) $_GET['interface'] : 0;

$bbb = new bbb('', '', $isGlobal, $isGlobalPerUser);

$conferenceManager = $bbb->isConferenceManager();
if ($bbb->isGlobalConference()) {
    api_block_anonymous_users();
} else {
    api_protect_course_script(true);
}

if ($bbb->pluginEnabled) {
    if ($bbb->isServerConfigured()) {
        if ($bbb->isServerRunning()) {
            if (isset($_GET['launch']) && $_GET['launch'] == 1) {
                if (file_exists(__DIR__.'/config.vm.php')) {
                    $config = require __DIR__.'/config.vm.php';
                    $vmIsEnabled = true;
                    $host = '';
                    $salt = '';

                    require __DIR__.'/lib/vm/AbstractVM.php';
                    require __DIR__.'/lib/vm/VMInterface.php';
                    require __DIR__.'/lib/vm/DigitalOceanVM.php';
                    require __DIR__.'/lib/VM.php';

                    $vm = new VM($config);

                    if ($vm->isEnabled()) {
                        try {
                            $vm->resizeToMaxLimit();
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            exit;
                        }
                    }
                }

                $meetingParams = [];
                $meetingParams['meeting_name'] = $bbb->getCurrentVideoConferenceName();
                $meetingParams['interface'] = $interface;
                $url = null;
                if ($bbb->meetingExists($meetingParams['meeting_name'])) {
                    $joinUrl = $bbb->joinMeeting($meetingParams['meeting_name']);
                    if ($joinUrl) {
                        $url = $joinUrl;
                    }
                } else {
                    if ($bbb->isConferenceManager()) {
                        $url = $bbb->createMeeting($meetingParams);
                    }
                }

                $meetingInfo = $bbb->findMeetingByName($meetingParams['meeting_name']);
                if (!empty($meetingInfo) && $url) {
                    $bbb->saveParticipant($meetingInfo['id'], api_get_user_id(), $interface);
                    $bbb->redirectToBBB($url);
                } else {
                    Display::addFlash(
                        Display::return_message($bbb->plugin->get_lang('ThereIsNoVideoConferenceActive'))
                    );
                    $url = $bbb->getListingUrl();
                    header('Location: '.$url);
                    exit;
                }
            } else {
                $url = $bbb->getListingUrl();
                header('Location: '.$url);
                exit;
            }
        } else {
            $message = Display::return_message(get_lang('ServerIsNotRunning'), 'warning');
        }
    } else {
        $message = Display::return_message(get_lang('ServerIsNotConfigured'), 'warning');
    }
} else {
    $message = Display::return_message(get_lang('ServerIsNotConfigured'), 'warning');
}
$tpl->assign('message', $message);
$tpl->display_one_col_template();
