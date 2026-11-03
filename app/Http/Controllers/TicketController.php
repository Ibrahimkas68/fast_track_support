<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Exceptions\TicketStorageException;
use App\Http\Requests\StoreTicketRequest;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private TicketService $tickets) {}

    public function create(): View
    {
        return view('tickets.create', ['formErrors' => session('errors', new ViewErrorBag)]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['title', 'description', 'priority', 'status']);
        $data['priority'] = TicketPriority::from($data['priority']);
        $data['status'] = TicketStatus::from($data['status']);

        try {
            $ticket = $this->tickets->create($data);
        } catch (TicketStorageException $exception) {
            return redirect()->route('tickets.create')->withErrors(['storage' => $exception->getMessage()])
                ->withInput($request->safe()->only(['title', 'description', 'priority', 'status']));
        }

        return redirect()->route('tickets.show', $ticket->id, 303)
            ->with('success', 'Le ticket a bien été créé.');
    }

    public function show(int $ticket): View
    {
        $record = $this->tickets->find($ticket);
        abort_if($record === null, 404);

        return view('tickets.show', ['ticket' => $record]);
    }
}
