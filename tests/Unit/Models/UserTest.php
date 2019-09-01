<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\UserRole;

class UserTest extends TestCase
{
    public function testRoleCategoryManager()
    {
        $categoryManager = new User(
            [
                'display_name' => 'Adam',
                'email' => 'adam@worldofswitch.com',
                'is_staff' => '1'
            ]
        );
        $categoryManager->addRole(UserRole::ROLE_CATEGORY_MANAGER);

        $this->assertTrue($categoryManager->hasRole(UserRole::ROLE_CATEGORY_MANAGER));
    }
}
