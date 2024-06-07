<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
/**
* @OA\Get(
     *     path="/api/user/messages",
     *     tags={"Messages"},
     *     summary="Получение id пользователей, с которыми у вас есть переписка",
     *     @OA\Response(response="200", description="Список id пользователей"),
     * )
     */
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

/**
* @OA\Post(
     *     path="/api/user/messages/{id}",
     *     tags={"Messages"},
     *     summary="Создание сообщения пользователю с данным id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id получателя",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="text",
     *         in="query",
     *         description="Текст сообщения",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Сообщение создано"),
     *     @OA\Response(response="404", description="Получатель не найден"),
     * )
     */
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

/**
* @OA\Get(
     *     path="/api/user/messages/{id}",
     *     tags={"Messages"},
     *     summary="Получение переписки с пользователем",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id получателя",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Все сообщения с сортировкой по времени"),
     * )
     */
    public function getConversation(Request $request, $id)
    {
        $user_id = $request->user()->id;
        $messages = Message::where('sender_id', $user_id)->where('receiver_id', $id)->union(
            Message::where('sender_id', $id)->where('receiver_id', $user_id)->getQuery()
        )->orderBy("created_at")->get();
        return response()->json($messages);
    }

/**
* @OA\Put(
     *     path="/api/user/messages",
     *     tags={"Messages"},
     *     summary="Изменение сообщения",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id сообщения",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="text",
     *         in="query",
     *         description="Новый текст сообщения",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Сообщение изменено"),
     *     @OA\Response(response="404", description="Сообщение не найдено"),
     *     @OA\Response(response="403", description="Вы не создатель сообщения")
     * )
     */
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
            ], 403);
        }

        $message->text = $request->text;
        $message->save();
        return response()->json([
            'text'=>'Сообщение изменено'
        ]);
    }

/**
* @OA\Delete(
     *     path="/api/user/messages",
     *     tags={"Messages"},
     *     summary="Удаление сообщения",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id сообщения",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Сообщение удалено"),
     *     @OA\Response(response="404", description="Сообщение не найдено"),
     *     @OA\Response(response="403", description="Вы не создатель сообщения")
     * )
     */
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
            ], 403);
        }
        $message->delete();
        return response()->json([
            'text'=>'Сообщение удалено'
        ]);
    }
}
