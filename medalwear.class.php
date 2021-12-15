<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_medalwear
{
    public function plugin_medalwear()
    {
        global $_G;
    }
}

class plugin_medalwear_home extends plugin_medalwear
{
    function medal_nav_extra()
    {
        return '<li><a href="home.php?mod=spacecp&ac=plugin&id=medalwear:memcp">勋章佩戴</a></li>';
    }
}
