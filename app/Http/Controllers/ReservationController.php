<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Terrain;

class ReservationController extends Controller
{
    // Get all reservations
    public function index()
{
    // Get the authenticated user's company_id
    $companyId = auth()->user()->company_id;

    // Fetch reservations that belong to the same company as the authenticated user
    $reservations = Reservation::with(['user', 'terrain'])
        ->whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->get();

    // Return the reservations as a JSON response
    return response()->json($reservations, 200);
}


    // Get a specific reservation by ID
    public function show($id)
    {
        $reservation = Reservation::with(['user', 'terrain'])->findOrFail($id);
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
    $reservations = $request->input('reservations'); // Assuming the data is sent as an array of events
    foreach ($reservations as $reservationData) {
        Reservation::create($reservationData);
    }
    return response()->json(['message' => 'Events saved successfully'], 200);
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