<?php

namespace App\Services\Urgency;

use App\Contracts\UrgencyAnalyzerInterface;
use App\Data\UrgencyResult;
use App\Enums\TicketPriority;
use Illuminate\Support\Str;

class RuleBasedUrgencyAnalyzer implements UrgencyAnalyzerInterface
{
    public function analyze(string $description): UrgencyResult
    {
        $text = mb_strtolower(Str::ascii(str_replace(['’', '‘'], "'", $description)), 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\\b(?:aucune?|pas de|sans)\\s+(?:panne|blocage|indisponibilite|interruption)s?\\b/', '', $text);
        $text = preg_replace("/\\b(?:n'est|ne sont)\\s+(?:pas|plus)\\s+(?:indisponibles?|bloque(?:e|es|s)?|en panne)\\b/", '', $text);

        if (preg_match('/\b(?:indisponibles?|indisponibilite|panne|blocage|bloque(?:e|es|s)?|impossible de|ne peut plus|ne peuvent plus)\b/', $text)) {
            return new UrgencyResult(TicketPriority::High, 'Service indisponible ou activité essentielle bloquée.', 'local');
        }

        if (preg_match('/\b(?:degrade(?:e|es|s)?|contournement|temporaire|erreur)\b/', $text)) {
            return new UrgencyResult(TicketPriority::Medium, 'Fonction dégradée ou solution de contournement signalée.', 'local');
        }

        if (preg_match('/\b(?:visuel(?:le|les|s)?|cosmetique|affichage|information|texte d\'aide)\b/', $text)) {
            return new UrgencyResult(TicketPriority::Low, 'Problème cosmétique ou demande d’information.', 'local');
        }

        return new UrgencyResult(TicketPriority::Medium, 'Aucun signal déterminant reconnu ; urgence moyenne proposée.', 'local');
    }
}
