<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Message",
 *   type="object",
 *   required={"id", "message", "status", "retries"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="message", type="string", example="Olá mundo"),
 *   @OA\Property(property="status", type="string", example="pending"),
 *   @OA\Property(property="retries", type="integer", example=0),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Message extends Model
{
    use HasFactory;
    protected $fillable = ['message'];
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const SENT = 'sent';
    public const FAILED = 'failed';

    protected $with = ['history'];
    


    public function history()
    {
        return $this->hasMany(MessageHistory::class);
    }

}
