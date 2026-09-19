{{-- Shared role form: $action, $method, $role (nullable), $grouped, $granted (int[]) --}}
<div class="mc-sec">
    <div class="mc-sec-hd"><span class="no">01</span><h3>Identity</h3><p>What this role is called</p></div>
    <div class="mc-sec-bd">
        <div class="mc-f">
            <label>Role name <i class="req">*</i></label>
            <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" placeholder="e.g. Receptionist" required>
            @error('name')<span class="mc-hint text-red">{{ $message }}</span>@enderror
        </div>
        <div class="mc-f">
            <label>Slug <i class="req">*</i></label>
            <input type="text" name="slug" value="{{ old('slug', $role->slug ?? '') }}" placeholder="e.g. receptionist" pattern="[a-z0-9-]+" required>
            <span class="mc-hint">Lowercase letters, numbers, dashes. Used by Gates — avoid renaming live roles.</span>
            @error('slug')<span class="mc-hint text-red">{{ $message }}</span>@enderror
        </div>
        <div class="mc-f full">
            <label>Description</label>
            <input type="text" name="description" value="{{ old('description', $role->description ?? '') }}" placeholder="Who should hold this role?">
        </div>
    </div>
</div>

<div class="mc-sec">
    <div class="mc-sec-hd"><span class="no">02</span><h3>Permissions</h3><p>{{ count($granted ?? []) }} granted</p></div>
    <div class="mc-sec-bd !grid-cols-1">
        @foreach($grouped as $group => $permissions)
            <div class="mc-f full">
                <label>{{ $group }}</label>
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    @foreach($permissions as $permission)
                        <label class="mc-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                {{ in_array($permission->id, old('permissions', $granted ?? [])) ? 'checked' : '' }}>
                            <span>{{ $permission->name }}<span class="mc-hint block">{{ $permission->slug }}</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
        @error('permissions')<span class="mc-hint text-red">{{ $message }}</span>@enderror
    </div>
</div>

<div class="mc-formacts">
    <button type="submit" class="mc-btn"><i class="bi bi-check-lg"></i> {{ isset($role) ? 'Save role' : 'Create role' }}</button>
    <a href="{{ route('admin.roles.index') }}" class="mc-btn ghost">Cancel</a>
</div>
