<section class="doctor-section">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">OUR DOCTORS</span>
            <h3 class="title">Experienced Medical Specialist</h3>
        </div>
        <div class="row g-4">

       @forelse($doctors as $doctor)

        <div class="col-lg-4">

            <div class="doctor-card">

                <div class="card-img">

                    @if($doctor->image)
                        <img class="img-fluid"
                             src="{{ asset('storage/'.$doctor->image) }}"
                             alt="{{ $doctor->name }}">
                    @endif

                </div>

                <div class="card-content">

                    <h4 class="title">
                        {{ $doctor->name }}
                    </h4>

                    <span class="speacility">
                        {{ $doctor->department }}
                    </span>

                    <a href="{{ route('doctor.show', $doctor->id) }}" class="card-btn">
                        Book Appointment
                    </a>
                    
                </div>

            </div>

        </div>

        @empty

        <div class="col-12 text-center">
            <p>No doctors found</p>
        </div>

       @endforelse

     </div>
    </div>
</section>