<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => ['unread_count' => $request->user()->unreadNotifications()->count(), 'total' => $notifications->total(), 'current_page' => $notifications->currentPage(), 'last_page' => $notifications->lastPage()],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->whereKey($id)->firstOrFail()->markAsRead();

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All marked as read.']);
    }

    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($request->isMethod('PUT')) {
            $user->update($request->validate(['notify_push' => ['nullable', 'boolean'], 'notify_database' => ['nullable', 'boolean'], 'locale' => ['nullable', 'in:en,ar']]));
        }

        return response()->json(['data' => ['notify_push' => $user->notify_push, 'notify_database' => $user->notify_database, 'locale' => $user->locale, 'has_device' => (bool) $user->fcm_token]]);
    }

    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $request->validate(['fcm_token' => ['required', 'string', 'max:500'], 'locale' => ['nullable', 'in:en,ar']]);
        $request->user()->update($validated);

        return response()->json(['message' => 'Device registered.']);
    }
}
