<?php

function admin_to_role($uid)
{
    $AUTH = new \Think\Auth();
    $list = $AUTH->getGroups($uid);
    //dump($list);
    if ($list) {
        return $list[0]['title'];
    } else {
        return '<font color=red>尚未分配</font>';
    }
}

function group_to_rule($group_id, $module)
{
    $oop = M('authGroup');
    $list = $oop->find($group_id);
    $arr = explode(',', $list['rules']);
    $con['module'] = $module;
    $list1 = M('authRule')->where($con)->select();
    foreach ($list1 as $key => $value) {
        if (in_array($value['id'], $arr)) {
            $list1[$key]['checked'] = 'checked';
        } else {
            $list1[$key]['checked'] = '';
        }
    }

    return $list1;
}

//检测PHP设置参数
function getcon($varName)
{
    switch ($res = get_cfg_var($varName)) {
    case 0:
        return '<font color="red">×</font>';
        break;
    case 1:
        return '<font color="green">√</font>';
        break;
    default:
        return $res;
        break;
    }
}

//PHP组件支持检测
function isfun($funName)
{
    return (false !== function_exists($funName)) ? '<font color="green">√</font>' : '<font color="red">×</font>';
}

//整数运算能力测试
function test_int()
{
    $timeStart = gettimeofday();
    for ($i = 0; $i < 500000; ++$i) {
        $t = 1 + 1;
    }
    $timeEnd = gettimeofday();
    $time = ($timeEnd['usec'] - $timeStart['usec']) / 1000000 + $timeEnd['sec'] - $timeStart['sec'];
    $time = round($time * 1000, 2).'毫秒';

    return $time;
}

//浮点运算能力测试
function test_float()
{
    $t = pi();
    $timeStart = gettimeofday();
    for ($i = 0; $i < 500000; ++$i) {
        sqrt($t);
    }
    $timeEnd = gettimeofday();
    $time = ($timeEnd['usec'] - $timeStart['usec']) / 1000000 + $timeEnd['sec'] - $timeStart['sec'];
    $time = round($time * 1000, 2).'毫秒';

    return $time;
}
