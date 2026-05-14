<?php

namespace App\Http\Controllers\Backend;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends BaseController
{
    protected string $resource = 'invoice';

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $users = User::all();
        return view('admin.invoices.index', compact('users'));
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = Invoice::with(['user', 'transactions']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('invoice_number_display', function ($invoice) {
                $date = '<small class="text-muted d-block">' . $invoice->issue_date->format('M d, Y') . '</small>';
                return '<strong>' . $invoice->invoice_number . '</strong>' . $date;
            })
            ->addColumn('customer', function ($invoice) {
                $email = '<small class="text-muted d-block">' . $invoice->customer_email . '</small>';
                return '<strong>' . $invoice->customer_name . '</strong>' . $email;
            })
            ->addColumn('amount', function ($invoice) {
                return '<strong class="text-primary">' . $invoice->formatted_total . '</strong>';
            })
            ->addColumn('status', function ($invoice) {
                // Check if overdue
                if ($invoice->isOverdue()) {
                    return '<span class="badge bg-danger">Overdue</span>';
                }
                return $invoice->status_badge;
            })
            ->addColumn('due_date_display', function ($invoice) {
                $class = $invoice->isOverdue() ? 'text-danger' : '';
                return '<span class="' . $class . '">' . $invoice->due_date->format('M d, Y') . '</span>';
            })
            ->addColumn('paid_amount', function ($invoice) {
                $paid = $invoice->transactions()->where('status', 'completed')->sum('amount');
                $remaining = $invoice->total_amount - $paid;

                if ($paid >= $invoice->total_amount) {
                    return '<span class="badge bg-success">Fully Paid</span>';
                } elseif ($paid > 0) {
                    return '<span class="badge bg-warning">$' . number_format($remaining, 2) . ' remaining</span>';
                }
                return '<span class="badge bg-secondary">Unpaid</span>';
            })
            ->addColumn('actions', function ($invoice) {
                $actions = '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-item" data-id="' . $invoice->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-item" data-id="' . $invoice->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';

                if ($invoice->status !== 'paid') {
                    $actions .= '
                        <button class="btn btn-sm btn-success mark-paid" data-id="' . $invoice->id . '" title="Mark as Paid">
                            <i class="fas fa-check"></i>
                        </button>';
                }

                $actions .= '
                        <button class="btn btn-sm btn-danger delete-item" data-id="' . $invoice->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';

                return $actions;
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'overdue') {
                    $query->where('status', 'sent')->where('due_date', '<', now());
                } else {
                    $query->where('status', $keyword);
                }
            })
            ->rawColumns(['invoice_number_display', 'customer', 'amount', 'status', 'due_date_display', 'paid_amount', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:draft,sent,paid,cancelled',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Calculate totals
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['tax_amount'] = $validated['subtotal'] * ($validated['tax_rate'] / 100);
        $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'] - $validated['discount_amount'];

        $invoice = Invoice::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully!',
                'data' => $invoice->load('user'),
            ]);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice created successfully!');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice, Request $request)
    {
        $invoice->load(['user', 'transactions.paymentMethod']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $invoice,
            ]);
        }

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $invoice,
            ]);
        }

        $users = User::all();
        return view('admin.invoices.edit', compact('invoice', 'users'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:draft,sent,paid,cancelled',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Calculate totals
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;
        $validated['tax_amount'] = $validated['subtotal'] * ($validated['tax_rate'] / 100);
        $validated['total_amount'] = $validated['subtotal'] + $validated['tax_amount'] - $validated['discount_amount'];

        // Set paid_date if marking as paid
        if ($validated['status'] === 'paid' && $invoice->status !== 'paid') {
            $validated['paid_date'] = now();
        }

        $invoice->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully!',
                'data' => $invoice->load('user'),
            ]);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice, Request $request)
    {
        // Check if invoice has transactions
        if ($invoice->transactions()->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete invoice with existing transactions.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Cannot delete invoice with existing transactions.');
        }

        $invoice->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully!',
            ]);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully!');
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice, Request $request)
    {
        $invoice->markAsPaid();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid!',
            ]);
        }

        return redirect()->back()->with('success', 'Invoice marked as paid!');
    }

    /**
     * Send invoice to customer.
     */
    public function send(Invoice $invoice, Request $request)
    {
        // TODO: Implement email sending logic

        $invoice->update(['status' => 'sent']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice sent to customer!',
            ]);
        }

        return redirect()->back()->with('success', 'Invoice sent to customer!');
    }
}
