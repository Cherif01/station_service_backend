<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Resources\SettingResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettingService
{
    /**
     * =================================================
     * LISTE DES SETTINGS (GLOBAL + STATION ACTIVE)
     * =================================================
     */
    public function index()
    {
        try {

            $settings = Setting::visible()
                ->orderBy('cle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => SettingResource::collection($settings),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des paramètres.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * DÉTAIL D’UN SETTING
     * =================================================
     */
    public function show(int $id)
    {
        try {

            $setting = Setting::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new SettingResource($setting),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Paramètre introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * =================================================
     * CRÉATION
     * =================================================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            /**
             * 🔹 STATION ACTIVE (OPTIONNEL)
             * null = global
             */
            $data['id_station'] = request()->attributes->get('station_active_id');

            /**
             * 🔹 UNICITÉ (id_station + cle)
             */
            $exists = Setting::where('cle', $data['cle'])
                ->where('id_station', $data['id_station'])
                ->exists();

            if ($exists) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Ce paramètre existe déjà pour cette station.',
                ], 409);
            }

            $setting = Setting::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paramètre créé avec succès.',
                'data'    => new SettingResource($setting),
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du paramètre.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * MISE À JOUR
     * =================================================
     */
    public function update(int $id, array $data)
    {
        DB::beginTransaction();

        try {

            $setting = Setting::visible()->findOrFail($id);

            /**
             * 🔹 Empêcher collision sur la clé
             */
            if (isset($data['cle'])) {
                $exists = Setting::where('cle', $data['cle'])
                    ->where('id_station', $setting->id_station)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    DB::rollBack();

                    return response()->json([
                        'status'  => 409,
                        'message' => 'Une autre entrée utilise déjà cette clé.',
                    ], 409);
                }
            }

            $setting->update($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paramètre mis à jour avec succès.',
                'data'    => new SettingResource($setting),
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du paramètre.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =================================================
     * SUPPRESSION
     * =================================================
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {

            $setting = Setting::visible()->findOrFail($id);
            $setting->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Paramètre supprimé avec succès.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du paramètre.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
