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
    public function show($id)
    {
        $reservation = Reservation::findOrFail($id);

        return response()->json($reservation, 200);
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
            // 'start' => 'required|date',
            // 'end' => 'required|date',
            'content' => 'nullable|string',
            'status' => 'required|string',
        ]);

        // Create the reservation
        // $reservation = Reservation::create($validated);
        $reservation = Reservation::create($request -> all());

        // return response()->json($reservation, 201); // Return created reservation
        return response()->json($reservation, 201); // Return created reservation
    }

    public function batchStore(Request $request)
{

    // Define validation rules
    $validator = Validator::make($request->all(), [
        'reservations' => 'required|array', // Make sure the reservations are an array
        'reservations.*.user_id' => 'required|exists:users,id', // User must exist
        'reservations.*.terrain_id' => 'required|exists:terrains,id', // Terrain must exist
        'reservations.*.title' => 'required|string|max:255',
        'reservations.*.class' => 'required|string|max:255',
        'reservations.*.split' => 'required|string|max:255',
        'reservations.*.clickable' => 'required|boolean',
        'reservations.*.duration' => 'required|integer|min:1',
        'reservations.*.editable' => 'required|boolean',
        'reservations.*.price' => 'required|integer|min:0',
        'reservations.*.category' => 'required|string|max:255',
        'reservations.*.terrain' => 'required|string|max:255',
        'reservations.*.start' => 'required|date',
        'reservations.*.end' => 'required|date|after_or_equal:reservations.*.start',
        'reservations.*.status' => 'required|string|max:255',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400);
    }

    // Loop through the reservations and store them
    try {
        $reservations = $request->input('reservations');
        foreach ($reservations as $reservationData) {
            // Ensure the data is in the correct format (array of key-value pairs)
            Reservation::create([
                'user_id' => $reservationData['user_id'],
                'terrain_id' => $reservationData['terrain_id'],
                'title' => $reservationData['title'],
                'class' => $reservationData['class'],
                'split' => $reservationData['split'],
                'clickable' => $reservationData['clickable'],
                'duration' => $reservationData['duration'],
                'editable' => $reservationData['editable'],
                'price' => $reservationData['price'],
                'category' => $reservationData['category'],
                'terrain' => $reservationData['terrain'],
                'start' => $reservationData['start'],
                'end' => $reservationData['end'],
                'content' => $reservationData['content'],
                'status' => $reservationData['status'],
            ]);
        }

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Reservations saved successfully',
        // ], 200);
    } catch (\Exception $e) {
        // Catch any exceptions and return an error response
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while saving reservations',
            'error' => $e->getMessage(),
        ], 500);
    }

    
    // return all reservations with the same company_id as auth user
    $companyId = auth()->user()->company_id;
    $reservations = Reservation::whereHas('user', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
    })
    ->get();
    
    return response()->json(['message' => 'Events saved successfully', 'reservations' => $reservations], 201); // Return created reservation'], 200);
}

    // Update a reservation
    public function update(Request $request, $id)
    {
        // Validate request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'terrain_id' => 'required|exists:terrains,id',
            'title' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'split' => 'required|string|max:255',
            'clickable' => 'required|boolean',
            'duration' => 'required|integer',
            'editable' => 'required|boolean',
            'price' => 'required|integer',
            'category' => 'required|string|max:255',
            'terrain' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date',
            'content' => 'nullable|string',
            'status' => 'required|string|in:confirmed,pending,canceled',
        ]);

        // Find the reservation to update
        $reservation = Reservation::findOrFail($id);
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
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted successfully'], 200);
    }
}