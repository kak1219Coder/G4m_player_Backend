<?php
namespace App\Services;

use App\Models\Competition;
use App\Models\Team;
use App\Models\Matche;
use App\Models\Group;

class CupService
{
    public function generateFirstRound(Competition $competition)
    {
        $teams = Team::inRandomOrder()->take(32)->get(); // par exemple

        if ($teams->count() % 2 !== 0) {
            throw new \Exception('Nombre d’équipes impair. Impossible de générer la coupe.');
        }

        for ($i = 0; $i < $teams->count(); $i += 2) {
            Matche::create([
                'competition_id' => $competition->id,
                'team1_id' => $teams[$i]->id,
                'team2_id' => $teams[$i + 1]->id,
                'phase' => 'round_of_16',
            ]);
        }
    }

    public function generateNextRound(Competition $competition, string $currentPhase, string $nextPhase)
    {
        $matches = Matche::where('competition_id', $competition->id)
            ->where('phase', $currentPhase)
            ->whereNotNull('team1_score')
            ->whereNotNull('team2_score')
            ->get();

        if ($matches->count() % 2 !== 0) {
            throw new \Exception("Nombre de vainqueurs impair.");
        }

        $winners = $matches->map(function ($match) {
            if ($match->team1_score > $match->team2_score) return $match->team1;
            if ($match->team2_score > $match->team1_score) return $match->team2;

            throw new \Exception("Égalité interdite en phase éliminatoire.");
        })->shuffle();

        for ($i = 0; $i < count($winners); $i += 2) {
            Matche::create([
                'competition_id' => $competition->id,
                'team1_id' => $winners[$i]->id,
                'team2_id' => $winners[$i + 1]->id,
                'phase' => $nextPhase,
            ]);
        }
    }

    public function generateGroupMatches(Competition $competition)
    {
        $groups = Group::where('competition_id', $competition->id)->get();

        foreach ($groups as $group) {
            $teams = $group->teams;

            for ($i = 0; $i < $teams->count(); $i++) {
                for ($j = $i + 1; $j < $teams->count(); $j++) {
                    Matche::create([
                        'competition_id' => $competition->id,
                        'team1_id' => $teams[$i]->id,
                        'team2_id' => $teams[$j]->id,
                        'phase' => 'group',
                    ]);
                }
            }
        }
    }

}
