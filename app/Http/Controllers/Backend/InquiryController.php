<?php

namespace App\Http\Controllers\Backend;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InquiryController extends BaseController
{
    protected string $resource = 'inquiry';

    public function __construct()
    {
        parent::__construct();

        // Custom permissions for inquiry-specific actions
        $this->applyMethodPermission('inquiry_reply', ['reply']);
        $this->applyMethodPermission('inquiry_export', ['export']);
    }

    /**
     * Display a listing of inquiries.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.inquiries.index');
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $query = Inquiry::with(['property', 'user']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('contact', function ($inquiry) {
                return '<strong>'.$inquiry->name.'</strong><br>
                        <small class="text-muted"><i class="fas fa-envelope"></i> '.$inquiry->email.'</small><br>
                        <small class="text-muted"><i class="fas fa-phone"></i> '.($inquiry->phone ?? 'N/A').'</small>';
            })
            ->addColumn('property', function ($inquiry) {
                if ($inquiry->property) {
                    $propertyUrl = route('admin.properties.show', $inquiry->property->id);
                    return '<a href="'.$propertyUrl.'" class="text-primary">'.$inquiry->property->title.'</a><br>
                            <small class="text-muted">'.$inquiry->property->city.'</small>';
                }
                return '<span class="text-muted">Property Deleted</span>';
            })
            ->addColumn('message', function ($inquiry) {
                $message = $inquiry->message;
                return strlen($message) > 100 ? substr($message, 0, 100).'...' : $message;
            })
            ->addColumn('status', function ($inquiry) {
                $badges = [
                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'contacted' => '<span class="badge bg-info">Contacted</span>',
                    'closed' => '<span class="badge bg-success">Closed</span>',
                ];
                return $badges[$inquiry->status] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('user', function ($inquiry) {
                if ($inquiry->user) {
                    return $inquiry->user->full_name;
                }
                return '<span class="text-muted">Guest</span>';
            })
            ->addColumn('date', function ($inquiry) {
                return $inquiry->created_at->format('Y-m-d').'<br>
                        <small class="text-muted">'.$inquiry->created_at->diffForHumans().'</small>';
            })
            ->addColumn('actions', function ($inquiry) {
                $showUrl = route('admin.inquiries.show', $inquiry->id);
                return '
                    <div class="btn-group">
                        <a href="'.$showUrl.'" class="btn btn-sm btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-primary reply-inquiry" data-id="'.$inquiry->id.'" title="Reply">
                            <i class="fas fa-reply"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-inquiry" data-id="'.$inquiry->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->filterColumn('contact', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereHas('property', function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['contact', 'property', 'message', 'status', 'user', 'date', 'actions'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiry $inquiry)
    {
        $inquiry->load(['property', 'user']);
        return view('admin.inquiries.show', compact('inquiry'));
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Inquiry status updated!',
                'inquiry' => $inquiry,
            ]);
        }

        sweetalert()->success('Inquiry status updated successfully!');
        return redirect()->route('admin.inquiries.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiry $inquiry, Request $request)
    {
        $inquiry->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Inquiry deleted successfully!',
            ]);
        }

        sweetalert()->success('Inquiry deleted successfully!');
        return redirect()->route('admin.inquiries.index');
    }

    /**
     * Reply to an inquiry (could send email)
     */
    public function reply(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'reply_message' => 'required|string',
        ]);

        // TODO: Implement email sending logic here
        // Mail::to($inquiry->email)->send(new InquiryReplyMail($inquiry, $validated['reply_message']));

        // Update status to contacted
        $inquiry->update(['status' => 'contacted']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Reply sent successfully!',
                'inquiry' => $inquiry,
            ]);
        }

        sweetalert()->success('Reply sent successfully!');
        return redirect()->route('admin.inquiries.show', $inquiry->id);
    }

    /**
     * Export inquiries to CSV
     */
    public function export(Request $request)
    {
        $inquiries = Inquiry::with(['property', 'user'])->get();

        $filename = 'inquiries_'.date('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($inquiries) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Property', 'Message', 'Status', 'Date']);

            foreach ($inquiries as $inquiry) {
                fputcsv($file, [
                    $inquiry->id,
                    $inquiry->name,
                    $inquiry->email,
                    $inquiry->phone,
                    $inquiry->property ? $inquiry->property->title : 'N/A',
                    $inquiry->message,
                    $inquiry->status,
                    $inquiry->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
