<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller {
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function create(Request $request, $contactUserId) {
        $user = auth()->user();
        User::findOrFail($contactUserId);
        $user->invitations()->attach($contactUserId);
        return response()->json(['success' => true]);
    }

    public function delete(Request $request, $contactUserId) {
        $user = auth()->user();
        User::findOrFail($contactUserId);
        $user->invitations()->detach($contactUserId);
        return response()->json(['success' => true]);
    }

    public function rejectInvitation(Request $request, $inviterUserId) {
        $inviter = User::findOrFail($inviterUserId);
        $inviter->invitations()->detach(auth()->user()->id);
        return response()->json(['success' => true]);
    }

    public function acceptInvitation(Request $request, $inviterUserId) {
        $contactName = $request->input('contact_name');
        $inviter = User::findOrFail($inviterUserId);
        $user = auth()->user();
        $invitationExists = DB::table('invitations')
            ->whereUserId($inviterUserId)
            ->whereContactUserId($user->id)
            ->count() > 0;
        if (!$invitationExists) {
            return response()->json(['error' => 'invitation not found']);
        }
        $inviter->contacts()->attach($user->id, ['contact_name' => $user->name]);
        $user->contacts()->attach($inviterUserId, ['contact_name' => $contactName ?: $inviter->name]);
        return response()->json(['success' => true]);
    }
}
