<?php
/**
 *  This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 */

require_once("../../../../../../../../../../vendor/composer/vendor/autoload.php");

ilInitialisation::initILIAS();

header('Content-type: application/json; charset=utf-8');

global $DIC;

$question_id = (int) ($_REQUEST['question_id'] ?? 0);
$active_id = (int) ($_REQUEST['active_id'] ?? 0);
$pass = (int) ($_REQUEST['pass'] ?? 0);
$user_id = (int) ($_REQUEST['user_id'] ?? 0);
$hint_index = (int) ($_REQUEST['hint_index'] ?? 0);
$hint_title = (string) ($_REQUEST['hint_title'] ?? '');
$event_type = (string) ($_REQUEST['event_type'] ?? '');
$current_user_id = (int) $DIC->user()->getId();
$current_pass = ilObjTest::_getPass($active_id);

$valid_event_types = ['open', 'close'];

if (
	$question_id <= 0 ||
	$active_id <= 0 ||
	$pass < 0 ||
	$hint_index <= 0 ||
	$current_user_id <= 0 ||
	$user_id !== $current_user_id ||
	$current_pass !== $pass ||
	!in_array($event_type, $valid_event_types, true)
) {
	http_response_code(400);
	echo json_encode(['status' => 'ignored']);
	exit;
}

require_once ILIAS_ABSOLUTE_PATH . '/public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionDB.php';

assStackQuestionDB::_storeHintInteraction(
	$question_id,
	$active_id,
	$pass,
	$current_user_id,
	$hint_index,
	$hint_title,
	$event_type
);

echo json_encode(['status' => 'ok']);
exit;
