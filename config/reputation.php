<?php

/*各种积分的要求*/

return [

    'require'  =>  [
        'add_question'  =>  0,
        'add_answer'    =>  0,
        'add_comment'   =>  0,
        'add_share'     =>  10,
        'add_post'      =>  0,
        'add_vote'      =>  0,  ///如果小于10分则不允许投票
        'join_club'     =>  0,  ///如果小于0则这个账户都不允许做任何的join的操作
        'add_chat_message'  =>  0,
    ],

    //只影响单个club的积分
    'reward'    =>  [
        'join_club'          =>  20,
        
        'add_good_question'  =>  10,
        'add_good_answer'    =>  10,
        'add_bad_question'   =>  -5,
        'add_bad_answer'     =>  -5,
        'add_bad_link'       =>  -10,

        'add_unfriendly_comment'    =>  -10,    ///添加不友好的comment
        'set_unfriendly_user'       =>  -20,    ///设置为不友好的用户
    ],

    //全局的reputation的奖励和惩罚
    'reward_global' =>  [
        'set_unfriendly_user'       =>  -20,    ///设置为不友好的用户
    ]

];
