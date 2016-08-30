<?php

include '../functions.php';

$items = getDb()->items->find();
foreach ($items as $item) {
	$item['board'] = $item['group'];
	getDb()->items->save($item);
	
}