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
        // whereDate: SQLite stores the date with a time part, so a plain equality would miss it.
        $existing = BirthdayMessage::query()
            ->where('user_id', $member->id)
            ->whereDate('birthday_on', $day)
            ->first();

        return $existing ?? BirthdayMessage::create([
            'user_id' => $member->id,
            'birthday_on' => $day->toDateString(),
            'message' => $this->writer->birthdayWishes($member->first_name),
        ]);
    }
}
