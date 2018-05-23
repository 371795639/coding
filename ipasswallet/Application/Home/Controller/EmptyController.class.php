<?php

namespace Home\Controller;

class EmptyController extends CommonController
{
    //空方法
    public function _empty()
    {
        $this->display('Public:error404');
    }
}
