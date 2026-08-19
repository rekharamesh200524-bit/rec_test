<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Add a new notification
     * 
     * @param string $title Short title of the notification
     * @param string $message Detailed message
     * @param string $type Notification type (info, success, warning, danger)
     * @param int $targetUserId Optional specific user ID to target
     * @param int $targetRoleId Optional role ID to target (e.g., 1 or 2 for HR)
     * @return bool
     */
    public function addNotification($title, $message, $type = 'info', $targetUserId = null, $targetRoleId = null) {
        $data = array(
            'Title' => $title,
            'Message' => $message,
            'Type' => $type,
            'Status' => 'Unread',
            'CreatedAt' => date('Y-m-d H:i:s')
        );

        if ($targetUserId !== null) {
            $data['TargetUserId'] = $targetUserId;
        }

        if ($targetRoleId !== null) {
            $data['TargetRoleId'] = $targetRoleId;
        }

        return $this->db->insert('IHRNotifications', $data);
    }

    /**
     * Fetch unread notifications for a specific user and their role
     * 
     * @param int $userId
     * @param int $roleId
     * @return array
     */
    public function getUnreadNotifications($userId, $roleId) {
        $this->db->select('*');
        $this->db->from('IHrNotifications');
        $this->db->where('Status', 'Unread');
        
        $this->db->group_start();
        $this->db->where('TargetUserId', $userId);
        
        if ($roleId) {
            $this->db->or_where('TargetRoleId', $roleId);
            if ($roleId == 1 || $roleId == 2) {
                $this->db->or_where_in('TargetRoleId', [1, 2]);
            }
        }
        $this->db->group_end();
        
        $this->db->order_by('CreatedAt', 'DESC');
        $this->db->limit(20); 
        
        return $this->db->get()->result();
    }

   
    public function markAsRead($notificationId, $userId) {
        $this->db->where('NotificationId', $notificationId);
        $this->db->update('IHrNotifications', array('Status' => 'Read'));
        return $this->db->affected_rows() > 0;
    }

   
    public function markAllAsRead($userId, $roleId) {
        $this->db->where('Status', 'Unread');
        
        $this->db->group_start();
        $this->db->where('TargetUserId', $userId);
        if ($roleId) {
            $this->db->or_where('TargetRoleId', $roleId);
            if ($roleId == 1 || $roleId == 2) {
                $this->db->or_where_in('TargetRoleId', [1, 2]);
            }
        }
        $this->db->group_end();
        
        $this->db->update('IHrNotifications', array('Status' => 'Read'));
        return $this->db->affected_rows() > 0;
    }
}
?>
