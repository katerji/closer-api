<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller {
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/'
        ]);
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ]));
        }
        $phone = $request->input('phone_number');
        $contactUser = User::where('phone', $phone)->firstOrFail();
        $invitation = new Invitation();
        $invitation->sender()->associate(auth()->user());
        $invitation->receiver()->associate($contactUser);
        $invitation->save();
        return $this->index();
    }

    public function index() {
        $sentInvitations = DB::table('invitations')
            ->whereUserId(auth()->user()->id)
            ->join('users', 'users.id', '=', 'invitations.contact_user_id')
            ->select('users.id', 'users.name', 'users.phone')
            ->orderByDesc('invitations.created_at')
            ->get();
        $receivedInvitations = DB::table('invitations')
            ->whereContactUserId(auth()->user()->id)
            ->join('users', 'users.id', '=', 'invitations.user_id')
            ->select('users.id', 'users.name', 'users.phone')
            ->orderByDesc('invitations.created_at')
            ->get();
        return response()->json(['sent_invitations' => $sentInvitations, 'received_invitations' => $receivedInvitations]);
    }

    public function delete(Request $request, $contactUserId) {
        DB::table('invitations')
            ->whereUserId(auth()->user()->id)
            ->whereContactUserId($contactUserId)
            ->delete();
        return $this->index();
    }

    public function rejectInvitation(Request $request, $inviterUserId) {
        DB::table('invitations')
            ->whereUserId($inviterUserId)
            ->whereContactUserId(auth()->user()->id)
            ->delete();
        return $this->index();
    }

    public function acceptInvitation(Request $request, $inviterUserId) {
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
        $user->contacts()->attach($inviterUserId, ['contact_name' => $inviter->name]);
        DB::table('invitations')
            ->whereUserId($inviterUserId)
            ->whereContactUserId(auth()->user()->id)
            ->delete();
        return $this->index();
    }
}
