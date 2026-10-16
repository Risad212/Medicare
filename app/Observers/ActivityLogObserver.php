<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    /**
     * Columns that must never be persisted into the audit trail.
     */
    // Never persist secrets, OAuth tokens, or raw patient PII in audit
    // payloads — log opaque IDs + action only (HIPAA minimum-necessary).
    protected array $sensitive = [
        'password', 'remember_token',
        'google_id', 'avatar', 'access_token', 'refresh_token',
        'patient_name', 'phone', 'email',
    ];

    public function created(Model $model): void
    {
        $this->log('created', $model, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model, $model->getDirty());
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, ['id' => $model->getKey()]);
    }

    protected function log(string $action, Model $model, array $payload): void
    {
        $payload = $this->redact($payload);

        app(ActivityLogger::class)->log(strtolower(class_basename($model)).'.'.$action, $model, $payload);
    }

    protected function redact(array $payload): array
    {
        return array_diff_key($payload, array_flip($this->sensitive));
    }
}
