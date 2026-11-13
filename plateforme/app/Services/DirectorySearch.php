<?php

namespace App\Services;

use App\Enums\ChurchRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DirectorySearch
{
    /**
     * Search members by trade, company, skill or role.
     *
     * Results are ranked by years of experience, then by relevance:
     * a match on the main profile beats a match on a secondary profile,
     * and a complete profile beats an incomplete one.
     *
     * @return Collection<int, User>
     */
    public function search(?string $term, ?ChurchRole $role = null, ?string $sector = null): Collection
    {
        $needle = Str::of((string) $term)->lower()->ascii()->trim()->toString();

        return User::query()
            ->withCompletedProfile()
            ->when($role !== null, fn ($query) => $query->whereJsonContains('church_roles', $role->value))
            ->when($sector !== null, fn ($query) => $query->where('sector', $sector))
            ->get()
            ->map(function (User $member) use ($needle): ?array {
                $score = $needle === '' ? 0 : $this->relevance($member, $needle);

                if ($needle !== '' && $score === 0) {
                    return null;
                }

                return ['member' => $member, 'score' => $score + $member->completeness()];
            })
            ->filter()
            ->sort(function (array $a, array $b): int {
                return [$b['member']->years_experience ?? -1, $b['score']]
                    <=> [$a['member']->years_experience ?? -1, $a['score']];
            })
            ->pluck('member')
            ->values();
    }

    private function relevance(User $member, string $needle): int
    {
        $matches = fn (?string $value): bool => $value !== null
            && Str::of($value)->lower()->ascii()->contains($needle);

        $score = 0;

        if ($matches($member->primary_profile)) {
            $score += 30;
        }

        if (collect($member->secondary_profiles ?? [])->contains(fn (string $profile) => $matches($profile))) {
            $score += 20;
        }

        if ($matches($member->job_title)) {
            $score += 15;
        }

        if ($matches($member->company)) {
            $score += 10;
        }

        if (collect($member->roleLabels())->contains(fn (string $label) => $matches($label))) {
            $score += 10;
        }

        if ($matches($member->sectorLabel())) {
            $score += 8;
        }

        if ($matches($member->full_name)) {
            $score += 5;
        }

        return $score;
    }
}
