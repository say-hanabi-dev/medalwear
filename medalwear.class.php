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
        global $_G;
        $memberMedals = DB::fetch_all('SELECT * FROM '.DB::table('common_member_medal').' where uid='.$_G['uid']);
        $memberMedalIds = [];
        foreach ($memberMedals as $memberMedal)
            $memberMedalIds[] = $memberMedal['medalid'];
        $memberMedalIdsStr = implode(',', $memberMedalIds);
        $jsvar = "memberMedalIds = [".$memberMedalIdsStr."];";
        $jsfunc = <<<EOF
            function changeMedalText(item, index){
                medal = document.getElementById("medal_"+item);
                li = medal.parentElement;
                p = li.lastElementChild;
                p.innerHTML="已拥有";
            }
            
            memberMedalIds.forEach(changeMedalText);
EOF;
        $jscode = '';
        $jscode.= '<script>'.$jsvar.$jsfunc."</script>";
        return '<li><a href="home.php?mod=spacecp&ac=plugin&id=medalwear:memcp">勋章佩戴</a></li>'.$jscode;
    }
}
