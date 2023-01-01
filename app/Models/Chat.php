<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    public function users() {
        return $this->belongsToMany(User::CLASS, 'user_chat')->withTimestamps();
    }

    public function messages() {
        return $this->hasMany(Message::CLASS);
    }

    public function containsUser($userId) {
        $chatUsers = $this->users()->get();
        foreach ($chatUsers as $user) {
            if ($user->id == $userId) {
                return true;
            }
        }
        return false;
    }
}
