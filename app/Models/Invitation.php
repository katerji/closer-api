<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model {
     use HasFactory;

     protected $fillable = [
         'user_id',
         'contact_user_id'
     ];

     public function sender() {
         return $this->belongsTo(User::class, 'user_id');
     }
     public function receiver() {
         return $this->belongsTo(User::class, 'contact_user_id');
     }
}
