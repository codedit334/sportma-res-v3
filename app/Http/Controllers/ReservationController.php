<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Terrain;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    // Get all reservations
    public function index()
{
    // Get the authenticated user's company_id
    $companyId = auth()->user()->company_id;

    // Fetch reservations that belong to the same company as the authenticated user
    $reservations = Reservation::whereHas('user', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
    })
    ->get();

    // Return the reservations as a JSON response
    return response()->json($reservations, 200);
}


    // Get a specific reservation by ID
    public function showByPartner($company_id)
{
    // Fetch reservations that belong to the specified company_id
    $reservations = Reservation::whereHas('user', function ($query) use ($company_id) {
        $query->where('company_id', $company_id);
    })->get();

    // Return the reservations as a JSON response
    return response()->json($reservations, 200);
}

    // Create a new reservation
    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user exists
            'terrain_id' => 'required|exists:terrains,id', // Ensure the terrain exists
            'title' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'split' => 'required|string|max:255',
            'clickable' => 'required|boolean',
            'duration' => 'required|integer',
            'editable' => 'required|boolean',
            'price' => 'required|integer',
            'category' => 'required|string|max:255',
            'terrain' => 'required|string|max:255',
            'start' => 'required|date', // Ensure start is provided and valid
            'end' => 'nullable|date',  // Optional end date
            'content' => 'nullable|string',
            'status' => 'required|string',
            'sportma' => 'nullable|boolean',
            'sportma_reservation_id' => 'nullable|uuid',
            'titleEditable' => 'nullable|boolean',
            'deletable' => 'nullable|boolean',
            'draggable' => 'nullable|boolean',
            'resizable' => 'nullable|boolean',
            'calendar_reservation_uuid' => 'required|uuid',
        ]);
    
        // Check if an event with the same start already exists
        $existingEvent = Reservation::where('start', $validated['start'])
        ->where('split', $validated['split'])
        ->first();
    
                    if ($existingEvent) {
                        return response()->json([
                            'success' => false,
                            'message' => "An event with the start time '{$validated['start']}' and the same category '{$validated['split']}' already exists.",
                        ], 422);
                    }
        
    
        // Create the reservation
        $reservation = Reservation::create($validated);
    
        // Return created reservation
        return response()->json($reservation, 201);
    }
    

    public function batchStore(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'reservations' => 'required|array', // Ensure reservations are an array
            'reservations.*.user_id' => 'required|exists:users,id', // User must exist
            'reservations.*.terrain_id' => 'required|exists:terrains,id', // Terrain must exist
            'reservations.*.title' => 'required|string|max:255',
            'reservations.*.class' => 'required|string|max:255',
            'reservations.*.split' => 'required|string|max:255',
            'reservations.*.clickable' => 'required|boolean',
            'reservations.*.duration' => 'required|integer|min:1',
            'reservations.*.editable' => 'required|boolean',
            'reservations.*.titleEditable' => 'nullable|boolean',
            'reservations.*.deletable' => 'nullable|boolean',
            'reservations.*.draggable' => 'nullable|boolean',
            'reservations.*.resizable' => 'nullable|boolean',
            'reservations.*.price' => 'required|integer|min:0',
            'reservations.*.category' => 'required|string|max:255',
            'reservations.*.terrain' => 'required|string|max:255',
            'reservations.*.start' => 'required|date',
            'reservations.*.end' => 'required|date|after_or_equal:reservations.*.start',
            'reservations.*.content' => 'nullable|string',
            'reservations.*.status' => 'required|string|max:255',
            'reservations.*.sportma' => 'nullable|boolean',
            'reservations.*.sportma_reservation_id' => 'nullable|uuid',
            'reservations.*.calendar_reservation_uuid' => 'required|uuid',

        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $reservations = $request->input('reservations');
        $savedReservations = [];
    
        // Loop through the reservations and store them
        try {
            foreach ($reservations as $reservationData) {
                // Check for an existing event with the same start time
                $existingEvent = Reservation::where('start', $reservationData['start'])
    ->where('split', $reservationData['split'])
    ->first();

                if ($existingEvent) {
                    return response()->json([
                        'success' => false,
                        'message' => "An event with the start time '{$reservationData['start']}' already exists.",
                    ], 422);
                }
    
                // Create the reservation if no conflict exists
                $savedReservations[] = Reservation::create([
                    'user_id' => $reservationData['user_id'],
                    'terrain_id' => $reservationData['terrain_id'],
                    'title' => $reservationData['title'],
                    'class' => $reservationData['class'],
                    'split' => $reservationData['split'],
                    'clickable' => $reservationData['clickable'],
                    'titleEditable' => $reservationData['titleEditable'],
                    'deletable' => $reservationData['deletable'],
                    'draggable' => $reservationData['draggable'],
                    'resizable' => $reservationData['resizable'],
                    'duration' => $reservationData['duration'],
                    'editable' => $reservationData['editable'],
                    'price' => $reservationData['price'],
                    'category' => $reservationData['category'],
                    'terrain' => $reservationData['terrain'],
                    'start' => $reservationData['start'],
                    'end' => $reservationData['end'],
                    'content' => $reservationData['content'] ?? null,
                    'status' => $reservationData['status'],
                    'sportma' => $reservationData['sportma'] ?? false,
                    'sportma_reservation_id' => $reservationData['sportma_reservation_id'] ?? null,
                    'calendar_reservation_uuid' => $reservationData['calendar_reservation_uuid'],
                ]);
            }
        } catch (\Exception $e) {
            // Catch any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving reservations',
                'error' => $e->getMessage(),
            ], 500);
        }
    
        // Return all reservations belonging to the same company as the authenticated user
        $companyId = auth()->user()->company_id;
        $reservations = Reservation::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->get();
    
        return response()->json([
            'message' => 'Reservations saved successfully',
            'reservations' => $reservations,
        ], 201);
    }
    

    // Update a reservation
    public function update(Request $request, $id)
    {
        // Validate request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user exists
            'terrain_id' => 'required|exists:terrains,id', // Ensure the terrain exists
            'title' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'split' => 'required|string|max:255',
            'clickable' => 'required|boolean',
            'duration' => 'required|integer',
            'editable' => 'required|boolean',
            'price' => 'required|integer',
            'category' => 'required|string|max:255',
            'terrain' => 'required|string|max:255',
            'start' => 'required|date', // Ensure start is provided and valid
            'end' => 'nullable|date',  // Optional end date
            'content' => 'nullable|string',
            'status' => 'required|string',
            'sportma' => 'nullable|boolean',
            'sportma_reservation_id' => 'nullable|uuid',
            'titleEditable' => 'nullable|boolean',
            'deletable' => 'nullable|boolean',
            'draggable' => 'nullable|boolean',
            'resizable' => 'nullable|boolean',
            'calendar_reservation_uuid' => 'required|uuid',
        ]);

        // Find the reservation to update
        // $reservation = Reservation::findOrFail($id);
        $reservation = Reservation::where('calendar_reservation_uuid', $id)->firstOrFail();

        $reservation->update($validated);

        return response()->json($reservation, 200); // Return updated reservation
    }
    public function batchUpdate(Request $request)
{
    $events = $request->input('events');

    foreach ($events as $eventData) {
        Reservation::where('id', $eventData['id'])->update([
            'start' => $eventData['start'],
            'end' => $eventData['end'],
            // Add other fields to update as needed
        ]);
    }

    return response()->json(['message' => 'Batch update successful']);
}

    // Delete a reservation
    public function destroy($id)
    {
        // $reservation = Reservation::findOrFail($id);
        // $reservation->delete();
        
        $reservation = Reservation::where('calendar_reservation_uuid', $id)->firstOrFail();
        $reservation->delete();
        
        return response()->json(['message' => 'Reservation deleted successfully'], 200);
    }
}