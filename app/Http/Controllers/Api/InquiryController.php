<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * Display a listing of inquiries.
     */
    public function index()
    {
        $inquiries = Inquiry::with(['property', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($inquiries);
    }

    /**
     * Store a new inquiry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        // Optional: associate with authenticated user
        $validated['user_id'] = null; // Can be auth()->id() if user is logged in

        $inquiry = Inquiry::create($validated);

        return response()->json([
            'message' => 'Inquiry submitted successfully',
            'inquiry' => $inquiry
        ], 201);
    }

    /**
     * Display the specified inquiry.
     */
    public function show(Inquiry $inquiry)
    {
        return response()->json($inquiry->load(['property', 'user']));
    }

    /**
     * Update inquiry status.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,contacted,closed',
        ]);

        $inquiry->update($validated);

        return response()->json([
            'message' => 'Inquiry status updated',
            'inquiry' => $inquiry
        ]);
    }

    /**
     * Remove the specified inquiry.
     */
    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return response()->json([
            'message' => 'Inquiry deleted successfully'
        ]);
    }
}
