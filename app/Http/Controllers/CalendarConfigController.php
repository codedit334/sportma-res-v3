<?php

namespace App\Http\Controllers;

use App\Models\CalendarConfig;
use Illuminate\Http\Request;

class CalendarConfigController extends Controller
{
    // Fetch the calendar configuration for a specific company
    public function show($companyId)
{
    // Retrieve all calendar configurations for the specified company ID
    $calendarConfigs = CalendarConfig::where('company_id', $companyId)->get();

    // Decode each 'configurations' field and merge them into a single array
    $configurations = $calendarConfigs->flatMap(function ($config) {
        return json_decode($config->configurations, true);
    })->toArray();

    return response()->json(['configurations' => $configurations]);
}
    // Update the calendar configuration for a specific company
    public function update(Request $request, $companyId)
    {
        $calendarConfig = CalendarConfig::where('company_id', $companyId)->first();

        if ($calendarConfig) {
            $calendarConfig->configurations = $request->input('configurations');
            $calendarConfig->save();
            return response()->json($calendarConfig);
        } else {
            return response()->json(['message' => 'Configuration not found'], 404);
        }
    }

    // Create a new calendar config for a company
    public function store(Request $request)
{
    // Validate the incoming request data
    $validated = $request->validate([
        'company_id' => 'required|exists:companies,id', // Make sure the company exists
        'created_by_user_id' => 'required|exists:users,id', // Ensure the user exists
        'configurations' => 'required|array', // Ensure configurations is an array
    ]);

    // Create a new CalendarConfig record
    $calendarConfig = new CalendarConfig();
    $calendarConfig->company_id = $validated['company_id'];
    $calendarConfig->created_by_user_id = $validated['created_by_user_id'];
    $calendarConfig->configurations = json_encode($validated['configurations']); // Store configurations as JSON

    // Save the calendar configuration
    $calendarConfig->save();

    // Return the newly created calendar configuration as JSON response
    return response()->json($calendarConfig, 201); // HTTP status 201 Created
}
}