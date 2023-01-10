<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller {

    public function index() {
        return self::getContacts(auth()->user());
    }

    public static function getContacts($user) {
        return $user->contacts()->limit(50)->get()->makeHidden('pivot')->sortBy('Name');
    }
}
