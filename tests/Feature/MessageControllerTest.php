<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessarMessage;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_list_all_messages()
    {
        Message::factory()->count(3)->create();
        $response = $this->getJson('/api/messages');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    /** @test */
    public function it_should_return_error_if_message_is_missing()
    {
        $response = $this->postJson('/api/message', []);
        $response->assertStatus(422)->assertJson(['error' => 'Message is required']);
    }

    /** @test */
    public function it_should_prevent_duplicate_pending_message()
    {
        Message::factory()->create([
            'message' => 'Hello world',
            'status' => Message::PENDING
        ]);

        $response = $this->postJson('/api/message', ['message' => 'Hello world']);
        $response->assertStatus(422)->assertJson(['error' => 'Message already exists']);
    }

    /** @test */
    public function it_should_store_and_dispatch_message_successfully()
    {
        Queue::fake();

        $response = $this->postJson('/api/message', ['message' => 'Process this']);
        $response->assertStatus(200)->assertJsonStructure(['id', 'message']);

        $this->assertDatabaseHas('messages', ['message' => 'Process this']);
        Queue::assertPushed(ProcessarMessage::class);
    }

    /** @test */
    public function it_should_return_message_by_id()
    {
        $message = Message::factory()->create();

        $response = $this->getJson("/api/message/{$message->id}");
        $response->assertStatus(200)->assertJsonFragment(['id' => $message->id]);
    }

    /** @test */
    public function it_should_return_404_if_message_not_found_on_show()
    {
        $response = $this->getJson('/api/message/999');
        $response->assertStatus(404)->assertJson(['error' => 'Message not found']);
    }

    /** @test */
    public function it_should_reprocess_a_failed_message()
    {
        Queue::fake();
        $message = Message::factory()->create(['status' => Message::FAILED, 'retries' => 2]);

        $response = $this->postJson("/api/message/{$message->id}/retry");

        $response->assertStatus(200)->assertJson(['message' => "Message {$message->id} reprocessed"]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => Message::PENDING,
            'retries' => 0
        ]);

        Queue::assertPushed(ProcessarMessage::class);
    }

    /** @test */
    public function it_should_not_reprocess_sent_message()
    {
        $message = Message::factory()->create(['status' => Message::SENT]);

        $response = $this->postJson("/api/message/{$message->id}/retry");

        $response->assertStatus(422)->assertJson(['error' => 'You cannot reprocess a sent message']);
    }

    /** @test */
    public function it_should_return_404_on_reprocess_if_not_found()
    {
        $response = $this->postJson('/api/message/999/retry');
        $response->assertStatus(404)->assertJson(['error' => 'Message not found']);
    }
}
