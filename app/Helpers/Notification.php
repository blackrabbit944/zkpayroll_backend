<?php
namespace App\Helpers;


use Illuminate\Support\Facades\Log;


use App\Models\Notification as NotificationModal;
/**
 * 
 */
class Notification
{


    static public function getUnreadCount($user_id) {
        return NotificationModal::where([
            'to_user_id'=>$user_id,
            'read_time'=>0
        ])->count();
    }

    static public function getUnreadCountByGroup($user_id) {

        $data = NotificationModal::where([
            'to_user_id'=>$user_id,
            'read_time'=>0
        ])->get();

        $grouped_data = $data->groupBy(['to_item_type','to_item_id','notify_type']);

        $result = [];
        foreach($grouped_data as $item_name => $v1) {
            foreach($v1 as $item_id => $noti_map) {
                foreach($noti_map as $notify_type => $noti_list) {
                    $r1 = [
                        'item_type' => $item_name,
                        'notification_list' => $noti_list,
                        'notify_type' => $notify_type
                    ];
                    $result[] = $r1;                  
                }
            }
        }

        return count($result);
    }

}