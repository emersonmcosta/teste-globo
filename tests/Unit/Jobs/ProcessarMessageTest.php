<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Message;
use App\Models\MessageHistory;
use App\Jobs\ProcessarMessage;
use Illuminate\Support\Facades\Log;
use Mockery;

class ProcessarMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_process_a_pending_message_and_update_status_and_history()
    {
        $message = Message::factory()->create([
            'status' => Message::PENDING,
            'retries' => 0
        ]);

        (new ProcessarMessage($message))->handle();
        $message->refresh();
        $this->assertContains($message->status, [Message::SENT, Message::FAILED]);
        $this->assertGreaterThanOrEqual(1, $message->history()->count());
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
