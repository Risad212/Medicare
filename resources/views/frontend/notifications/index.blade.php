@extends('frontend.layouts.front-app')

@section('meta_title', 'My Notifications')
@section('meta_description', 'Your appointment, lab and prescription updates')
@section('meta_keywords', 'notifications, patient updates, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Notifications'
])

@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<section class="patient-profile py-5">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div>
                    <h4 class="mb-1">Notifications</h4>
                    <p class="text-muted mb-0">
                        {{ $unreadCount }} unread
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">

                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary">
                        Back to Profile
                    </a>

                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                Mark all as read
                            </button>
                        </form>
                    @endif

                </div>

            </div>

        </div>

        @forelse($notifications as $notification)

            @php
                $unread = $notification->read_at === null;
            @endphp

            <div class="card border-0 shadow-sm mb-3" @if($unread) style="border-left:4px solid #05d3b0 !important;" @endif>

                <div class="card-body d-flex justify-content-between align-items-start gap-3">

                    <div>
                        <h6 class="mb-1">
                            {{ $notification->data['title'] ?? 'Notification' }}

                            @if($unread)
                                <span class="badge" style="background:#05d3b0;color:#fff;">New</span>
                            @endif
                        </h6>

                        <p class="mb-1 text-muted">
                            {{ $notification->data['message'] ?? '' }}
                        </p>

                        <small class="text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>

                    @if($unread)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                Mark read
                            </button>
                        </form>
                    @endif

                </div>

            </div>

        @empty

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <h5>
                        No Notifications Yet
                    </h5>

                    <p class="text-muted mb-0">
                        Updates about your appointments, lab reports and prescriptions will appear here.
                    </p>

                </div>

            </div>

        @endforelse

        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</section>

@endsection