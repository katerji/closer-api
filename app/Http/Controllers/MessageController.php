<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller {
    /**
     * Display a listing of the resource.
     * needs pagination
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $chatId) {
        $chat = Chat::findOrFail($chatId);
        if(!$chat->containsUser(auth()->user()->id)) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Permission error',
                'data' => 'Not allowed to view messages of this chat.'
            ]));
        }
        return $chat->messages()->orderBy('updated_at', 'desc')->get();
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'message_type' => 'required|int',
            'chat_id' => 'required|int',
        ]);
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ]));
        }
        $chatId = $request->input('chat_id');
        $senderId = auth()->user()->id;
        $chat = Chat::findOrFail($chatId);
        $chatUsers = $chat->users()->get();
        $isUserInChat = false;
        foreach ($chatUsers as $chatUser) {
            if ($chatUser->id == $senderId) {
                $isUserInChat = true;
                break;
            }
        }
        if (!$isUserInChat) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid permission',
                'data' => 'You are not allowed to send this message to chat.'
            ]));
        }
        $message = Message::create([
            'sender_user_id' => $senderId,
            'chat_id' => $chat->id,
            'message' => $request->input('message'),
            'message_type' => $request->input('message_type')
        ]);
        $chat->touch();
        return response()->json(['message' => $message]);
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
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function show(Message $message) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message) {
        //
    }
}
