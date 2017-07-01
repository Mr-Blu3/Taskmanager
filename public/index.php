<?php
$board = @$_GET['board'] ? $_GET['board'] : @$_GET['group'];
if ($board) {
	$boardNameParts = explode('-', $board);
	$title = @$boardNameParts[1] ? $boardNameParts[1] : $boardNameParts[0];
} else {
	$title = 'Taskmanager';
}

$root = 'http://138.68.143.251/Taskmanager/public';	
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta http-equiv="refresh" content="3600">
		<title>Taskmanager - <?= ucfirst($title) ?></title>
		<link href='http://fonts.googleapis.com/css?family=Oleo+Script' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href=<?="$root/jquery/jquery-ui.min.css" ?>>
		<link rel="stylesheet" href=<?="$root/css/taskmanager.css" ?>>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src=<?= "$root/jquery/jquery-ui.min.js"?>></script>
		<script type="text/javascript">
			var currentBoard = '<?= $board ?>';
			var newBoardId = '<?= md5(rand(1, 10000) . microtime()) ?>';
		</script>
		<script src=<?= "$root/js/tasks.js"?>></script>
	</head>
	<body>
		<?php if ($board) : ?>
			<div id="header">
				<span id="new_item" class="button">Add New Item</span>
				<span id="logo">Tasks</span>
				<span id="new_board"> 
					<span id="new_board_start" class="button">Create New Board</span>
					<span  style="display: none;" id="new_board_continue">
						<input type="text" id="new_board_name" />
						<span id="new_board_create" class="button">Create</span>
					</span>
			</div>

			<div id="edit">
				<span class="label">Category</span>
				<select id="existing_project"></select> <input type="text" id="project" value="" placeholder="Other" />
				<span class="label">Title</span>
				<input type="text" id="title" value="" />
				<span class="label">Priority</span>
				<select id="prio">
					<option value="1">Critical</option>
					<option value="2">High</option>
					<option value="3">Normal</option>
					<option value="4">Future</option>
				</select>
				<span class="label">Days</span>
				<input type="text" id="days" value=""  placeholder="1" />
				<span class="label">Deadline</span>
				<input type="text" id="deadline" value=""  placeholder="XXXX-XX-XX" />
				<input type="hidden" id="id" value="" />

				<span class="button" style="display: none;" id="add">Add</span>
				<span class="button" style="display: none;" id="save">Save</span>
				<span class="button" style="display: none;" id="cancel">Cancel</span>
				<span class="button" style="display: none;" id="remove">Remove</span>
				<textarea id="description"></textarea>
			</div>

			<div id="container"></div>

		<?php else: ?>
			<div id="header">
				<span id="logo">Taskmanager</span>
			</div>
			<div id="start">
				<input type="text" id="new_board_name" value="" placeholder="Name" />
				<span id="new_board_create" class="button"> Create your first board</span>
			</div>
		<?php endif; ?>

	</body>
</html>
