<?php

namespace App\Http\Controllers;

use App\Models\CalendarConfig;
use Illuminate\Http\Request;

class CalendarConfigController extends Controller
{
    // Fetch the calendar configuration for a specific company
    public function show($companyId)
    {
        $calendarConfig = CalendarConfig::where('company_id', $companyId)->first();
        return response()->json($calendarConfig);
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
        $calendarConfig = new CalendarConfig();
        $calendarConfig->company_id = $request->input('company_id');
        $calendarConfig->created_by_user_id = $request->input('created_by_user_id');
        $calendarConfig->configurations = $request->input('configurations');
        $calendarConfig->save();

        return response()->json($calendarConfig, 201);
    }
}