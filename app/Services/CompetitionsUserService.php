<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionsUser;
use Illuminate\Support\Facades\DB;

class CompetitionsUserService
{
    /**
     * Récupérer la liste des participants avec filtres
     */
    public function getParticipants(array $filters = [], int $perPage = 10)
    {
        $query = CompetitionsUser::with(['competition', 'user']);
        
        // Filtrer par compétition si spécifié
        if (isset($filters['competition_id'])) {
            $query->where('competition_id', $filters['competition_id']);
        }
        
        // Filtrer par utilisateur si spécifié
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Inscrire un utilisateur à une compétition
     */
    public function registerUserToCompetition(int $competitionId, int $userId): array
    {
        return DB::transaction(function () use ($competitionId, $userId) {
            $competition = Competition::findOrFail($competitionId);
            
            // Vérifier si l'utilisateur est déjà inscrit
            $existingParticipation = $this->checkExistingParticipation($competitionId, $userId);
            
            if ($existingParticipation) {
                return [
                    'success' => false,
                    'message' => 'L\'utilisateur est déjà inscrit à cette compétition',
                    'error_code' => 'already_registered',
                    'status_code' => 409
                ];
            }
            
            // Vérifier si la compétition peut accepter des participants
            $canRegister = $this->canUserRegister($competition);
            
            if (!$canRegister['success']) {
                return $canRegister;
            }
            
            // Créer l'inscription
            $competitionUser = CompetitionsUser::create([
                'competition_id' => $competitionId,
                'user_id' => $userId,
                'points' => 0,
            ]);
            
            // Mettre à jour la compétition
            $this->updateCompetitionAfterRegistration($competition);
            
            return [
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => $competitionUser->load(['competition', 'user']),
                'competition_status' => $competition->fresh()->status,
                'status_code' => 201
            ];
        });
    }

    /**
     * Désinscrire un utilisateur d'une compétition
     */
    public function unregisterUser(CompetitionsUser $competitionsUser): array
    {
        return DB::transaction(function () use ($competitionsUser) {
            $competition = $competitionsUser->competition;
            
            // Supprimer l'inscription
            $competitionsUser->delete();
            
            // Mettre à jour la compétition
            $this->updateCompetitionAfterUnregistration($competition);
            
            return [
                'success' => true,
                'message' => 'Désinscription réussie',
                'competition_status' => $competition->fresh()->status,
                'status_code' => 200
            ];
        });
    }

    /**
     * Obtenir les détails d'une inscription
     */
    public function getParticipationDetails(CompetitionsUser $competitionsUser): array
    {
        return [
            'success' => true,
            'data' => $competitionsUser->load(['competition', 'user']),
            'status_code' => 200
        ];
    }

    /**
     * Vérifier si un utilisateur est déjà inscrit à une compétition
     */
    private function checkExistingParticipation(int $competitionId, int $userId): ?CompetitionsUser
    {
        return CompetitionsUser::where('competition_id', $competitionId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Vérifier si un utilisateur peut s'inscrire à une compétition
     */
    private function canUserRegister(Competition $competition): array
    {
        // Vérifier si la compétition est pleine
        if ($competition->status === 'full') {
            return [
                'success' => false,
                'message' => 'Cette compétition est complète',
                'error_code' => 'competition_full',
                'status_code' => 409
            ];
        }
        
        // Vérifier si la compétition peut encore accepter des participants
        if ($competition->current_participants >= $competition->max_participants) {
            return [
                'success' => false,
                'message' => 'Cette compétition a atteint le nombre maximum de participants',
                'error_code' => 'max_participants_reached',
                'status_code' => 409
            ];
        }

        // Vérifier si la compétition accepte encore des inscriptions (statut)
        if (!in_array($competition->status, ['upcoming'])) {
            return [
                'success' => false,
                'message' => 'Les inscriptions ne sont plus ouvertes pour cette compétition',
                'error_code' => 'registration_closed',
                'status_code' => 409
            ];
        }
        
        return ['success' => true];
    }

    /**
     * Mettre à jour la compétition après une inscription
     */
    private function updateCompetitionAfterRegistration(Competition $competition): void
    {
        // Incrémenter le nombre de participants
        $competition->increment('current_participants');
        
        // Mettre à jour le statut si nécessaire
        if ($competition->status !== 'upcoming') {
            $competition->update(['status' => 'upcoming']);
        }
        
        // Vérifier si la compétition est maintenant pleine
        if ($competition->current_participants >= $competition->max_participants) {
            $competition->update(['status' => 'full']);
        }
    }

    /**
     * Mettre à jour la compétition après une désinscription
     */
    private function updateCompetitionAfterUnregistration(Competition $competition): void
    {
        // Décrémenter le nombre de participants
        $competition->decrement('current_participants');
        
        // Mettre à jour le statut si la compétition n'est plus pleine
        if ($competition->status === 'full' && $competition->current_participants < $competition->max_participants) {
            $competition->update(['status' => 'upcoming']);
        }
    }

    /**
     * Obtenir les statistiques d'une compétition
     */
    public function getCompetitionStats(int $competitionId): array
    {
        $competition = Competition::findOrFail($competitionId);
        
        return [
            'success' => true,
            'data' => [
                'competition_id' => $competitionId,
                'current_participants' => $competition->current_participants,
                'max_participants' => $competition->max_participants,
                'available_slots' => $competition->availableSlots(),
                'is_full' => $competition->isFull(),
                'can_register' => $competition->canUserRegister(),
                'status' => $competition->status
            ],
            'status_code' => 200
        ];
    }

    /**
     * Vérifier si un utilisateur spécifique peut s'inscrire à une compétition
     */
    public function canSpecificUserRegister(int $competitionId, int $userId): array
    {
        $competition = Competition::findOrFail($competitionId);
        
        // Vérifier si déjà inscrit
        if ($this->checkExistingParticipation($competitionId, $userId)) {
            return [
                'success' => false,
                'can_register' => false,
                'reason' => 'already_registered',
                'message' => 'L\'utilisateur est déjà inscrit à cette compétition'
            ];
        }
        
        // Vérifier les conditions générales
        $canRegister = $this->canUserRegister($competition);
        
        return [
            'success' => true,
            'can_register' => $canRegister['success'],
            'reason' => $canRegister['success'] ? 'eligible' : $canRegister['error_code'],
            'message' => $canRegister['success'] ? 'L\'utilisateur peut s\'inscrire' : $canRegister['message'],
            'competition_status' => $competition->status,
            'available_slots' => $competition->availableSlots()
        ];
    }
}