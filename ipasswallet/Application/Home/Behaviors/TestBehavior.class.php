<?php
namespace Home\Behaviors;
class TestBehavior extends \Think\Behavior{
    //行为执行入口
    public function run(&$param){
		echo "555";
		C('DEFAULT_LANG','en-us');
    }
}
?>