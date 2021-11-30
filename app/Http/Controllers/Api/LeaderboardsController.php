<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use App\Traits\ScholarshipTrait;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LeaderboardsController extends Controller
{
    use ScholarshipTrait;

    public function localRank(){
        $scholarship = Scholarship::where('scholar_id', Auth::user()->id)->first();

        $underManager = Scholarship::where('manager_id', $scholarship->manager_id)->get();

        foreach($underManager as $item){

            $rank = $this->leaderboards($item->manager_ronin, 0)['items'][1];
            $user = User::where('id', $item->scholar_id)->first();

            $localRank[] = collect($rank)->merge(['user' => $user]);
        }

        $collection = collect($localRank)->sortByDesc('elo');

        return $collection->values()->all();
    }

    public function worldRank(){
        return array_slice($this->leaderboards('', 9)['items'], 0, -1);
    }

}
