<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
