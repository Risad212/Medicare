@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Inbox</p>
        <h1 class="mc-title">Notifications</h1>
        <p class="mc-sub">In-app alerts for appointment and lab activity.</p>
    </div>
</div>

<div class="mc-bar">
    <span class="mc-pill p-info">
        <i></i>{{ auth()->user()->unreadNotifications()->count() }} unread
    </span>
    <form action="{{ route('notifications.read-all') }}" method="POST"
          onsubmit="return confirm('Mark all notifications as read?');">
        @csrf
        <button type="submit" class="mc-btn sm ghost">
            <i class="bi bi-check2-all"></i> Mark all as read
        </button>
    </form>
</div>

<div class="mc-card">
    <ul class="m-0 list-none divide-y divide-line-2">
        @forelse($notifications as $notification)
            @php
                $unread = $notification->read_at === null;
                $type = Str::afterLast($notification->type, '\\');
                $icon = match ($type) {
                    'AppointmentBooked' => 'calendar-check',
                    'AppointmentStatusChanged' => 'arrow-repeat',
                    'AppointmentReminder' => 'alarm',
                    'LabResultReady' => 'clipboard2-pulse',
                    default => 'bell',
                };
                $isLab = ! empty($notification->data['lab_order_id']);
                $target = auth()->user()->role === 'doctor'
                    ? ($isLab ? route('doctor.lab-orders.show', $notification->data['lab_order_id']) : route('doctor.appointments'))
                    : ($isLab ? route('admin.lab-orders.show', $notification->data['lab_order_id']) : route('admin.appointments.edit', $notification->data['appointment_id']));
            @endphp
            <li class="flex items-start gap-3.5 px-4.5 py-4 {{ $unread ? 'bg-wash' : '' }}">
                <a href="{{ $target }}" class="flex w-full items-start gap-3.5 no-underline">
                    <div class="mc-av {{ $isLab ? 'b' : ($unread ? 't' : '') }}">
                        <i class="bi bi-{{ $icon }} text-[15px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                            <b class="text-[14px] {{ $unread ? 'text-ink' : 'text-ink-2' }}">{{ $notification->data['title'] ?? 'Notification' }}</b>
                            <time class="whitespace-nowrap text-xs text-mut">{{ $notification->created_at->diffForHumans() }}</time>
                        </div>
                        <p class="mt-0.5 mb-0 text-[13px] {{ $unread ? 'text-ink-2' : 'text-mut' }}">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                    @if($unread)
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-teal"></span>
                    @endif
                </a>
                @if($unread)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="mt-0.5 shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="mc-btn sm ghost" aria-label="Mark as read">
                            <i class="bi bi-check2"></i>
                        </button>
                    </form>
                @endif
            </li>
        @empty
            <li>
                <div class="mc-empty">
                    <b>All caught up</b>
                    No notifications yet.
                </div>
            </li>
        @endforelse
    </ul>

    @if($notifications->hasPages())
        <div class="mc-pg">
            <span>Showing {{ $notifications->firstItem() ?? 0 }}–{{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }}</span>
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@endsection