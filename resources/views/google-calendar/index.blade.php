@extends('backend.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-3">Google Calendar</h1>
    <p class="text-secondary">Approved appointments sync as private events titled “Medical appointment.” Patient names and clinical details are not sent to Google.</p>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning" role="status">{{ session('warning') }}</div>
    @endif
    @if($errors->has('calendar'))
        <div class="alert alert-danger" role="alert">{{ $errors->first('calendar') }}</div>
    @endif

    @if($connection)
        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">Connected</h2>
                    <p class="mb-0 text-secondary">{{ $connection->google_email }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-primary" href="{{ route('doctor.calendar.connect') }}">Reconnect / refresh authorization</a>
                    <form action="{{ route('doctor.calendar.disconnect') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger" type="submit">Disconnect calendar</button>
                    </form>
                </div>
            </div>
        </div>

        <h2 class="h5">Synchronization failures</h2>
        @if($failedEvents->isEmpty())
            <p class="text-secondary">No failed appointment syncs.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Appointment reference</th><th>Error</th><th>Last attempt</th><th></th></tr></thead>
                    <tbody>
                    @foreach($failedEvents as $event)
                        <tr>
                            <td>#{{ $event->appointment_id }}</td>
                            <td><code>{{ $event->last_error_code }}</code></td>
                            <td>{{ $event->updated_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <form action="{{ route('doctor.calendar.retry', $event->appointment_id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Retry</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body">
                <h2 class="h5">Connect your calendar</h2>
                <p class="text-secondary">Only appointment times and a generic event title are synced. Pending appointments are not added. Canceled or deleted appointments are removed from the connected calendar.</p>
                <a class="btn btn-primary" href="{{ route('doctor.calendar.connect') }}">Connect Google Calendar</a>
            </div>
        </div>
    @endif
</div>
@endsection
