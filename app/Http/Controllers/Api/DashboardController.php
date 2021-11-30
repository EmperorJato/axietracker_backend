<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ScholarshipTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Scholarship;

class DashboardController extends Controller
{
    use ScholarshipTrait;

    public function index()
    {
        $yesterday = Carbon::parse('yesterday 8am')->format('Y-m-d H:i:s');
        $today = Carbon::parse('today 8am')->format('Y-m-d H:i:s');
        $daily_stats = DB::table('daily_scholarship')->where('user_id', Auth::user()->id)->orderBy('datetime', 'desc')->get();
        $yesterday_stats = DB::table('daily_scholarship')->where('user_id', Auth::user()->id)->whereBetween('datetime', [$yesterday, $today])->first();

        $scholarship = Scholarship::where('scholar_id', Auth::user()->id)->first();
        
        $earnings = null;

        if(Auth::user()->roles->pluck('slug')->first() === 'manager'){
            $underManager = Scholarship::where('manager_id', $scholarship->manager_id)->get();

            foreach($underManager as $item){
                $earnings[] = $this->earnings($item->manager_ronin);
            }

            foreach($earnings as $item){
                $slp_inventory[] = $item['slp_inventory'];
                $slp_ronin[] = $item['slp_ronin'];
                $slp_total[] = $item['slp_total'];
            }

            $earning_stats = [
                'slp_inventory' => array_sum($slp_inventory),
                'slp_ronin' => array_sum($slp_ronin),
                'slp_total' => array_sum($slp_total)
            ];

        } else {

            $earning_stats = $this->earnings($scholarship->manager_ronin);

        }

        return response()->json(['daily_stats' => $daily_stats, 'yesterday_stats' => $yesterday_stats, 'earning_stats' => $earning_stats]);
    }

    public function resultStats()
    {
        return $this->doneDaily();
    }
}
