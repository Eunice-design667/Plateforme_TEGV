<?php

namespace App\Services;

use App\Models\BirthdayMessage;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * The personal birthday message shown on the platform (never sent by WhatsApp).
 * One message per member and per birthday, however many times it is asked for.
 */
class BirthdayWishes
{
    public function __construct(private MessageWriter $writer) {}

    public function ensureFor(User $member, CarbonInterface $day): BirthdayMessage
    {
        return BirthdayMessage::query()->firstOrCreate(
            ['user_id' => $member->id, 'birthday_on' => $day->toDateString()],
            ['message' => $this->writer->birthdayWishes($member->first_name)],
        );
    }
}
