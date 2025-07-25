<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Models\MessageHistory;
use App\Jobs\ProcessarMessage;

class ProcessarMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_process_a_pending_message_and_update_status_and_history()
    {
        Log::shouldReceive('info')->atLeast()->once();

        $message = Message::factory()->create([
            'status' => Message::PENDING,
            'retries' => 0
        ]);

        // Rodar o job diretamente
        (new ProcessarMessage($message))->handle();

        $message->refresh();

        // Verifica se o status foi alterado de PENDING para SENT ou FAILED
        $this->assertContains($message->status, [Message::SENT, Message::FAILED]);

        // Verifica que há pelo menos uma entrada de histórico
        $this->assertGreaterThanOrEqual(1, $message->history()->count());

        // Verifica que o número de retentativas é até 3
        $this->assertLessThanOrEqual(3, $message->retries);
    }

    /** @test */
    public function it_should_not_process_if_no_pending_message()
    {
        Log::shouldReceive('info')->never();

        $message = Message::factory()->create([
            'status' => Message::SENT,
            'retries' => 0
        ]);

        (new ProcessarMessage($message))->handle();

        // Mensagem não deve ser alterada
        $message->refresh();
        $this->assertEquals(Message::SENT, $message->status);
        $this->assertEquals(0, $message->retries);
        $this->assertEquals(0, $message->history()->count());
    }
}
