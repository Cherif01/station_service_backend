<?php

namespace App\Modules\Administration\Resources;

use App\Modules\Settings\Resources\AffectationResource;
use App\Modules\Settings\Resources\StationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $affectation = $this->affectations
            ? $this->affectations->firstWhere('status', true)
            : null;

        return [

            // =========================
            // Identité
            // =========================
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'telephone' => $this->telephone,
            'adresse'   => $this->adresse,

            // =========================
            // Image
            // =========================
            'image'     => $this->image
                ? asset('storage/images/users/' . $this->image)
                : null,

            // =========================
            // Métier
            // =========================
            'role'      => $this->role,
            'status'    => $this->status,

            // =========================
            // Station affectée
            // =========================
            'station'   => $affectation && $affectation->station
                ? new StationResource($affectation->station)
                : null,

            // =========================
            // Historique affectations
            // =========================
            'affectations' => AffectationResource::collection(
                $this->whenLoaded('affectations')
            ),

            // =========================
            // Audit
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            // =========================
            // Dates
            // =========================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}