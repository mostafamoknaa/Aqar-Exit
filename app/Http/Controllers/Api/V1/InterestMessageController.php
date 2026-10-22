<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestMessageController extends Controller
{
    public function index(Request $request, Interest $interest): JsonResponse
    {
        $this->authorizeAccess($request, $interest);
        $interest->messages()->where('sender_id', '!=', $request->user()->id)->update(['read_at' => now()]);

        return response()->json(['data' => $interest->messages()->with('sender:id,name')->get()->map(fn ($m) => [
            'id' => $m->id, 'body' => $m->body, 'sender_role' => $m->sender_role,
            'sender' => $m->sender?->name, 'mine' => $m->sender_id === $request->user()->id,
            'read_at' => $m->read_at?->toISOString(), 'created_at' => $m->created_at?->toISOString(),
        ])]);
    }

    public function store(Request $request, Interest $interest): JsonResponse
    {
        $this->authorizeAccess($request, $interest);
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $user = $request->user();
        $message = $interest->messages()->create([
            'sender_id' => $user->id,
            'sender_role' => $user->isStaff() ? 'staff' : ($interest->buyer_id === $user->id ? 'buyer' : 'seller'),
            'body' => $validated['body'],
        ]);

        return response()->json(['data' => ['id' => $message->id, 'body' => $message->body, 'sender_role' => $message->sender_role]], 201);
    }

    /** Buyer, the listing's seller, or staff — nobody else can read a thread. */
    private function authorizeAccess(Request $request, Interest $interest): void
    {
        $user = $request->user();
        abort_unless($user->isStaff() || in_array($user->id, [$interest->buyer_id, $interest->listing->seller_id], true), 403);
    }
}
