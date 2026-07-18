<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use App\Models\ShiftSwapRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftSwapRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $canApprove = $user->can('manage schedules');

        $requests = ShiftSwapRequest::with([
                'requester:id,name',
                'requesterSchedule.shift',
                'targetUser:id,name',
                'targetSchedule.shift',
            ])
            ->when(!$canApprove, function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('target_user_id', $user->id)
                    ->orWhereNull('target_user_id');
            })
            ->latest()
            ->get();

        return Inertia::render('HR/ShiftSwaps/Index', [
            'requests' => $requests,
            'mySchedules' => $user->shiftSchedules()
                ->where('work_date', '>=', now()->toDateString())
                ->with('shift')
                ->orderBy('work_date')
                ->get(),
            'canApprove' => $canApprove,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'requester_schedule_id' => ['required', 'exists:shift_schedules,id'],
            'target_user_id' => ['nullable', 'exists:users,id'],
            'target_schedule_id' => ['nullable', 'exists:shift_schedules,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $schedule = ShiftSchedule::findOrFail($data['requester_schedule_id']);
        abort_unless($schedule->user_id === $request->user()->id, 403);

        ShiftSwapRequest::create([
            ...$data,
            'requester_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Swap request submitted.');
    }

    public function accept(Request $request, ShiftSwapRequest $swapRequest): RedirectResponse
    {
        abort_unless($swapRequest->status === 'pending', 409, 'This request is no longer pending.');
        abort_unless(
            $swapRequest->target_user_id === null || $swapRequest->target_user_id === $request->user()->id,
            403
        );

        $swapRequest->update([
            'target_user_id' => $request->user()->id,
            'accepted_by' => $request->user()->id,
            'status' => 'accepted',
        ]);

        return back()->with('success', 'Swap accepted, awaiting manager approval.');
    }

    public function review(Request $request, ShiftSwapRequest $swapRequest): RedirectResponse
    {
        abort_unless($request->user()->can('manage schedules'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'approved') {
            $requesterSchedule = $swapRequest->requesterSchedule;

            if ($swapRequest->target_schedule_id) {
                // Mutual swap: exchange the two employees on their schedules
                $targetSchedule = $swapRequest->targetSchedule;
                [$requesterUserId, $targetUserId] = [$requesterSchedule->user_id, $targetSchedule->user_id];

                $requesterSchedule->update(['user_id' => $targetUserId]);
                $targetSchedule->update(['user_id' => $requesterUserId]);
            } else {
                // One-way giveaway: hand the shift to the accepting user
                $requesterSchedule->update(['user_id' => $swapRequest->target_user_id]);
            }
        }

        $swapRequest->update([
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'] ?? null,
        ]);

        return back()->with('success', 'Swap request '.$data['status'].'.');
    }

    public function cancel(Request $request, ShiftSwapRequest $swapRequest): RedirectResponse
    {
        abort_unless($swapRequest->requester_id === $request->user()->id, 403);
        abort_unless($swapRequest->status === 'pending', 409, 'Only pending requests can be cancelled.');

        $swapRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Swap request cancelled.');
    }
}