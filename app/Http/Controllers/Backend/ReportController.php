<?php

namespace App\Http\Controllers\Backend;

use App\Models\Report;
use App\Models\Property;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportController extends BaseController
{
    protected string $resource = 'report';

    /**
     * Get database-agnostic date format expression
     */
    private function getDateFormat(string $column = 'created_at', string $format = 'day'): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return match ($format) {
                'hour' => "strftime('%Y-%m-%d %H:00', $column)",
                'day' => "strftime('%Y-%m-%d', $column)",
                'month' => "strftime('%Y-%m', $column)",
                default => "strftime('%Y-%m-%d', $column)",
            };
        }

        // MySQL/MariaDB
        return match ($format) {
            'hour' => "DATE_FORMAT($column, '%Y-%m-%d %H:00')",
            'day' => "DATE_FORMAT($column, '%Y-%m-%d')",
            'month' => "DATE_FORMAT($column, '%Y-%m')",
            default => "DATE_FORMAT($column, '%Y-%m-%d')",
        };
    }

    /**
     * Display reports dashboard.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        // Quick stats
        $stats = $this->getDashboardStats();

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = Report::with(['creator']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name_display', function ($report) {
                $desc = $report->description ? '<small class="text-muted d-block">' . \Illuminate\Support\Str::limit($report->description, 50) . '</small>' : '';
                return '<strong>' . $report->name . '</strong>' . $desc;
            })
            ->addColumn('type_badge', function ($report) {
                return $report->type_badge;
            })
            ->addColumn('creator_name', function ($report) {
                return $report->creator ? $report->creator->name : '<span class="text-muted">System</span>';
            })
            ->addColumn('schedule_display', function ($report) {
                if ($report->schedule) {
                    return '<span class="badge bg-info">' . ucfirst($report->schedule) . '</span>';
                }
                return '<span class="text-muted">Manual</span>';
            })
            ->addColumn('status', function ($report) {
                $class = $report->is_active ? 'bg-success' : 'bg-danger';
                $text = $report->is_active ? 'Active' : 'Inactive';
                return '<span class="badge ' . $class . '">' . $text . '</span>';
            })
            ->addColumn('last_run', function ($report) {
                return $report->last_generated_at ? $report->last_generated_at->diffForHumans() : 'Never';
            })
            ->addColumn('actions', function ($report) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary run-report" data-id="' . $report->id . '" title="Run Report">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-sm btn-info view-item" data-id="' . $report->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-item" data-id="' . $report->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" data-id="' . $report->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['name_display', 'type_badge', 'creator_name', 'schedule_display', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Sales Report page
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        // Sales data
        $salesData = $this->getSalesDataForRange($startDate, $endDate);

        // Invoice data
        $invoiceData = $this->getInvoiceData($startDate, $endDate);

        // Chart data
        $chartData = $this->getSalesChartDataForRange($startDate, $endDate);

        // Status breakdown
        $statusBreakdown = [
            'completed' => Transaction::where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending' => Transaction::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'failed' => Transaction::whereIn('status', ['failed', 'cancelled'])->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // Top properties
        $topProperties = Property::withCount('inquiries')
            ->orderByDesc('views')
            ->take(5)
            ->get();

        // Recent transactions
        $recentTransactions = Transaction::with(['user', 'paymentMethod'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Payment method stats
        $paymentMethodStats = \App\Models\PaymentMethod::withCount('transactions')
            ->withSum('transactions', 'amount')
            ->where('is_active', true)
            ->get();

        return view('admin.reports.sales-report', compact(
            'salesData',
            'invoiceData',
            'chartData',
            'statusBreakdown',
            'topProperties',
            'recentTransactions',
            'paymentMethodStats'
        ));
    }

    /**
     * Get sales data for AJAX request
     */
    public function getSalesData(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        return response()->json([
            'success' => true,
            'data' => [
                'salesData' => $this->getSalesDataForRange($startDate, $endDate),
                'invoiceData' => $this->getInvoiceData($startDate, $endDate),
                'chartData' => $this->getSalesChartDataForRange($startDate, $endDate),
                'statusBreakdown' => [
                    'completed' => Transaction::where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])->count(),
                    'pending' => Transaction::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
                    'failed' => Transaction::whereIn('status', ['failed', 'cancelled'])->whereBetween('created_at', [$startDate, $endDate])->count(),
                ],
            ],
        ]);
    }

    /**
     * Export sales report
     */
    public function exportSales(Request $request)
    {
        // TODO: Implement export logic
        return response()->json([
            'success' => true,
            'message' => 'Export functionality coming soon!',
        ]);
    }

    /**
     * Analytics page
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        // User analytics
        $userAnalytics = $this->getUserAnalyticsData();

        // Property analytics
        $propertyAnalytics = $this->getPropertyAnalyticsData();

        // Traffic/views analytics
        $viewsAnalytics = $this->getViewsAnalyticsData();

        // Inquiry analytics
        $inquiryAnalytics = $this->getInquiryAnalyticsData();

        // Chart data for all analytics
        $chartData = $this->getAnalyticsChartData($startDate, $endDate);

        // Top viewed properties
        $topViewedProperties = Property::orderByDesc('views')
            ->take(5)
            ->get();

        // Most inquired properties
        $topInquiredProperties = Property::withCount('inquiries')
            ->orderByDesc('inquiries_count')
            ->take(5)
            ->get();

        return view('admin.reports.analytics', compact(
            'userAnalytics',
            'propertyAnalytics',
            'viewsAnalytics',
            'inquiryAnalytics',
            'chartData',
            'topViewedProperties',
            'topInquiredProperties'
        ));
    }

    /**
     * Get analytics data for AJAX request
     */
    public function getAnalyticsData(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->subDays(30);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        return response()->json([
            'success' => true,
            'data' => [
                'userAnalytics' => $this->getUserAnalyticsData(),
                'propertyAnalytics' => $this->getPropertyAnalyticsData(),
                'viewsAnalytics' => $this->getViewsAnalyticsData(),
                'inquiryAnalytics' => $this->getInquiryAnalyticsData(),
                'chartData' => $this->getAnalyticsChartData($startDate, $endDate),
            ],
        ]);
    }

    /**
     * Store a new saved report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,analytics,property,user,transaction',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'schedule' => 'nullable|in:daily,weekly,monthly',
            'email_recipients' => 'nullable|string',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_active'] = $request->boolean('is_active', true);

        $report = Report::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report created successfully!',
                'data' => $report,
            ]);
        }

        return redirect()->route('admin.reports.index')->with('success', 'Report created successfully!');
    }

    /**
     * Show report details
     */
    public function show(Report $report, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $report->load('creator'),
            ]);
        }

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Edit report
     */
    public function edit(Report $report, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        }

        return view('admin.reports.edit', compact('report'));
    }

    /**
     * Update report
     */
    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sales,analytics,property,user,transaction',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'schedule' => 'nullable|in:daily,weekly,monthly',
            'email_recipients' => 'nullable|string',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_active'] = $request->boolean('is_active');

        $report->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully!',
                'data' => $report,
            ]);
        }

        return redirect()->route('admin.reports.index')->with('success', 'Report updated successfully!');
    }

    /**
     * Delete report
     */
    public function destroy(Report $report, Request $request)
    {
        $report->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully!',
            ]);
        }

        return redirect()->route('admin.reports.index')->with('success', 'Report deleted successfully!');
    }

    /**
     * Run/Generate a report
     */
    public function run(Report $report, Request $request)
    {
        $report->update(['last_generated_at' => now()]);

        // Generate report data based on type
        $data = $this->generateReportData($report);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully!',
                'data' => $data,
            ]);
        }

        return redirect()->back()->with('success', 'Report generated successfully!');
    }

    /**
     * Export report
     */
    public function export(Report $report, Request $request)
    {
        $format = $request->get('format', 'csv');
        $data = $this->generateReportData($report);

        // TODO: Implement actual export logic (CSV, PDF, Excel)

        return response()->json([
            'success' => true,
            'message' => 'Report exported successfully!',
            'download_url' => '#',
        ]);
    }

    // ========== Helper Methods ==========

    private function getDashboardStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_revenue' => Transaction::where('status', 'completed')
                ->where('type', 'payment')
                ->sum('amount'),
            'monthly_revenue' => Transaction::where('status', 'completed')
                ->where('type', 'payment')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total_properties' => Property::count(),
            'active_properties' => Property::where('is_available', true)->count(),
            'total_users' => User::count(),
            'new_users_this_month' => User::where('created_at', '>=', $thisMonth)->count(),
            'total_inquiries' => Inquiry::count(),
            'pending_inquiries' => Inquiry::where('status', 'new')->count(),
            'total_invoices' => Invoice::count(),
            'unpaid_invoices' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
        ];
    }

    private function getStartDate(string $range): Carbon
    {
        return match ($range) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }

    private function getSalesDataForRange(Carbon $startDate, Carbon $endDate): array
    {
        $transactions = Transaction::where('status', 'completed')
            ->where('type', 'payment')
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_revenue' => (clone $transactions)->sum('amount') ?? 0,
            'total_transactions' => (clone $transactions)->count(),
            'average_transaction' => (clone $transactions)->avg('amount') ?? 0,
            'total_fees' => (clone $transactions)->sum('fee') ?? 0,
        ];
    }

    private function getInvoiceData(Carbon $startDate, Carbon $endDate): array
    {
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => (clone $invoices)->count(),
            'pending' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
            'total_amount' => (clone $invoices)->sum('total_amount') ?? 0,
            'paid_amount' => Invoice::where('status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount') ?? 0,
        ];
    }

    private function getSalesChartDataForRange(Carbon $startDate, Carbon $endDate): array
    {
        $dateExpr = $this->getDateFormat('created_at', 'day');

        $data = Transaction::where('status', 'completed')
            ->where('type', 'payment')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("$dateExpr as date"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->toArray(),
            'revenue' => $data->pluck('total')->map(fn($v) => (float)$v)->toArray(),
            'transactions' => $data->pluck('count')->toArray(),
        ];
    }

    private function getUserAnalyticsData(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $newThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        $newLastMonth = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();

        $growthRate = $newLastMonth > 0 ? round((($newThisMonth - $newLastMonth) / $newLastMonth) * 100, 1) : 0;

        return [
            'total' => User::count(),
            'active' => User::where('is_verified', true)->count(),
            'new_this_month' => $newThisMonth,
            'with_properties' => User::has('properties')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'growth_rate' => $growthRate,
        ];
    }

    private function getPropertyAnalyticsData(): array
    {
        return [
            'total' => Property::count(),
            'available' => Property::where('is_available', true)->count(),
            'for_sale' => Property::where('listing_type', 'For Sale')->count(),
            'for_rent' => Property::where('listing_type', 'For Rent')->count(),
            'featured' => Property::where('is_featured', true)->count(),
            'total_value' => Property::sum('price') ?? 0,
        ];
    }

    private function getViewsAnalyticsData(): array
    {
        return [
            'total_views' => Property::sum('views') ?? 0,
            'today' => 0, // Would need a views tracking table
            'this_week' => 0,
            'this_month' => 0,
            'average_per_property' => (int) (Property::avg('views') ?? 0),
        ];
    }

    private function getInquiryAnalyticsData(): array
    {
        $total = Inquiry::count();
        $resolved = Inquiry::whereIn('status', ['replied', 'resolved'])->count();
        $responseRate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'pending' => Inquiry::where('status', 'new')->count(),
            'in_progress' => Inquiry::where('status', 'in_progress')->count(),
            'resolved' => $resolved,
            'this_month' => Inquiry::where('created_at', '>=', now()->startOfMonth())->count(),
            'response_rate' => $responseRate,
        ];
    }

    private function getAnalyticsChartData(Carbon $startDate, Carbon $endDate): array
    {
        $dateExpr = $this->getDateFormat('created_at', 'day');

        // User registrations by day
        $userRegistrations = User::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("$dateExpr as date"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // User roles distribution
        $userRoles = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('roles.title', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.title')
            ->get();

        // Property types
        $propertyTypes = Property::select('property_type', DB::raw('COUNT(*) as count'))
            ->groupBy('property_type')
            ->get();

        // Property cities
        $propertyCities = Property::select('city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        // Inquiry trend
        $inquiryTrend = Inquiry::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("$dateExpr as date"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Inquiry status distribution
        $inquiryStatus = [
            Inquiry::where('status', 'new')->count(),
            Inquiry::where('status', 'in_progress')->count(),
            Inquiry::whereIn('status', ['replied', 'resolved'])->count(),
            Inquiry::where('status', 'closed')->count(),
        ];

        return [
            'userRegistrations' => [
                'labels' => $userRegistrations->pluck('date')->toArray(),
                'data' => $userRegistrations->pluck('count')->toArray(),
            ],
            'userRoles' => [
                'labels' => $userRoles->pluck('title')->toArray(),
                'data' => $userRoles->pluck('count')->toArray(),
            ],
            'propertyTypes' => [
                'labels' => $propertyTypes->pluck('property_type')->toArray(),
                'data' => $propertyTypes->pluck('count')->toArray(),
            ],
            'propertyCities' => [
                'labels' => $propertyCities->pluck('city')->toArray(),
                'data' => $propertyCities->pluck('count')->toArray(),
            ],
            'views' => [
                'labels' => $userRegistrations->pluck('date')->toArray(), // Placeholder
                'data' => [], // Would need views tracking
            ],
            'inquiryTrend' => [
                'labels' => $inquiryTrend->pluck('date')->toArray(),
                'data' => $inquiryTrend->pluck('count')->toArray(),
            ],
            'inquiryStatus' => $inquiryStatus,
        ];
    }

    private function generateReportData(Report $report): array
    {
        $filters = $report->filters ?? [];
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->subMonth();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now();

        return match ($report->type) {
            'sales' => $this->getSalesDataForRange($startDate, $endDate),
            'analytics' => [
                'users' => $this->getUserAnalyticsData(),
                'properties' => $this->getPropertyAnalyticsData(),
            ],
            'property' => $this->getPropertyAnalyticsData(),
            'user' => $this->getUserAnalyticsData(),
            'transaction' => $this->getSalesDataForRange($startDate, $endDate),
            default => [],
        };
    }
}
