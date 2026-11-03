<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Services\Urgency\RuleBasedUrgencyAnalyzer;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            ['Accès à l’application indisponible', 'Depuis ce matin, l’équipe finance ne peut plus accéder au service de facturation.', 'high', 'open'],
            ['Erreur lors de l’export du rapport', 'L’export PDF est en erreur. Le CSV reste une solution temporaire.', 'medium', 'in_progress'],
            ['Décalage d’affichage sur mobile', 'Simple décalage visuel. Le parcours reste utilisable.', 'low', 'resolved'],
            ['Données absentes dans le tableau de suivi', 'Le service de suivi est indisponible, l’activité essentielle est bloquée.', 'high', 'in_progress'],
            ['Mise à jour du texte d’aide', 'Demande d’information sur le texte d’aide de la page profil.', 'low', 'open'],
            ['Lenteur du chargement des dossiers', 'Fonction dégradée avec contournement : actualiser la page.', 'medium', 'open'],
            ['Facturation rétablie après interruption', 'Le service était indisponible. Le correctif est maintenant en place.', 'high', 'resolved'],
            ['Import du fichier corrigé', 'Erreur d’import corrigée ; une solution temporaire était disponible.', 'medium', 'resolved'],
            ['Précision sur le guide utilisateur', 'Demande d’information sur le guide utilisateur.', 'low', 'in_progress'],
        ];
        $analyzer = new RuleBasedUrgencyAnalyzer;

        $date = now()->subDay()->startOfDay();

        foreach ($examples as $index => [$title, $description, $priority, $status]) {
            $result = $analyzer->analyze($description);
            $fixtureLlm = in_array($index, [0, 1], true);
            $ticket = Ticket::query()->firstOrNew(['title' => '[Démo] '.$title]);
            $ticket->fill([
                'description' => $description,
                'priority' => $priority,
                'status' => $status,
                'suggested_priority' => $result->priority,
                'analysis_reason' => $fixtureLlm ? 'Exemple fictif, sans appel LLM : '.$result->reason : $result->reason,
                'analysis_source' => $fixtureLlm ? 'llm' : 'local',
            ]);
            $ticket->forceFill([
                'created_at' => $index < 3 ? $date : $date->copy()->subDay(),
                'updated_at' => $date,
            ])->save();
        }
    }
}
