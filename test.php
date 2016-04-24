<?php

function comb($m, $a)
{
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

function comb2($arr, $temp_string, &$collect)
{
    if (count($temp_string) > 0)
        $collect [] = explode(" ", trim($temp_string));

    for ($i = 0; $i < count($arr); $i++) {
        $arrcopy = $arr;
        $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
        if (count($arrcopy) > 0) {
            comb2($arrcopy, $temp_string . " " . $elem[0], $collect);
        } else {
            $temp_string = $temp_string . " " . $elem[0];
            $collect [] = explode(" ", trim($temp_string));
        }
    }
}

function permute($items, $perms = array())
{
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

function flattenArray(array $array)
{
    $ret_array = array();
    foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
        $ret_array[] = $value;
    }
    return $ret_array;
}


$a = [1, 2, 3, 4, 5, 6, 7, 8];
$count = count($a);

$counter = 0;
$first = [];
$second = [];
$all = [];

//foreach (range(1, $count) as $j => $n) {
//$combinations = comb($n, $a);
$combinations = array();
comb2($a, null, $combinations);
die(print_r($combinations));

foreach ($combinations as $i => $combination) {
    $permutations = permute($combination);
    $first[] = $permutations;
    $counter += count($permutations);
}
//}

foreach ($first as $used) {
    $free = array_merge(array_diff($a, $used[0]));
    //foreach (range(1, count($free)) as $n) {
    //   $combinations = comb($n, $free);
    $combinations = array();
    comb2($free, "", $combinations);
    foreach ($combinations as $combination) {
        $permutations = permute($combination);
        $counter += count($permutations);
        $second[] = [$used, $permutations];
    }
    // }
}
//die(json_encode($second));

foreach ($second as $used) {

    $flattenUsedValues = array_merge(flattenArray($used[0]), flattenArray($used[1]));
    $free = array_merge(array_diff($a, $flattenUsedValues));

    if (count($free) > 0) {
        //$combinations = comb(count($free), $free);
        $combinations = array();
        comb2($free, "", $combinations);
        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $counter += count($permutations);
            $all[] = [$used[0], $used[1], $permutations];
        }
    } else {
        $all[] = [$used[0], $used[1], []];
    }
}

//die(json_encode($all));
echo $counter;