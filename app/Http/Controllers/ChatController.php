<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller {

    public static function getUserChats($user, $withLimit = true) {
        $chats = $user->chats()->orderBy('updated_at', 'desc')->limit(50)->get()->makeHidden('pivot');
        foreach ($chats as &$chat) {
            $chat['messages'] = $chat->messages()->orderBy('created_at', 'desc')->limit(50)->get();
            if (!$chat['name']) {
                $chatUsers = $chat->users()->get(['users.id', 'users.name']);
                foreach ($chatUsers as $chatUser) {
                    if ($chatUser->id != $user->id) {
                        $chat['name'] = $chatUser->name;
                        break;
                    }
                }
            }
        }
        return $chats;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index() {
        return response()->json(self::getUserChats(auth()->user()));
    }

    public function getChat(Request $request, $chatId) {
        $chat = Chat::find($chatId);
        if (!$chat) {
            return response()->json(['error' => "Chat $chatId not found"]);
        }
        $chat['messages'] = $chat->messages()->orderBy('created_at', 'desc')->limit(50)->get();
        if (!$chat['name']) {
            $users = $chat->users()->get(['users.id', 'users.name']);
            foreach ($users as $user) {
                if ($user->id != auth()->user()->id) {
                    $chat['name'] = $user->name;
                    break;
                }
            }
        }
        return response()->json(['chat' => $chat]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|int'
        ]);
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ]));
        }
        $recipient = User::findOrFail($request->input('recipient_id'));
        $contacts = auth()->user()->contacts()->get();
        $areUsersContacts = false;
        foreach ($contacts as $contact) {
            if ($contact->id == $recipient->id) {
                $areUsersContacts = true;
                break;
            }
        }
        if (!$areUsersContacts) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Permission denied.',
                'data' => 'Not allowed to create a chat with this user'
            ]));
        }
        $recipientChats = $recipient->chats()->get();
        $userId = auth()->user()->id;
        foreach ($recipientChats as $chat) {
            $chatUsers = $chat->users()->get();
            foreach ($chatUsers as $user) {
                if ($user->id == $userId) {
                    return response()->json(['chat' => $chat->makeHidden('pivot')]);
                }
            }
        }
        $chat = Chat::create();
        $chat->users()->attach($recipient->id);
        $chat->users()->attach($userId);
        if (!$chat['name']) {
            $users = $chat->users()->get(['users.id', 'users.name']);
            foreach ($users as $user) {
                if ($user->id != auth()->user()->id) {
                    $chat['name'] = $user->name;
                    break;
                }
            }
        }
        $chat['messages'] = [];
        return response()->json(['chat' => $chat]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function show(Chat $chat) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function edit(Chat $chat) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chat $chat) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chat $chat) {
        //
    }
}
