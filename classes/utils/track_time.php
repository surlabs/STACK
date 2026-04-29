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
$duration_ms = (int) ($_REQUEST['duration_ms'] ?? 0);
$current_user_id = (int) $DIC->user()->getId();
$current_pass = ilObjTest::_getPass($active_id);

if (
	$question_id <= 0 ||
	$active_id <= 0 ||
	$pass < 0 ||
	$current_user_id <= 0 ||
	$user_id !== $current_user_id ||
	$current_pass !== $pass ||
	$duration_ms <= 0 ||
	$duration_ms > 3600000
) {
	http_response_code(400);
	echo json_encode(['status' => 'ignored']);
	exit;
}

require_once ILIAS_ABSOLUTE_PATH . '/public/Customizing/global/plugins/Modules/TestQuestionPool/Questions/assStackQuestion/classes/utils/class.assStackQuestionDB.php';

assStackQuestionDB::_storeQuestionTime(
	$question_id,
	$active_id,
	$pass,
	$current_user_id,
	$duration_ms
);

echo json_encode(['status' => 'ok']);
exit;
