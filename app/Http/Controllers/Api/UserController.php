<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Models\Scholarship;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ScholarshipTrait;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ChangePasswordRequest;

class UserController extends Controller
{
    use ScholarshipTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(new UserResource(Auth::user()));
    }

    public function userProfile(Request $request)
    {

        return response()->json(new UserResource(User::where('id', $request->id)->first()));
    }

    public function changePassword(ChangePasswordRequest $request, $id)
    {
        if (Auth::id() == $id) {
            $user = User::find(Auth::id());

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'Wrong Old Password'], 401);
            }

            $user->password = Hash::make($request->new_password);

            if ($user->save()) {
                return response()->json(['message' => 'Updated Successfully']);
            }
            return response()->json(['message' => 'Oops... Something went wrong'], 500);
        }
    }

    public function userStats(Request $request)
    {

        //$stats = DB::table('daily_scholarship')->where('user_id',  Auth::user()->id)->whereBetween('utc_datetime', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get();
        $stats = DB::table('daily_scholarship')->where('user_id',  $request->id)->get();

        foreach ($stats as $item) {
            $mmr[] = $item->mmr;
            $total_win[] = $item->pvp;
            $total_draw[] = $item->draw;
            $total_pvp[] = $item->pvp_total;
            $slp[] = $item->slp;
            $rewards[] = $item->reward;
        }

        $userStats = [
            'total_win' => array_sum($total_win),
            'total_lose' => array_sum($total_pvp) - array_sum($total_win) - array_sum($total_draw),
            'total_draw' => array_sum($total_draw),
            'total_pvp' => array_sum($total_pvp),
            'winrate' => round((array_sum($total_win) * 100) / array_sum($total_pvp), 2),
            'highest_mmr' => max($mmr),
            'average_slp' => round(array_sum($slp) / count($slp)),
            'total_absent' => count(array_keys($rewards, 0))
        ];

        $fifteenDays = DB::table('daily_scholarship')->where('user_id', $request->id)->whereBetween('datetime', [Carbon::now('Asia/Manila')->subDays(15), Carbon::now('Asia/Manila')])->get();

        foreach ($fifteenDays as $item) {
            $charts_wins[] = $item->pvp;
            $charts_slps[] = $item->slp;
            $charts_dates[] = Carbon::parse($item->datetime)->format('M/d/Y g:i A');
        }

        $userChart = [
            'wins' => $charts_wins,
            'slps' => $charts_slps,
            'dates' => $charts_dates
        ];

        $scholarship = Scholarship::where('scholar_id', $request->id)->first();

        $battles = $this->battles($scholarship->manager_id, 100);

        return response()->json(['user_stats' => $userStats, 'user_chart' => $userChart, 'battles' => $battles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *  @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
        if (Auth::user()->id == $id) {
            $user = User::find($id);
            $user->name = $request->name;
            $user->metamask_address = $request->metamask_address;
            $user->ronin_address = $request->ronin_address;
            $user->email = $request->email;
            $user->contact_number = $request->contact_number;
            $user->address = $request->address;
            $user->save();

            return response()->json(['message' => 'Updated Successfully', 'user' =>  $user]);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
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


    public function saveImage(Request $request, $id)
    {

        $user = User::find($id);

        if ($request->hasFile('image')) {
            $this->removeImgFromServer($user->image);
            $img_path = Carbon::parse(Carbon::now('Asia/Manila'))->format('Y-m-d') . '_IMG_' . rand() . '-' . $request['image']->getClientOriginalName();
            $request['image']->move(public_path('img'), $img_path);
            $user->image = $img_path;
        }
        if ($user->save()) {
            return response()->json(['message' => 'Updated Successfully', 'image' =>  $user->image]);
        }

        return response()->json(['message' => 'Oops... Something went wrong'], 500);
    }

    public function removeImgFromServer($img)
    {

        $img_path = public_path() . '/img/' . $img;
        if (file_exists($img_path)) {
            @unlink($img_path);
        }

        return;
    }
}
