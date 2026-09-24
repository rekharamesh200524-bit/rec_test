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
     * @param int $targetRoleId Optional role ID to target
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

        return $this->db->insert('IHrNotifications', $data);
    }

    /**
     * Cache for resolved role group IDs to avoid duplicate queries in the same request.
     * 
     * @var array
     */
    protected $roleGroupCache = [];

    /**
     * Dynamically resolve all role IDs belonging to the same role group as the given role ID.
     *
     * In this project, executive/administrative management roles (centrally defined in EmpRoles
     * with names such as 'Management', 'Admin', 'Super Admin') form a shared role group.
     * Users belonging to this group can view notifications targeted to their own role ID as well as
     * any other role IDs mapped to that same group (including historical/mapped target role IDs).
     *
     * @param int|string $roleId
     * @return array Array of role IDs in the same group
     */
    protected function getSharedRoleGroupIds($roleId) {
        if (empty($roleId)) {
            return [];
        }

        $roleIdInt = (int)$roleId;
        if (isset($this->roleGroupCache[$roleIdInt])) {
            return $this->roleGroupCache[$roleIdInt];
        }

        // Centrally defined role names representing executive / administrative management
        $managementRoleNames = ['management', 'admin', 'super admin'];

        // 1. Fetch all roles from central EmpRoles table
        $allRoles = $this->db->select('Erid, RoleName')
                             ->from('EmpRoles')
                             ->get()
                             ->result_array();

        $currentRoleName = '';
        $groupRoleIds = [];
        $otherRoleIds = [];

        foreach ($allRoles as $r) {
            $rId = (int)$r['Erid'];
            $rName = strtolower(trim(!empty($r['RoleName']) ? $r['RoleName'] : ''));
            if ($rId === $roleIdInt) {
                $currentRoleName = $rName;
            }
            if (in_array($rName, $managementRoleNames, true)) {
                $groupRoleIds[] = $rId;
            } else {
                $otherRoleIds[] = $rId;
            }
        }

        // 2. Historical or mapped target role IDs in IHrNotifications that do not belong to other functional roles
        $mgmtTargetRoleIds = [];
        $notifRoleRows = $this->db->select('DISTINCT(TargetRoleId) as TargetRoleId')
                                  ->from('IHrNotifications')
                                  ->where('TargetRoleId IS NOT NULL', null, false)
                                  ->get()
                                  ->result_array();
        foreach ($notifRoleRows as $row) {
            $trId = (int)$row['TargetRoleId'];
            if ($trId > 0 && !in_array($trId, $otherRoleIds, true)) {
                $mgmtTargetRoleIds[] = $trId;
            }
        }

        // Determine if the current roleId belongs to the management group
        $isManagementGroup = in_array($currentRoleName, $managementRoleNames, true)
                          || in_array($roleIdInt, $groupRoleIds, true)
                          || in_array($roleIdInt, $mgmtTargetRoleIds, true);

        if ($isManagementGroup) {
            $resolvedGroup = array_values(array_unique(array_merge($groupRoleIds, $mgmtTargetRoleIds)));
            $this->roleGroupCache[$roleIdInt] = $resolvedGroup;
            return $resolvedGroup;
        }

        $this->roleGroupCache[$roleIdInt] = [$roleIdInt];
        return [$roleIdInt];
    }

    /**
     * Fetch unread notifications for a specific user and their role
     * 
     * @param int $userId
     * @param int $roleId
     * @return array
     */
    public function getUnreadNotifications($userId, $roleId) {
        $groupRoleIds = $roleId ? $this->getSharedRoleGroupIds($roleId) : [];

        $this->db->select('*');
        $this->db->from('IHrNotifications');
        $this->db->where('Status', 'Unread');
        
        $this->db->group_start();
        $this->db->where('TargetUserId', $userId);
        
        if (!empty($groupRoleIds)) {
            $this->db->or_where_in('TargetRoleId', $groupRoleIds);
        } elseif ($roleId) {
            $this->db->or_where('TargetRoleId', $roleId);
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
        $groupRoleIds = $roleId ? $this->getSharedRoleGroupIds($roleId) : [];

        $this->db->where('Status', 'Unread');
        
        $this->db->group_start();
        $this->db->where('TargetUserId', $userId);
        if (!empty($groupRoleIds)) {
            $this->db->or_where_in('TargetRoleId', $groupRoleIds);
        } elseif ($roleId) {
            $this->db->or_where('TargetRoleId', $roleId);
        }
        $this->db->group_end();
        
        $this->db->update('IHrNotifications', array('Status' => 'Read'));
        return $this->db->affected_rows() > 0;
    }
}
?>
