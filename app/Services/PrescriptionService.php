<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    /**
     * Create a prescription with its medicine rows inside a transaction.
     *
     * When an appointment is provided its patient snapshot is authoritative
     * (the order can never disagree with the record the patient sees in
     * their profile); otherwise the submitted walk-in fields are used.
     */
    public function create(Doctor $doctor, ?Appointment $appointment, array $validated): Prescription
    {
        return DB::transaction(function () use ($doctor, $appointment, $validated) {
            $patient = $this->patientSnapshot($appointment, $validated);

            $prescription = Prescription::create([
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointment?->id ?? $validated['appointment_id'] ?? null,
                'patient_user_id' => $patient['patient_user_id'],
                'patient_name' => $patient['patient_name'],
                'age' => $patient['age'],
                'gender' => $patient['gender'],
                'phone' => $patient['phone'],
                'email' => $patient['email'],
                'symptoms' => $validated['symptoms'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'advice' => $validated['advice'] ?? null,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
            ]);

            $this->syncItems($prescription, $validated['items']);

            return $prescription->load('items');
        });
    }

    /**
     * Update a prescription and replace its medicine rows.
     */
    public function update(Prescription $prescription, ?Appointment $appointment, array $validated): Prescription
    {
        return DB::transaction(function () use ($prescription, $appointment, $validated) {
            $patient = $this->patientSnapshot($appointment, $validated, $prescription);

            $prescription->update([
                'appointment_id' => $appointment?->id ?? $validated['appointment_id'] ?? null,
                'patient_user_id' => $patient['patient_user_id'],
                'patient_name' => $patient['patient_name'],
                'age' => $patient['age'],
                'gender' => $patient['gender'],
                'phone' => $patient['phone'],
                'email' => $patient['email'],
                'symptoms' => $validated['symptoms'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'advice' => $validated['advice'] ?? null,
                'follow_up_date' => $validated['follow_up_date'] ?? null,
            ]);

            $this->syncItems($prescription, $validated['items']);

            return $prescription->load('items');
        });
    }

    /**
     * Delete a prescription together with its rows.
     */
    public function delete(Prescription $prescription): void
    {
        DB::transaction(function () use ($prescription) {
            $prescription->items()->delete();
            $prescription->delete();
        });
    }

    private function patientSnapshot(?Appointment $appointment, array $validated, ?Prescription $current = null): array
    {
        if ($appointment) {
            return [
                'patient_user_id' => $appointment->user_id,
                'patient_name' => $appointment->patient_name,
                'age' => $appointment->age,
                'gender' => $appointment->gender,
                'phone' => $appointment->phone,
                'email' => $appointment->email,
            ];
        }

        return [
            // Walk-in: never unlink an already-linked account on edit.
            'patient_user_id' => $current?->patient_user_id,
            'patient_name' => $validated['patient_name'],
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
        ];
    }

    private function syncItems(Prescription $prescription, array $items): void
    {
        $prescription->items()->delete();

        $rows = collect($items)->map(fn (array $item) => new PrescriptionItem([
            'medicine_name' => $item['medicine_name'],
            'dosage' => $item['dosage'] ?? null,
            'frequency' => $item['frequency'] ?? null,
            'duration' => $item['duration'] ?? null,
            'quantity' => $item['quantity'] ?? null,
            'instructions' => $item['instructions'] ?? null,
        ]));

        $prescription->items()->saveMany($rows);
    }
}
