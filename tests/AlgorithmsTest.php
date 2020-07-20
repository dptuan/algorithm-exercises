<?php

use TuanDo\SkeletonPhp\DataStoreAndLoad;
use TuanDo\SkeletonPhp\Graph;

test('store_data', function () {
    $arr = [
        [
            'key1' => 'value1',
            'key2' => 'value2',
        ],
        [
            'keyA' => 'valueA',
        ],
    ];

    assertEquals('key1=value1;key2=value2\nkeyA=valueA',
        (new DataStoreAndLoad)->store($arr));
});

test('load_data', function () {
    $str1 = 'key1=value1;key2=value2\nkeyA=valueA';
    assertEquals([
        [
            'key1' => 'value1',
            'key2' => 'value2',
        ],
        [
            'keyA' => 'valueA',
        ],
    ], (new DataStoreAndLoad)->load($str1));

    $str2 = 'key1=value1;key2=value2\nkeyA=valueA\n';
    assertEquals([
        [
            'key1' => 'value1',
            'key2' => 'value2',
        ],
        [
            'keyA' => 'valueA',
        ],
    ], (new DataStoreAndLoad)->load($str2));
});

test('find_optinal_path_in_normal_case', function () {
    $verticles = [
        'A' => 1,
        'B' => 2,
        'C' => 2,
    ];
    $edges = [
        'A' => ['B', 'C'],
        'B' => ['C'],
    ];
    $graph = new Graph('A');
    $values = $graph->findOptimalPath($verticles, $edges, 'A', '', 0);
    $bestPath = $values[0];
    $bestWeight = $values[1];

    assertEquals('ABC', $bestPath);
    assertEquals(5, $bestWeight);
});

test('find_optinal_path_in_infinite_loop_case', function () {
    $verticles = [
        'A' => 1,
        'B' => 2,
        'C' => 2,
    ];
    $edges = [
        'A' => ['B'],
        'B' => ['C'],
        'C' => ['A'],
    ];
    $graph = new Graph('A');
    $values = $graph->findOptimalPath($verticles, $edges, 'A', '', 0);
    $bestPath = $values[0];
    $bestWeight = $values[1];

    assertEquals('ABC', $bestPath);
    assertEquals(5, $bestWeight);
});