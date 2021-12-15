<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

// 获取用户拥有的勋章
function getMemberMedals($uid){
    return DB::fetch_all('SELECT * FROM '.DB::table('common_member_medal').' where uid='.$uid);
}

// 获取论坛中所有的勋章
function getForumMedals(){
    return DB::fetch_all('SELECT * FROM '. DB::table('forum_medal'));
}

// 将数据库查询结果转换为id=>name对应的数组
function getMedalNamesByForumMedals($forumMedals){
    $medalNames = array();
    foreach ($forumMedals as $medal)
        $medalNames[$medal['medalid']]=$medal['name'];
    return $medalNames;
}

// 获取用户正在佩戴的勋章Id
function getMemberWearingMedalIds($uid){
    $memberField = DB::fetch_first('SELECT * FROM '.DB::table('common_member_field_forum').' where uid='.$uid);
    $memberMedalStr = $memberField['medals'];
    $memberMedalIds = explode("\t", $memberMedalStr);
    return $memberMedalIds;
}

// 将勋章列表转换为Id
function getMedalIdsByMedals($medals){
    $medalIds = array();
    foreach ($medals as $medal)
        $medalIds[]=$medal['medalid'];
    return $medalIds;
}

// 更新用户佩戴中的勋章
function updateMemberWearingMedalsByUid($uid, $medals){
    $medalStr = implode("\t", $medals);
    return DB::update('common_member_field_forum', array('medals'=>$medalStr), array('uid'=>$uid));
}

$forumMedals = getForumMedals();
$memberMedals = getMemberMedals($_G['uid']);
$memberMedalIds = getMedalIdsByMedals($memberMedals);
$medalNames = getMedalNamesByForumMedals($forumMedals);
$memberWearingMedalIds = getMemberWearingMedalIds($_G['uid']);
$memberWearingMedalIdsStr = implode(',', $memberWearingMedalIds);

$displayMedals = array();
foreach ($forumMedals as $forumMedal){
    $displayMedals[$forumMedal['medalid']]['medalid']=$forumMedal['medalid'];
    $displayMedals[$forumMedal['medalid']]['name']=$forumMedal['name'];
    $displayMedals[$forumMedal['medalid']]['have']=in_array($forumMedal['medalid'], $memberMedalIds);
    $displayMedals[$forumMedal['medalid']]['wearing']=in_array($forumMedal['medalid'], $memberWearingMedalIds);
}

if($_GET['pluginop'] == 'wear' && submitcheck('wearmedals')) {
    $wearMedalIds = explode('|', $_POST['medalIds']);
    $verified = true;
    foreach ($wearMedalIds as $medalId){
        if(!$displayMedals[$medalId]['have']){
            $verified = false;
            showmessage('勋章数据错误!', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
        }
    }
    if($verified)
        if(updateMemberWearingMedalsByUid($_G['uid'], $wearMedalIds))
            showmessage('勋章佩戴成功!', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
        else
            showmessage('数据库错误...', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
}
?>