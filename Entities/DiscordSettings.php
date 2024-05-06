<?php

namespace Modules\Discord\Entities;

use Illuminate\Database\Eloquent\Model;

class DiscordSettings extends Model
{
    protected $table = 'discord';

    public $timestamps = false;

    protected $primaryKey = 'mailbox_id';

    public $incrementing = false;

    protected $fillable = ['mailbox_id', 'enabled', 'webhook_url'];
}