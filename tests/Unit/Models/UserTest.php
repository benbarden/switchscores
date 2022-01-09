<?php

namespace Tests\Unit\Models;

use App\Models\UserRole;
use App\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRoleCategoryManager()
    {
        $categoryManager = new User(
            [
                'display_name' => 'Barry',
                'email' => 'barry@switchscores.com',
                'is_staff' => '1'
            ]
        );
        $categoryManager->addRole(UserRole::ROLE_CATEGORY_MANAGER);

        $this->assertTrue($categoryManager->hasRole(UserRole::ROLE_CATEGORY_MANAGER));
    }
}
