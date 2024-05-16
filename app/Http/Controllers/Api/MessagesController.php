<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function getConversationsID(Request $request)
    {
        $user_id=request()->user()->id;
        $messages_to = Message::where('sender_id', $user_id)->groupBy('receiver_id')->get('receiver_id');
        $messages_from = Message::where('receiver_id', $user_id)->groupBy('sender_id')->get('sender_id');
        $answer = [];
        foreach ($messages_to as $message){
            $answer[] = $message->receiver_id;
        }
        foreach ($messages_from as $message){
            $answer[] = $message->sender_id;
        }
        return response()->json(array_unique($answer));
    }

    public function store(Request $request, $receiver_id)
    {
        $request->validate([
            'text' => 'required|min:2|max:100'
        ]);
        $receiver = User::where('id', $receiver_id)->first();
        if (!$receiver){
            return response()->json([
                'text'=>'Receiver not found'
            ], 404);
        }
        $sender_id = $request->user()->id;
        $message = Message::create([
            'sender_id'=>$sender_id,
            'receiver_id'=>$receiver_id,
            'text'=>$request->text
        ]);
        return response()->json($message);
    }

    public function getConversation(Request $request, $id)
    {
        $user_id = $request->user()->id;
        $messages = Message::where('sender_id', $user_id)->where('receiver_id', $id)->union(
            Message::where('sender_id', $id)->where('receiver_id', $user_id)->getQuery()
        )->orderBy("created_at")->get();
        return response()->json($messages);
    }

    public function update(Request $request)
    {
        $request->validate([
            'text'=>'required|min:2|max:100',
            'id'=>'required|numeric',
        ]);
        $message = Message::find($request->id);
        if (!$message){
            return response()->json([
                'text'=>'Сообщение не найдено'
            ], 404);
        }
        $user_id = $request->user()->id;
        if ($user_id !== $message->sender_id){
            return response()->json([
                'text'=>'Вы не создатель сообщения'
            ], 400);
        }

        $message->text = $request->text;
        $message->save();
        return response()->json([
            'text'=>'Сообщение изменено'
        ]);
    }
    public function destroy(Request $request)
    {
        $request->validate([
            'id'=>'required|numeric',
        ]);
        $message = Message::find($request->id);
        if (!$message){
            return response()->json([
                'text'=>'Сообщение не найдено'
            ], 404);
        }
        $user_id = $request->user()->id;
        if ($user_id !== $message->sender_id){
            return response()->json([
                'text'=>'Вы не создатель сообщения'
            ], 400);
        }
        $message->delete();
        return response()->json([
            'text'=>'Сообщение удалено'
        ]);
    }
}
