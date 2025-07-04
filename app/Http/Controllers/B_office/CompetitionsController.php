<?php

namespace App\Http\Controllers\B_office;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Requests\Tournaments\StoreCompetitionsRequest;

class CompetitionsController extends Controller
{
    public function index()
    {
        $competitions = Competition::with('game')->orderByDesc('date')->get();
        return view('tournaments.index', compact('competitions'));
    }

    public function create()
    {
        $games = Game::all();
        return view('tournaments.create', compact('games'));
    }

    public function store(StoreCompetitionsRequest $request)
    {
        $validated = $request->validated();

        // Préparation des règles et contacts
        $rules = !empty($validated['rules']) ? json_encode(array_filter($validated['rules'])) : null;
        $contacts = [];
        if (!empty($validated['contact_link'])) {
            foreach ($validated['contact_link'] as $contact) {
                if (!empty($contact['type']) && !empty($contact['url'])) {
                    $contacts[] = [
                        'type' => $contact['type'],
                        'url' => $contact['url'],
                    ];
                }
            }
        }

        Competition::create([
            'game_id' => $validated['game_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'max_participants' => $validated['max_participants'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'mode' => $validated['mode'] ?? null,
            'is_online' => $validated['is_online'],
            'location' => $validated['location'] ?? null,
            'reward' => $validated['reward'] ?? null,
            'status' => 'upcoming', // Default status
            'rules' => $rules,
            'contact_link' => !empty($contacts) ? json_encode($contacts) : null,
        ]);

        return redirect()->route('competitions.index')->with('success', 'Compétition créée avec succès.');
    }

    public function show($id)
    {
        $competition = Competition::with('game')->findOrFail($id);
        return view('tournaments.show', compact('competition'));
    }
}
