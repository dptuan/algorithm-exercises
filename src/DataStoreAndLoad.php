<?php

declare(strict_types=1);

namespace TuanDo\SkeletonPhp;

final class DataStoreAndLoad
{
    public function store(array $data = []): string
    {
        return implode('\n', array_map(function ($line) {
            foreach ($line as $key => $value) {
                $line[$key] = "{$key}={$value}";
            }

            return implode(';', $line);
        }, $data));
    }

    function load(string $data): array
    {
        $lines = array_filter(explode('\n', $data));

        $arr = [];
        foreach ($lines as $i => $line) {
            $arr[$i] = [];
            $items = explode(';', $line);
            foreach ($items as $item) {
                [$key, $value] = explode('=', $item);
                $arr[$i][$key] = $value;
            }
        }

        return $arr;
    }
}