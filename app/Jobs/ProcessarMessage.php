<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as FacadesLog;
class ProcessarMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    /**
     * Create a new job instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            $message = Message::where('status', Message::PENDING)->where('retries', '<', 3)->first();
            if ($message) {

                $message->status = Message::PROCESSING;
                $message->save();
                sleep(rand(1, 10));

                for ($i = $message->retries; $i <= 2; $i++) {

                    FacadesLog:: channel('message')->info('Processando tentativa de envio: ' . $i+1 . ' da mensagem: ' . $message->id);
                    FacadesLog::channel('message')->info('Retries: ' . $message->retries);
                    FacadesLog::channel('message')->info('Status: ' . $message->status);
                    FacadesLog::channel('message')->info('--------------------------------');

                    $message->retries = $message->retries + 1;
                    $message->save();

                    //Simulação para aumentar a chance de falha
                    $randomStatus = [Message::FAILED, Message::SENT];
                    $selectedStatus = $randomStatus[array_rand($randomStatus)];

                    $message->history()->create([
                        'status' => $selectedStatus,
                        'message_id' => $message->id,
                    ]);

                    if ($selectedStatus == Message::SENT) {
                        $message->status = $selectedStatus;
                        $message->save();
                        FacadesLog::channel('message')->info('Mensagem ' . $message->id . ' enviada com sucesso');
                        break;
                    }

                    sleep(rand(1, 10));
                }

                if ($message->status == Message::PROCESSING) {
                    $message->status = Message::FAILED;
                    $message->save();
                    FacadesLog::channel('message')->error('Falha no envio da mensagem: ' . $message->id);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
