<?php

function comb($m, $a) {
    if (!$m) {
        yield [];
        return;
    }
    if (!$a) {
        return;
    }
    $h = $a[0];
    $t = array_slice($a, 1);
    foreach (comb($m - 1, $t) as $c)
        yield array_merge([$h], $c);
    foreach (comb($m, $t) as $c)
        yield $c;
}

function permute($items, $perms = array()) {
    if (empty($items)) {
        $return = array($perms);
    } else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $return = array_merge($return, permute($newitems, $newperms));
        }
    }
    return $return;
}

function flattenArray(array $array) {
    $ret_array = array();
    foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
        $ret_array[] = $value;
    }
    return $ret_array;
}

$a = [1, 2, 3, 4, 5, 6, 7, 8, 9];
$count = count($a);

$first = [];
$second = [];
$all = [];

foreach (range(1, $count) as $n) {
    $combinations = comb($n, $a);
    foreach ($combinations as $combination) {
        $permutations = permute($combination);
        $first[] = $permutations;
    }
}

foreach ($first as $used) {
    $free = array_merge(array_diff($a, $used[0]));
    foreach (range(1, count($free)) as $n) {
        $combinations = comb($n, $free);

        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $second[] = [$used, $permutations];
        }
    }
}
//die(json_encode($second));

foreach ($second as $used) {

    $flattenUsedValues = array_merge(flattenArray($used[0]), flattenArray($used[1]));
    $free = array_merge(array_diff($a, $flattenUsedValues));
//    die(json_encode($permutations ));

    if (count($free) > 0) {
        $combinations = comb(count($free), $free);
        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $all[] = [$used[0], $used[1], $permutations];
        }
    } else {
        $all[] = [$used[0], $used[1], []];
    }
}

//die(json_encode($all));
echo 'ok';