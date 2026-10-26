<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PlantConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Ajustes de la planta por grupo (empresa, acopio, calidad) con su valor vigente.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => PlantConfig::groupsWithValues()]);
    }

    /**
     * Guarda un grupo completo con las mismas reglas que el panel web.
     */
    public function updateGroup(Request $request, string $group): JsonResponse
    {
        if (! isset(PlantConfig::SETTINGS[$group])) {
            abort(404, 'Grupo de configuración inexistente.');
        }
        $data = $request->validate(PlantConfig::groupRules($group), [], PlantConfig::groupAttributeNames($group));
        PlantConfig::saveGroup($group, $data['settings'] ?? [], $request->user()->id);

        return response()->json(['data' => PlantConfig::groupsWithValues()]);
    }
}
