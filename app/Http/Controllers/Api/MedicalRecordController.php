<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Services\MedicalRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MedicalRecordController extends Controller
{
    public function __construct(private readonly MedicalRecordService $service)
    {
    }

    /** GET /medical-records?patient_id= */
    public function index(Request $request)
    {
        $request->validate(['patient_id' => ['nullable', 'integer', 'exists:users,id']],
            ['patient_id.exists' => 'Пациент не найден']);

        return MedicalRecordResource::collection(
            $this->service->list($request->user(), $request->integer('patient_id') ?: null)
        );
    }

    /** POST /appointments/{appointment}/medical-record — только врач этого приёма */
    public function store(StoreMedicalRecordRequest $request, Appointment $appointment)
    {
        Gate::authorize('create', [MedicalRecord::class, $appointment]);

        $record = $this->service->create($appointment, $request->validated());

        return (new MedicalRecordResource($record))->response()->setStatusCode(201);
    }

    /** GET /medical-records/{medicalRecord} */
    public function show(MedicalRecord $medicalRecord)
    {
        Gate::authorize('view', $medicalRecord);

        return new MedicalRecordResource($medicalRecord->load('appointment.doctor.user'));
    }

    /** PUT/PATCH /medical-records/{medicalRecord} */
    public function update(StoreMedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        Gate::authorize('update', $medicalRecord);

        return new MedicalRecordResource($this->service->update($medicalRecord, $request->validated()));
    }

    /** DELETE /medical-records/{medicalRecord} */
    public function destroy(MedicalRecord $medicalRecord)
    {
        Gate::authorize('delete', $medicalRecord);
        $this->service->delete($medicalRecord);

        return response()->noContent();
    }
}
