<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllTransactionReportResource;
use App\Http\Resources\DailyReportResource;
use App\Http\Resources\DashboardDailyReportResource;
use App\Http\Resources\DashboardRecentTransactionResource;
use App\Http\Resources\DashboardWeeklyReportResource;
use App\Http\Resources\DashboardYearlyReportResource;
use App\Http\Resources\ExportCustomResource;
use App\Http\Resources\ExportCustomWeekMonthResource;
use App\Http\Resources\ExportCustomYearlyResource;
use App\Http\Resources\MonthlyReportResource;
use App\Http\Resources\WeeklyReportResource;
use App\Http\Resources\YearlyReportResource;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboardDailyReport()
    {
        $currentDate = Carbon::now()->toDateString();
    
        $getDaily = Order::selectRaw("
                SUM(total_price) AS order_total,
                COUNT(id) AS total_transaction,
                TO_CHAR(created_at, 'YYYY-MM-DD') AS order_date
            ")
            ->where('status', 2)
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM-DD')")
            ->havingRaw("TO_CHAR(created_at, 'YYYY-MM-DD') = ?", [$currentDate])
            ->first();
    
        if (!$getDaily) {
            return response()->json([
                'data' => [
                    'order_total' => 0,
                    'total_transaction' => 0,
                    'order_date' => $currentDate,
                ]
            ]);
        }
    
        return new DashboardDailyReportResource($getDaily);
        // return response()->json(['data' => $getDaily]);
    }
    

    public function dashboardWeeklyReport()
    {
        $currentDate = Carbon::now()->addDay();
        $currentDateString = $currentDate->toDateString();
        $lastWeek = $currentDate->subWeek()->toDateString();
    
        $getWeekly = Order::selectRaw("
                SUM(total_price) AS order_total,
                COUNT(id) AS total_transaction,
                TO_CHAR(created_at, 'YYYY-MM-DD') AS order_date
            ")
            ->where('status', 2)
            ->groupBy('order_date')
            ->havingRaw("TO_CHAR(created_at, 'YYYY-MM-DD') >= ?", [$lastWeek])
            ->havingRaw("TO_CHAR(created_at, 'YYYY-MM-DD') <= ?", [$currentDateString])
            ->get();
    
        return DashboardWeeklyReportResource::collection($getWeekly);
    }
    
    public function dasboardYearlyReport()
    {
        $currentYear = Carbon::now()->format('Y');
    
        $getYearly = Order::selectRaw("
                SUM(total_price) AS order_total,
                MIN(created_at) AS first_trans,
                MAX(created_at) AS last_trans,
                EXTRACT(YEAR FROM created_at) AS tahun
            ")
            ->where('status', 2)
            ->groupByRaw("EXTRACT(YEAR FROM created_at)")
            ->havingRaw("EXTRACT(YEAR FROM created_at) = ?", [$currentYear])
            ->first();
    
        if (!$getYearly) {
            return response()->json([
                'data' => [
                    'order_total' => 0,
                    'bulan' => 'no transaction yet',
                    'tahun' => $currentYear,
                ]
            ]);
        }
    
        return new DashboardYearlyReportResource($getYearly);
    }

    public function dashboardRecentTransaction()
    {
        $getRecent = Order::selectRaw('sum(total_price) as order_total, employee_id, order_code, created_at')
            ->with('employee')
            ->where('status', 2)
            ->groupBy('employee_id', 'order_code', 'created_at')
            ->limit(6)
            ->orderBy('created_at', 'desc')
            ->get();
        return DashboardRecentTransactionResource::collection($getRecent);
    }

    public function dailyReport()
    {
        $getDaily = Order::selectRaw("total_price,order_code,employee_id")
            ->with('employee')
            ->where('status', 2)
            ->where('created_at', '>=', Carbon::today()->toDateString())->get();
        return DailyReportResource::collection($getDaily);
    }

    // public function weeklyReport()
    // {
    //     $currentDate = Carbon::now()->addDay();
    //     $currentDateString = $currentDate->toDateString();
    //     $lastWeek = $currentDate->subWeek()->toDateString();
    //     $getWeekly = Order::selectRaw("sum(total_price) as order_total,count(id) as total_transaction, TO_CHAR(created_at, 'YYYY-MM-DD') as order_date")
    //         ->where('status', 2)
    //         ->groupBy('order_date')
    //         ->having('order_date', '>=', $lastWeek)
    //         ->having('order_date', '<=', $currentDateString)
    //         ->get();
    //     return WeeklyReportResource::collection($getWeekly);
    // }
    public function weeklyReport()
    {
        $currentDate = Carbon::now()->addDay();
        $currentDateString = $currentDate->toDateString();
        $lastWeek = $currentDate->subWeek()->toDateString();
    
        $getWeekly = Order::selectRaw("sum(total_price) as order_total, count(id) as total_transaction, TO_CHAR(created_at, 'YYYY-MM-DD') as order_date")
            ->where('status', 2)
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM-DD')")
            ->havingRaw('TO_CHAR(created_at, \'YYYY-MM-DD\') >= ?', [$lastWeek])
            ->havingRaw('TO_CHAR(created_at, \'YYYY-MM-DD\') <= ?', [$currentDateString])
            ->get();
    
        return WeeklyReportResource::collection($getWeekly);
    }
    
    // public function monthlyReport()
    // {
    //     $getMonthly = Order::selectRaw("sum(total_price) as order_total,count(id) as total_transaction, TO_CHAR(created_at, 'YYYY-MM-DD') as order_date, MONTH(created_at) as bulan")
    //         ->where('status', 2)
    //         ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM-DD')")
    //         ->having('bulan', date('m'))
    //         ->get();
    //     return MonthlyReportResource::collection($getMonthly);
    // }

    public function monthlyReport()
    {
        $getMonthly = Order::selectRaw("sum(total_price) as order_total, count(id) as total_transaction, TO_CHAR(created_at, 'YYYY-MM-DD') as order_date, EXTRACT(MONTH FROM created_at) as bulan")
            ->where('status', 2)
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM-DD'), EXTRACT(MONTH FROM created_at)")
            ->havingRaw('EXTRACT(MONTH FROM created_at) = ?', [date('m')])
            ->get();

        return MonthlyReportResource::collection($getMonthly);
    }


    // public function yearlyReport()
    // {
    //     $getYearly = Order::selectRaw("sum(total_price) as order_total, MIN(created_at) as first_trans , MAX(created_at) as last_trans,MONTH(created_at) as bulan, YEAR(created_at) as tahun, count(id) as total_transaction")
    //         ->where('status', 2)
    //         ->groupBy('bulan')
    //         ->having('tahun', date('Y'))
    //         ->get();
    //     return YearlyReportResource::collection($getYearly);
    // }

    public function yearlyReport()
    {
        $getYearly = Order::selectRaw("
                sum(total_price) as order_total, 
                MIN(created_at) as first_trans, 
                MAX(created_at) as last_trans,
                EXTRACT(MONTH FROM created_at) as bulan, 
                EXTRACT(YEAR FROM created_at) as tahun, 
                count(id) as total_transaction
            ")
            ->where('status', 2)
            ->groupByRaw("EXTRACT(MONTH FROM created_at), EXTRACT(YEAR FROM created_at)")
            ->havingRaw('EXTRACT(YEAR FROM created_at) = ?', [date('Y')])
            ->get();

        return YearlyReportResource::collection($getYearly);
    }


    public function allTransactionReport(Request $request)
    {
        $getAll = Order::selectRaw("sum(total_price) as order_total, order_number, employee_id, order_code, created_at, id, discount_percentage, discount_value, cash, change, total_price")
            ->with(['employee', 'details.product'])
            ->where('status', 2)
            ->groupBy('created_at', 'order_number', 'employee_id', 'order_code', 'id', 'discount_percentage', 'discount_value', 'cash', 'change', 'total_price')
            ->orderBy('created_at', 'desc');

        if ($request->filter) {
            $getAll = $getAll->where('order_code', 'like', "%$request->filter%")->orWhere('total_price', 'like', "%$request->filter%");
        }

        if ($request->fromdate) {
            $fromdate = $request->fromdate;
            if ($request->fromdate && $request->todate) {
                $todate = Carbon::parse($request->todate)->addDay();
                $getAll = $getAll->whereBetween('created_at', [$fromdate . '%', $todate . '%']);
            } else {
                $getAll = $getAll->whereDate('created_at', '=', "$fromdate");
            }
        }

        if ($request->has('per_page')) {
            $perPage = 5;
            if ($request->per_page) {
                $perPage = $request->per_page;
                $getAll = $getAll->paginate($perPage);
            }
        } else {
            $getAll = $getAll->get();
        }

        return AllTransactionReportResource::collection($getAll);
    }


    public function exportReport(Request $request)
    {
        $getAll = Order::selectRaw("sum(total_price) as order_total, order_number, employee_id, order_code, created_at, id, discount_percentage, discount_value, cash, `change`, total_price")
            ->with(['employee', 'details.product'])
            ->where('status', 2)
            ->groupBy('created_at')
            ->orderBy('created_at', 'desc');
        if ($request->fromdate) {
            $fromdate = $request->fromdate;
            if ($request->fromdate && $request->todate) {
                $fromdate = Carbon::parse($request->fromdate)->subDays(1);
                $todate = Carbon::parse($request->todate);
                $gap = $fromdate->diffInDays($todate);
                if ($gap < 32) {
                    $smallGap = Order::selectRaw("sum(total_price) as order_total,count(id) as total_transaction, (DATE_FORMAT(created_at,'%Y-%m-%d')) as order_date")
                        ->where('status', 2)
                        ->groupBy('order_date')
                        ->having('order_date', ">=", $fromdate)
                        ->having('order_date', "<=", $todate)
                        ->get();
                    return ExportCustomWeekMonthResource::collection($smallGap);
                }
                if ($gap > 32) {
                    $bigGap = Order::selectRaw("sum(total_price) as order_total, (DATE_FORMAT(created_at,'%Y-%m')) as bulan, count(id) as total_transaction, MIN(created_at) as first_trans , MAX(created_at) as last_trans")
                        ->whereBetween('created_at', [$fromdate . '%', $todate . '%'])
                        ->where('status', 2)
                        ->groupBy('bulan')
                        ->get();
                    // return response()->json(['data' => $bigGap, 'gap' => $gap]);
                    // $bigGap->extra = $gap;
                    return ExportCustomYearlyResource::collection($bigGap, $gap);
                    // return new ExportCustomYearlyResource($bigGap, $gap);
                }
            } else {
                $getAll = $getAll->whereDate('created_at', '=', "$fromdate")->get();
                return ExportCustomResource::collection($getAll);
            }
        }
    }
}