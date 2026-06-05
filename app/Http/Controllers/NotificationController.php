<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        $data = $notification->data;
        if (! empty($data['url'])) {
            return redirect($data['url']);
        }

        return redirect()->route('notifications.index');
    }

    public function open(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect($url) : redirect()->route('notifications.index');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }
}
