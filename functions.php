<?php

require_once(__DIR__ . '/vendor/autoload.php');

function registerErrorHandler()
{
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    $whoops->register();
}

/**
 * get the MongoDB connected object
 *
 * @return \MongoDB\Database
 */
function getDb()
{
    $connection = new \MongoDB\Client(
        sprintf(
            'mongodb://%s:%d/%s', 
            'localhost', '27017',  
            'taskmanager'
        ));

    return $connection->taskmanager;
}

/**
 * Save Item
 *
 * @param array $item
 */
function save($board, $item)
{
    $oDatabase = getDb();
    
    $oDatabase->items->updateOne(
        ['_id' => $item['_id']], 
        ['$set' => $item],
        ['upsert' => true]
    );

    $existingItem = getItem($board, $item['_id']);

    if ($existingItem['project'] != $item['project']) {
        $oDatabase->items->updateOne(
            array('board' => $existingItem['board'], 'project' => $existingItem['project']),
            array('$set' => array('project' => $item['project']))
        );
    }
    ensureOrder($board, $item['project']);
}

/**
 * Make sure the order is correct
 */

function ensureOrder($board, $project)
{
    $i = 1;
    $aBatch = [];
    $oDatabase = getDb();
    
    $items = $oDatabase->items->find(
        ['board' => $board, 'project' => $project], 
        ['sort' => ['order' => 1]]
    );

    foreach ($items as $currentItem) {
        $aBatch[] = [
            'updateOne' => [
                ['board' => $board, '_id' => $currentItem['_id']],
                [
                    '$set' => [
                        'order' => $i
                    ]
                ]
            ]
        ];
        $i++;
    }
    $oDatabase->items->bulkWrite($aBatch);
}

/**
 * Get Items
 *
 * @return array
 */

function getItems($board)
{
    $items = getDb()->items->find(['board' => $board]);
    $build = array();
    foreach ($items as $item) {
        if (!isset($build[$item['project']]))
            $build[$item['project']] = array();

        $build[$item['project']][] = $item;
    }

    ksort($build);

    return $build;
}

/**
 * Save a new order to the items
 * @param string $board
 * @param array $ids
 */
function moveItem($board, $project, $sChangedId, $aToIds, $fromIds)
{
    $oDatabase = getDb();
    if (is_array($fromIds)) {
        $fromIds = array_filter($fromIds);
    } else {
        $fromIds = array();
    }
    $updates = array();
    $aBatch = [];$aBatch2= [];$aBatch3= [];
    $y = 1;
    
    for ($i=0; $i < count($aToIds); $i++) { 
        $items = $oDatabase->items->find(
            ['board' => $board, 'project' => $project, '_id' => $aToIds[$i]], 
            ['sort' => ['order' => 1]]
        );
        
        $aBatch[] = [
            'updateOne' => [
                ['board' => $board, '_id' => $aToIds[$i]],
                [
                    '$set' => [
                        'order' => $i
                    ]
                ]
            ]
        ];
    }
    for ($i=0; $i < count($fromIds); $i++) { 
        $items = $oDatabase->items->find(
            ['board' => $board, 'project' => $project, '_id' => $fromIds[$i]], 
            ['sort' => ['order' => 1]]
        );
        
        $aBatch2[] = [
            'updateOne' => [
                ['board' => $board, '_id' => $fromIds[$i]],
                [
                    '$set' => [
                        'order' => $i
                    ]
                ]
            ]
        ];
    }
    $oDatabase->items->bulkWrite($aBatch2);
    
    $aBatch3[] = [
            'updateOne' => [
                ['board' => $board, '_id' => $sChangedId],
                [
                    '$set' => [
                        'project' => $project
                    ]
                ]
            ]
        ];
    $oDatabase->items->bulkWrite($aBatch3);
}

/**
 * Get items from project
 *
 * @param string $project
 * @return array
 */
function getProjectItems($project, $board)
{
    return getDb()->items->find(['project' => $project, 'board' => $board], ['sort' => ['order' => 1]]);
}

/**
 * Get Item
 *
 * @param string $id
 * @param string $board
 * @return array
 */
function getItem($board, $id)
{
    return getDb()->items->findOne(['_id' => $id, 'board' => $board]);
}

/**
 * Remove one item
 *
 * @param string $id
 * @return boolean
 */

function removeItem($board, $id)
{
    return getDb()->items->deleteOne(array('_id' => $id, 'board' => $board));
}
