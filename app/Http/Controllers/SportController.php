<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Sport;
use App\Models\Terrain;

class SportController extends Controller
{

    public function index($companyId)
{
    // Retrieve sports with their associated terrains, filtered by company_id
    $sports = Sport::with('terrains')
                ->where('company_id', $companyId)
                ->get();

    return response()->json(['sports' => $sports], 200);
}


    public function update(Request $request, int $id)
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
            Terrain::updateOrCreate(
                [
                    'sport_id' => $sport->id,
                ],
                [
                    'label' => $terrainData['label'],  // This assumes 'label' uniquely identifies the terrain per sport
                    'prices' => $terrainData['prices'] ?? null,  // Ensure null if prices are not provided
                ]
            );
        }
    
        // Preload terrains to avoid lazy-loading issues
        $sport->load('terrains');
    
        return response()->json([
            'message' => 'Sport and terrains updated successfully',
            'sport' => $sport,
        ]);
        // return response()->json(['sport' => "sport->load(terrains)"], 201);

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
    public function destroy(int $id)
{
    // Find the sport by ID
    $sport = Sport::findOrFail($id);

    // Delete associated terrains
    $sport->terrains()->delete();

    // Delete the sport
    $sport->delete();

    return response()->json([
        'message' => 'Sport and associated terrains deleted successfully'
    ], 200);
}

}