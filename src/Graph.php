<?php

namespace TuanDo\SkeletonPhp;

final class Graph
{
    public string $fromEdge;

    public function __construct($fromEdge)
    {
        $this->fromEdge = $fromEdge;
    }

    public function findOptimalPath(array $verticles = [], $edges = [], $fromEdge = '', $path = '', $weight = 0)
    {
        $path .= $fromEdge;
        $weight += $verticles[$fromEdge] ?? 0;

        $nextEdges = $edges[$fromEdge] ?? [];

        if (empty($nextEdges)) {
            return [$path, $weight];
        }

        $bestWeight = $weight;
        $bestPath = $path;

        foreach ($nextEdges as $nextEdge) {
            if ($nextEdge === $this->fromEdge) {
                return [$bestPath, $bestWeight];
            }
            [$nextPath, $nextWeight] = $this->findOptimalPath(
                $verticles, $edges, $nextEdge, $path, $weight
            );

            if ($bestWeight < $nextWeight) {
                $bestWeight = $nextWeight;
                $bestPath = $nextPath;
            }
        }

        return [$bestPath, $bestWeight];
    }
}

$graph = new Graph('A');

$verticles = [
    'A' => 1,
    'B' => 2,
    'C' => 2,
];
$edges = [
    'A' => ['B', 'C'],
    'B' => ['C'],
];


var_dump($graph->findOptimalPath($verticles, $edges, 'A', '', 0));