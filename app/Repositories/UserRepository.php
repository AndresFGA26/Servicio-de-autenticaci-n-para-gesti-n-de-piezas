<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmail(String $email): ?User
    {
        return User::where('email',$email)->first();

    }



}
