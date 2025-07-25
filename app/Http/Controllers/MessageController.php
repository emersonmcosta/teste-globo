<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Jobs\ProcessarMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /** List all messages */
    public function index()
    {
        return response()->json(Message::all());
    }

    /** Create a new message */
    public function store(Request $request)
    {

        if(!$request->has('message')) {
            return response()->json(['error' => 'Message is required'], 422);
        }

        if(Message::where('message', $request->message)->whereNotIn('status', [Message::SENT, Message::FAILED])->exists()) {
            return response()->json(['error' => 'Message already exists'], 422);
        }

        try {
            $message = new Message();
            $message->message = $request->message;
            $message->status = Message::PENDING;
            $message->retries = 0;
            $message->save();

            //Process the message
            ProcessarMessage::dispatch($message);

            return response()->json([
                'id' => $message->id,
                'message' => "Message {$message->id} created and processing"
            ]);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $message = Message::find($id);
        if(!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        return response()->json($message);
    }

    /** Reprocess a message */
    public function reprocess($id)
    {
        $message = Message::find($id);
        if(!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        if($message->status == Message::SENT) {
            return response()->json(['error' => 'You cannot reprocess a sent message'], 422);
        }

        $message->status = Message::PENDING;
        $message->retries = 0;
        $message->save();

        ProcessarMessage::dispatch($message);

        return response()->json(['message' => 'Message ' . $message->id . ' reprocessed'], 200);
    }
}
