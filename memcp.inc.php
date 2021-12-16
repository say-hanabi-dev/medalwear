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

// 获取用户勋章Id|过期时间
function getMemberFieldForumMedals($uid){
    $memberField = DB::fetch_first('SELECT * FROM '.DB::table('common_member_field_forum').' where uid='.$uid);
    $memberMedalStr = $memberField['medals'];
    $memberMedalIds = explode("\t", $memberMedalStr);
    return $memberMedalIds;
}

// 获取用户正在佩戴的勋章Id
function getMemberWearingMedalIds($uid){
    $memberMedalIds = getMemberFieldForumMedals($uid);
    // 处理有有效期限制的勋章
    foreach($memberMedalIds as $key => $memberMedalId){
        if(strpos($memberMedalId, '|')){
            if(explode('|', $memberMedalId)[1] > 0){
                $memberMedalIds[$key] = explode('|', $memberMedalId)[0];
            }else{
                unset($memberMedalIds[$key]);
            }
        }
    }
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
    $res = DB::update('common_member_field_forum', array('medals'=>$medalStr), array('uid'=>$uid));
    C::t('common_member_field_forum')->clear_cache($uid);
    return $res;
}

// 获取勋章过期时间
function getMemberMedalExpiration($uid, $medalId){
    return intval(DB::fetch_first('SELECT * FROM '.DB::table('forum_medallog').' WHERE uid='.$uid.' AND medalid='.$medalId.' ORDER BY dateline DESC')['expiration']);
}

$forumMedals = getForumMedals();
$memberMedals = getMemberMedals($_G['uid']);
$memberMedalIds = getMedalIdsByMedals($memberMedals);
$medalNames = getMedalNamesByForumMedals($forumMedals);
$memberWearingMedalIds = getMemberWearingMedalIds($_G['uid']);
$memberWearingMedalIdsStr = implode(',', $memberWearingMedalIds);

$displayMedals = array();
foreach ($forumMedals as $forumMedal){
    $medalExpiration = getMemberMedalExpiration($_G['uid'], $forumMedal['medalid']);
    $notExprired = $medalExpiration > time() || $medalExpiration == 0;
    $displayMedals[$forumMedal['medalid']]['medalid']=$forumMedal['medalid'];
    $displayMedals[$forumMedal['medalid']]['name']=$forumMedal['name'];
    $displayMedals[$forumMedal['medalid']]['have']=in_array($forumMedal['medalid'], $memberMedalIds);
    $displayMedals[$forumMedal['medalid']]['wearing']=in_array($forumMedal['medalid'], $memberWearingMedalIds);
    $displayMedals[$forumMedal['medalid']]['expired']=!$notExprired;
    $displayMedals[$forumMedal['medalid']]['expiration']=$medalExpiration;
}

if($_GET['pluginop'] == 'wear' && submitcheck('wearmedals')) {
    if($_POST['medalIds'] == ""){
        $res = DB::query("UPDATE %t SET medals = '' WHERE uid = %d", array("common_member_field_forum", $_G['uid']));
        C::t('common_member_field_forum')->clear_cache($uid);
        showmessage('全都脱掉了啦...', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
    }
    $wearMedalIds = explode('`', $_POST['medalIds']);
    $verified = true;
    foreach ($wearMedalIds as $medalId){
        if(!$displayMedals[$medalId]['have']){
            $verified = false;
            showmessage('勋章数据错误!', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
        }
    }
    if($verified){
        foreach($wearMedalIds as $key => $wearMedalId){ //对限时勋章进行特殊处理
            if($displayMedals[$wearMedalId]['expiration']){
                $wearMedalIds[$key] = $wearMedalId.'|'.$displayMedals[$wearMedalId]['expiration'];
            }
        }
        if(updateMemberWearingMedalsByUid($_G['uid'], $wearMedalIds))
            showmessage('勋章佩戴成功!', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
        else
            showmessage('勋章佩戴状态没有变化或数据库错误...', 'home.php?mod=spacecp&ac=plugin&id=medalwear:memcp');
    }
}
?>