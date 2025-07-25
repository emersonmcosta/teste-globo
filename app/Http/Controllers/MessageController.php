<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Jobs\ProcessarMessage;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Message API",
 *     version="1.0",
 *     description="API para criar, listar, exibir e reprocessar mensagens"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/public/api",
 *     description="Servidor local"
 * )
 */
class MessageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/messages",
     *     summary="Listar todas as mensagens",
     *     tags={"Messages"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de mensagens",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Message")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Message::all());
    }

    /**
     * @OA\Post(
     *     path="/message",
     *     summary="Criar nova mensagem",
     *     tags={"Messages"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Olá mundo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mensagem criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Message 1 created and processing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
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

            ProcessarMessage::dispatch($message);

            return response()->json([
                'id' => $message->id,
                'message' => "Message {$message->id} created and processing"
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/message/{id}",
     *     summary="Exibir uma mensagem por ID",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da mensagem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mensagem encontrada",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mensagem não encontrada"
     *     )
     * )
     */
    public function show($id)
    {
        $message = Message::find($id);
        if(!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        return response()->json($message);
    }

    /**
     * @OA\Post(
     *     path="/message/{id}/retry",
     *     summary="Reprocessar uma mensagem",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da mensagem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mensagem reprocessada"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mensagem não encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Não é possível reprocessar"
     *     )
     * )
     */
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
