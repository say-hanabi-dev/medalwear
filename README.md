# MedalWear 勋章分类插件 For DiscuzX

可以让用户自己选择要佩戴展示的勋章。
即装即用，无需配置。
如需卸载并恢复用户勋章佩戴请自行将`pre_common_member_medal`表中的medalid项使用implode("\t", $medalidArr)转换为字符串并且存入`pre_common_member_field_forum`表中的`medals`