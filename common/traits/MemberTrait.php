<?php

namespace common\traits;

use yii\db\ActiveRecord;

trait MemberTrait
{
    public function addMember()
    {

    }

    /**
     * Attach role to group/project members
     * @param array $users
     * @param ActiveRecord[] $usersModel
     * @return array
     */
    public function buildMembersWithRoles(array $users, $usersModel)
    {
        if (empty($usersModel)) {
            return [];
        }

        return array_map(function ($item) use ($users) {
            return [
                'id' => $item->user_id,
                'name' => $users[$item->user_id]['username'],
                'role' => $item->role,
            ];
        }, $usersModel);
    }
}