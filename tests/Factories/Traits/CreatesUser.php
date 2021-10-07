<?php 

namespace Tests\Factories\Traits;

use App\Models\User;
use App\Models\UserProfile;


trait CreatesUser
{
    private function createUser(): User
    {
        $user = factory(User::class)->create();
        $user->profile()->save(factory(UserProfile::class)->make());

        return $user;
    }
}


?>