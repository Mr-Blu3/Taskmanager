<?php

include_Once __DIR__ . '/../functions.php';

registerErrorHandler();

$response = array();

switch($_REQUEST['service']) {
	case 'load-item':
		$response = getItem(@$_REQUEST['board'], $_REQUEST['id']);
		break;
	case 'load-items':
		$response = getItems(@$_REQUEST['board']);
		break;
	case 'move-item':
		moveItem(@$_REQUEST['board'], $_REQUEST['project'], $_REQUEST['id'], $_REQUEST['to_ids'], @$_REQUEST['from_ids']);
		break;
	case 'remove-item':
		removeItem(@$_REQUEST['board'], $_REQUEST['id']);
		break;
	case 'save-item':
		$item = array(
			'_id' => (@$_REQUEST['id']? (String) $_REQUEST['id'] : md5(rand(1,100000) . date("ymdhis"))),
			'project' => (String) @$_REQUEST['project'],
			'existing_project' => (String) @$_REQUEST['existing_project'], 
			'board' => preg_replace("/[^ \-\w\(\)]+/", "", $_REQUEST['board']),
			'title' => (String) $_REQUEST['title'],
			'prio' => (int) $_REQUEST['prio'],
			'days' => (int) $_REQUEST['days'],
			'deadline' => preg_replace("/[^ \-\d]+/", "", $_REQUEST['deadline']),
		);
		save($item['board'], $item);
		break;
	default:
		$response = array("error" => "Unknown Service");
}

header('Content-Type: application/json');
echo json_encode($response);
