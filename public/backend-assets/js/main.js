
<select name="cmbOrderDate" class="form-control border-warning" required>
    <option value="">Select Order Date</option>

    @foreach($qOrderDate as $sOrderDate)
        @if(in_array(date('Y-m-d', strtotime($sOrderDate)), [
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 days'))
        ]))
            <option value="{{ date('Y-m-d', strtotime($sOrderDate)) }}">
                {{ date('d-m-Y', strtotime($sOrderDate)) }}
            </option>
        @endif
    @endforeach
</select>
        
