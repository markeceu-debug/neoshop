<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notificação marcada como lida.');
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'Notificações apagadas.');
    }
}