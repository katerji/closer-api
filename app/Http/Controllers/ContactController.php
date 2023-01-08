<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller {

    public function index() {
        return auth()->user()->contacts()->get()->makeHidden('pivot')->sortBy('Name');
    }
}
