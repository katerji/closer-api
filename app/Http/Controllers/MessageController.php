<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller {
    private const S3_BUCKET = 'closer-media';

    /**
     * Display a listing of the resource.
     * needs pagination
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $chatId) {
        $chat = Chat::findOrFail($chatId);
        if (!$chat->containsUser(auth()->user()->id)) {
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

    public function upload(Request $request) {
        $validated = Validator::make($request->all(), [
            'chat_id' => 'required',
            'file' => 'required',
            'file_name' => 'required'
        ]);
        if (!$validated) {
            return response()->json(['error' => 'Please enter required fields']);
        }
        $credentials = new Credentials(env('AWS_ACCESS_KEY_ID'), env('AWS_SECRET_ACCESS_KEY'));
        $file = file_get_contents($request->file('file')->getRealPath());
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-1',
            'credentials' => $credentials,
        ]);
        $chatId = $request->input('chat_id');
        $userId = auth()->user()->id;
        $pathToFolder = "$chatId/$userId/";
        $fileExtension = pathinfo($request->input('file_name'), PATHINFO_EXTENSION);
        $fileName = time() . ".$fileExtension";
        $filePath = $pathToFolder . $fileName;
        $s3->putObject([
            'Key' => $filePath,
            'Bucket' => self::S3_BUCKET,
            'Body' => $file
        ]);
        return response()->json(['success' => true]);
    }
}
