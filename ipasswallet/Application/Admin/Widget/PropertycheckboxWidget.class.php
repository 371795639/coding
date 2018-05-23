<?php

namespace Admin\Widget;

use Think\Controller;

class PropertycheckboxWidget extends Controller
{
    public function show($params)
    {
        $arr = unserialize($params);
        if (in_array(1, $arr)) {
            $pp['property1'] = 'checked';
        }
        if (in_array(2, $arr)) {
            $pp['property2'] = 'checked';
        }
        if (in_array(3, $arr)) {
            $pp['property3'] = 'checked';
        }
        if (in_array(4, $arr)) {
            $pp['property4'] = 'checked';
        }
        if (in_array(5, $arr)) {
            $pp['property5'] = 'checked';
        }
        $this->assign($pp);
        $this->display('Widget:propertycheckbox');
    }
}
