<?php

namespace App\Traits;

use App\Lib\JWTCodec;

trait HasApiTokens {
    public function createToken(): string {
        $codec = new JWTCodec();
        $token = $codec->encode([
            "user_id" => $this->id,
            "exp" => 60 * 60 * 24
        ]);

        return $token;
    }
}