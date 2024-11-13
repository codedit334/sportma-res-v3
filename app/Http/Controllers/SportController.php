<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Sport;
use App\Models\Terrain;

class SportController extends Controller
{

    public function index()
    {
        // Retrieve all sports with their associated terrains
        $sports = Sport::with('terrains')->get();

        return response()->json(['sports' => $sports], 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validatedData = $request->validate([
            'type' => 'required|string|max:255',
            'terrains' => 'required|array',
            'terrains.*.label' => 'required|string|max:255',
            'terrains.*.prices' => 'nullable|array',
        ]);

        // Find the sport by ID
        $sport = Sport::findOrFail($id);
        $sport->update([
            'type' => $validatedData['type'],
        ]);

        // Update or create terrains
        foreach ($validatedData['terrains'] as $terrainData) {
            $terrain = Terrain::updateOrCreate(
                [
                    'sport_id' => $sport->id,
                    'label' => $terrainData['label'],
                ],
                [
                    'prices' => $terrainData['prices'],
                ]
            );
        }

        return response()->json([
            'message' => 'Sport and terrains updated successfully',
            'sport' => $sport->load('terrains'),
        ]);
    }
    
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'terrains' => 'required|array',
            'terrains.*.label' => 'required|string|max:255',
            'terrains.*.prices' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the sport
        $sport = Sport::create([
            'type' => $request->input('type'),
            'company_id' => $request->input('company_id'),
        ]);

        // Create associated terrains
        $terrains = collect($request->input('terrains'))->map(function ($terrainData) use ($sport) {
            return new Terrain([
                'label' => $terrainData['label'],
                'prices' => $terrainData['prices'] ?? [],
                'sport_id' => $sport->id,
            ]);
        });

        $sport->terrains()->saveMany($terrains);

        return response()->json(['sport' => $sport->load('terrains')], 201);
    }
}