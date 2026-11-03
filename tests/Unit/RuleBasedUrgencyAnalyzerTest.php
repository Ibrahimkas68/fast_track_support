<?php

use App\Enums\TicketPriority;
use App\Services\Urgency\RuleBasedUrgencyAnalyzer;

test('classifies French impact signals deterministically', function (string $description, TicketPriority $expected) {
    $analyzer = new RuleBasedUrgencyAnalyzer;

    $result = $analyzer->analyze($description);
    $again = $analyzer->analyze($description);

    expect($result->priority)->toBe($expected);
    expect($result->source)->toBe('local');
    expect($result->reason)->not->toBeEmpty();
    expect($again)->toEqual($result);
})->with([
    'unavailable' => ['Le service est indisponible.', TicketPriority::High],
    'essential blockage' => ['Notre activité essentielle est bloquée.', TicketPriority::High],
    'access impossible' => ['Impossible de travailler sur la facturation.', TicketPriority::High],
    'cannot access' => ['L’équipe ne peut plus accéder au service.', TicketPriority::High],
    'degraded with workaround' => ['La fonction est dégradée, une solution de contournement existe.', TicketPriority::Medium],
    'temporary solution' => ['Export en erreur, solution temporaire : CSV.', TicketPriority::Medium],
    'cosmetic' => ['Simple décalage visuel sur mobile.', TicketPriority::Low],
    'information' => ['Une demande d’information sur la procédure.', TicketPriority::Low],
    'not urgent' => ['pas urgent, simple décalage visuel, tout reste utilisable', TicketPriority::Low],
    'negated outage' => ['aucune panne, aucun blocage, simple demande d’information', TicketPriority::Low],
    'unknown' => ['Bonjour, voici ma demande.', TicketPriority::Medium],
    'highest impact wins' => ['Un défaut visuel et un service indisponible.', TicketPriority::High],
    'local negation' => ['Aucune panne visuelle, mais impossible de travailler.', TicketPriority::High],
    'accent and case' => ['ACTIVITÉ ESSENTIELLE BLOQUÉE.', TicketPriority::High],
    'not unavailable' => ['Le service n’est pas indisponible. Demande d’information.', TicketPriority::Low],
    'not blocked' => ['L’activité n’est pas bloquée. Simple défaut cosmétique.', TicketPriority::Low],
    'no outage' => ['Pas de panne, problème cosmétique.', TicketPriority::Low],
    'urgent alone' => ['urgent', TicketPriority::Medium],
]);

test('explains the absence of a recognized signal', function () {
    $result = (new RuleBasedUrgencyAnalyzer)->analyze('Bonjour.');

    expect($result->reason)->toBe('Aucun signal déterminant reconnu ; urgence moyenne proposée.');
});
