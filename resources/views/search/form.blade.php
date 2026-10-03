{{-- Search module (app/Modules/Search/). Rendered only when the
     'search' flag is on; the controller passes $filters + $departments. --}}
<!--========== Doctors Search ==========-->
<div class="container mt-4">
    <form action="{{ route('doctor') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="doctor-search">Search</label>
            <input type="text" id="doctor-search" name="search" class="form-control" placeholder="Name, specialist, department…" value="{{ $filters['search'] ?? request('search') }}" maxlength="100">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="doctor-department">Department</label>
            <select id="doctor-department" name="department" class="form-control">
                <option value="">All departments</option>
                @foreach(($departments ?? []) as $department)
                    <option value="{{ $department }}" {{ (($filters['department'] ?? request('department')) === $department) ? 'selected' : '' }}>{{ $department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="doctor-date">Available on</label>
            <input type="date" id="doctor-date" name="date" class="form-control" min="{{ now()->toDateString() }}" value="{{ $filters['date'] ?? request('date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search', 'department', 'date']))
                <a href="{{ route('doctor') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>
