<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\ScholarshipTrait;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class ScholarshipController extends Controller
{
    use ScholarshipTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $showScholarship = Scholarship::where('manager_id', Auth::user()->id)->get();

        if (!$showScholarship->isEmpty()) {

            foreach ($showScholarship as $item) {
                $scholarship[] = $this->scholarship($item);
            }

            return $scholarship;
        }

        return [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($request)
    {
        if (Str::substr($request['manager_ronin'], 0, 6) !== 'ronin:') {

            throw ValidationException::withMessages([
                'manager_ronin' => 'Invalid Ronin Address'
            ]);
        }

        if ($request['scholar_id'] == '') {
            $scholar_id = Auth::user()->id;
        } else {
            $scholar_id = $request['scholar_id'];
        }

        return Scholarship::create([
            'manager_id' => Auth::user()->id,
            'scholar_id' =>  $scholar_id,
            'name' => $request['name'],
            'manager_ronin' => $request['manager_ronin'],
            'rate' => $request['rate'],
            'private_key' => Crypt::encryptString($request['key'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $scholar = $this->create($request->all());

        if ($scholar) {
            User::where('id', $request->scholar_id)->update(['active' => 1]);

            return response()->json(['message' => 'Created Successfully', 'scholar' => $scholar]);
        }

        return response()->json(['message' => 'Failed to create scholar. Please try again'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $scholarship = Scholarship::find($id);

        $scholarship->name = $request->name;

        $scholarship->rate = $request->rate;

        $scholarship->private_key = Crypt::encryptString($request->key);

        if($scholarship->save()){
            return response()->json(['message' => 'Updated Successfully', 'scholarship' => $scholarship]);
        }

        return response()->json(['message' => 'Oops Something went wrong.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    } 

    public function applyingScholarship()
    {

        $scholarship = Scholarship::where('id', Auth::user()->id)->first();

        return User::where('active', 0)->get();
    }

    private function scholarship($profile)
    {

        $yesterday =  DB::table('daily_scholarship')->where('scholarship_id', $profile->id)->whereDate('utc_datetime', Carbon::yesterday())->first();
        $today =  DB::table('daily_scholarship')->where('scholarship_id', $profile->id)->whereDate('utc_datetime', Carbon::today())->first();
        $average_slp =  DB::table('daily_scholarship')->where('scholarship_id', $profile->id)->avg('slp');
        $token_expired = false;

        if(Carbon::parse($profile->updated_token)->addDays(14) <= Carbon::now('Asia/Manila')){
            $token_expired = true;
        }

        $data = [
            'scholarship_id' => $profile->id,
            'scholar_id' => $profile->scholar_id,
            'name' => $profile->name,
            'daily' => empty($today) ? 0 : $today->reward,
            'energy' => empty($today) ? 'Full' : $today->energy,
            'mmr' => empty($today) ? $yesterday->mmr : $today->mmr,
            'yesterday_slp' => $yesterday->slp,
            'today_slp' => empty($today) ? 0 : $today->slp,
            'average_slp' => round($average_slp),
            'rate' => $profile->rate,
            'inventory' => empty($today) ? $yesterday->slp_inventory : $today->slp_inventory,
            'token_expired' => $token_expired
        ];

        return $data;
    }

    public function scholars(){
        $scholarships = Scholarship::where('manager_id', Auth::user()->id)->get();
        $total_scholar = count($scholarships);

        if (!$scholarships->isEmpty()) {
            foreach ($scholarships as $item) {
                $axies[] = $this->axies($item->manager_ronin)['data']['axies']['total'];
                $slp[] = $this->earnings($item->manager_ronin)['slp_inventory'];
                $scholarship_id[] = $item->id;
            }
        }
        $daily = DB::table('daily_scholarship')->whereIn('scholarship_id', $scholarship_id)->whereDate('utc_datetime', Carbon::today())->get();
        $total_slp_yesterday = DB::table('daily_scholarship')->whereIn('scholarship_id', $scholarship_id)->whereDate('utc_datetime', Carbon::yesterday())->sum('slp');

        if($daily->isEmpty()){
            $scholar[] = [
                'user' => 'No activities yet',
                'win' => 0,
                'draw' => 0,
                'lose' => 0,
                'slp' => 0,
            ];
        }
        foreach($daily as $item){
            $lose = $item->pvp_total - $item->pvp - $item->draw;
            $user = User::where('id', $item->user_id)->value('name');

            $scholar[] = [
                'user' => $user,
                'win' => $item->pvp,
                'draw' => $item->draw,
                'lose' => $lose,
                'slp' => $item->slp,
            ];
        }
        $today_top_scholar = $scholar[0];
        foreach($scholar as $item){
            $scholar_slp = $item['slp'];
            if($scholar_slp > $today_top_scholar['slp']){
                $today_top_scholar = $item;
            }
        }

        $data = [
            'total_scholar' => $total_scholar,
            'total_axies' => array_sum($axies),
            'total_unclaimed' => array_sum($slp),
            'total_slp_today' => array_sum(array_column($scholar, 'slp')),
            'total_slp_yesterday' => (int)$total_slp_yesterday,
            'today_top_scholar' => $today_top_scholar
        ];

        return $data;
    }

    public function getDataToday(){
        $scholarship = Scholarship::all();

        foreach($scholarship as $scholar){

            $scholars[] = $scholar;
            
        }

        foreach($scholars as $items){
            $item[] = $items;
            $earnings = $this->earnings($items->manager_ronin);
            $battles = $this->battles($items->manager_ronin, 100)['items'];
            $mmr = $this->leaderboards($items->manager_ronin, 0)['items'][1]['elo'];
            $yesterday_slp_inventory = DB::table('daily_scholarship')->where('scholarship_id',  $items->id)->whereDate('utc_datetime', Carbon::yesterday())->value('slp_inventory');

            $slp = $earnings['slp_inventory'] - $yesterday_slp_inventory;

            $toMetamask = str_replace('ronin:', '0x', $items->manager_ronin);
            $pvp_battles = [];
            $pvp = [];
            $draw = [];
            foreach ($battles as $item) {
                if ($item['battle_type'] === 0 && $item['created_at'] >= Carbon::today('UTC')->format('Y-m-d H:i:s')) {
                    $pvp_battles[] = $item;
                }
            }
    
            foreach ($pvp_battles as $item) {
                if ($item['first_client_id'] === strtolower($toMetamask) && $item['winner'] === 0) {
                    $pvp[] = $item;
                } else if ($item['second_client_id'] === strtolower($toMetamask) && $item['winner'] === 1) {
                    $pvp[] = $item;
                }
    
                if ($item['first_client_id'] === strtolower($toMetamask) && $item['winner'] === 2) {
                    $draw[] = $item;
                } else if ($item['second_client_id'] === strtolower($toMetamask) && $item['winner'] === 2) {
                    $draw[] = $item;
                }
            }
            
            if ($items->access_token) {
                $stats = $this->stats($items->manager_ronin, $items->access_token);
                $quests = $this->quests($items->manager_ronin, $items->access_token);
                $energy = $stats['remaining_energy'];
                $reward = $quests['reward'];
            } else {
                return response()->json(['message' => 'No Token Found']);
            }

            

            $dateToday = DB::table('daily_scholarship')->where('scholarship_id',  $items->id)->whereDate('utc_datetime', Carbon::today())->first();
            
            if (empty($dateToday)) {
                $data  = [
                    'user_id' => $items->scholar_id,
                    'scholarship_id' => $items->id,
                    'slp' => $slp,
                    'slp_inventory' => $earnings['slp_inventory'],
                    'pvp' => count($pvp),
                    'draw' => count($draw),
                    'pvp_total' => count($pvp_battles),
                    'energy' => $energy,
                    'reward' => $reward,
                    'mmr' => $mmr,
                    'datetime' => Carbon::now()->timezone('Asia/Manila'),
                    'utc_datetime' => Carbon::now(),
                ];
                DB::table('daily_scholarship')->insert($data);
            } else {
                tap(DB::table('daily_scholarship')
                    ->where('user_id', $items->scholar_id)->whereDate('utc_datetime', Carbon::today()))
                    ->update([
                        'user_id' => $items->scholar_id,
                        'scholarship_id' => $items->id,
                        'slp' => $slp,
                        'slp_inventory' => $earnings['slp_inventory'],
                        'pvp' => count($pvp),
                        'draw' => count($draw),
                        'pvp_total' => count($pvp_battles),
                        'energy' => $energy,
                        'reward' => $reward,
                        'mmr' => $mmr,
                        'datetime' => Carbon::now()->timezone('Asia/Manila'),
                        'utc_datetime' => Carbon::now(),
                    ])->first();
            }

        }

    }

    public function updateToken(Request $request){
        $scholarship = Scholarship::where('scholar_id', Auth::user()->id)->first();
        $scholarship->access_token = $request->token;
        $scholarship->updated_token = Carbon::now('Asia/Manila');
        if($scholarship->save()){
            return response()->json(['message' => 'Token Updated Successfully'], 200);
        }

        return response()->json(['message' => 'Something went wrong.'], 500);
    }

}
