<?php

namespace App\Models;

use App\Lib\Model;
use App\Traits\HasApiTokens;

class User extends Model {
    use HasApiTokens;

    public string $table = "users";
    public array $hidden = ["password"];

    public function roles() {
        return $this->belongsToMany(Role::class, "user_roles", "user_id", "role_id");
    }
}