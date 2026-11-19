<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\FounderSupportSession;
use App\Models\PlatformAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FounderSupportController extends Controller
{
    public function start(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        abort_unless($business->exists, 404);

        $now = now();
        $supportSession = FounderSupportSession::query()->create([
            'actor_user_id' => $request->user()->getAuthIdentifier(),
            'business_id' => $business->id,
            'reason' => trim($validated['reason']),
            'started_at' => $now,
            'expires_at' => $now->copy()->addMinutes(15),
        ]);

        $request->session()->put('zazu_founder_support_session_id', $supportSession->id);

        $this->audit($request, $supportSession, 'founder.support.started', [
            'expires_at' => $supportSession->expires_at->toIso8601String(),
            'access_mode' => 'read_only',
        ]);

        return redirect()->route('admin.sentinel.support.show', $supportSession);
    }

    public function show(Request $request, FounderSupportSession $supportSession): View
    {
        abort_unless($supportSession->business, 404);

        $business = $supportSession->business;
        $customers = $business->customers()->orderBy('name')->limit(20)->get(['id', 'name', 'created_at']);
        $events = $business->events()->latest('event_date')->limit(20)->get([
            'id',
            'reference',
            'name',
            'event_date',
            'status',
        ]);

        $this->audit($request, $supportSession, 'founder.support.viewed', [
            'customer_rows_returned' => $customers->count(),
            'event_rows_returned' => $events->count(),
            'access_mode' => 'read_only',
        ]);

        return view('admin.sentinel-support', [
            'supportSession' => $supportSession,
            'business' => $business,
            'customers' => $customers,
            'events' => $events,
        ]);
    }

    public function end(Request $request, FounderSupportSession $supportSession): RedirectResponse
    {
        abort_unless((int) $supportSession->actor_user_id === (int) $request->user()->getAuthIdentifier(), 403);

        if ($supportSession->ended_at === null) {
            $supportSession->forceFill([
                'ended_at' => now(),
                'ended_by_user_id' => $request->user()->getAuthIdentifier(),
            ])->save();

            $this->audit($request, $supportSession, 'founder.support.ended', [
                'access_mode' => 'read_only',
            ]);
        }

        if ((int) $request->session()->get('zazu_founder_support_session_id') === (int) $supportSession->id) {
            $request->session()->forget('zazu_founder_support_session_id');
        }

        return redirect()->route('admin.sentinel.businesses.show', $supportSession->business_id);
    }

    private function audit(Request $request, FounderSupportSession $supportSession, string $action, array $metadata = []): void
    {
        PlatformAuditLog::query()->create([
            'actor_user_id' => $request->user()->getAuthIdentifier(),
            'business_id' => $supportSession->business_id,
            'founder_support_session_id' => $supportSession->id,
            'action' => $action,
            'reason' => $supportSession->reason,
            'metadata' => $metadata,
            'request_id' => $request->attributes->get('zazu_request_id'),
            'created_at' => now(),
        ]);
    }
}
