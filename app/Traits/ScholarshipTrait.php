<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Scholarship;

trait ScholarshipTrait
{

    public function earnings($ronin)
    {

        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::get('https://game-api.skymavis.com/game-api/clients/' . $toMetamask . '/items/1');

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }


        $result = $response->json();

        if ($result['success'] === false) {
            return response()->json(['message' => 'Oops... Something went wrong. Please try again'], 500);
        }

        $inventory = $result['total'] - $result['blockchain_related']['balance'];

        $last_claimed =  Carbon::parse($result['last_claimed_item_at']);


        $earnings = [
            'slp_total' => $result['total'],
            'slp_inventory' => $inventory,
            'slp_ronin' => $result['blockchain_related']['balance'],
            'next_claim' =>  $last_claimed->addDays(14)->format('m/d/Y h:i:s'),
            'last_claimed' =>  $last_claimed->format('M. d, Y h:i:s')
        ];


        return $earnings;
    }

    public function battles($ronin, $limit)
    {
        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::get('https://game-api.skymavis.com/game-api/clients/' . $toMetamask . '/battles?offset=0&limit=' . $limit);

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        $result = $response->json();

        if ($result['success'] === false) {
            return response()->json(['message' => 'Oops... Something went wrong. Please try again'], 500);
        }

        return $result;
    }

    public function leaderboards($ronin, $limit)
    {

        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::get('https://game-api.skymavis.com/game-api/leaderboard?client_id=' . $toMetamask . '&offset=0&limit=' . $limit . '');

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        $result = $response->json();

        if ($result['success'] === false) {
            return response()->json(['message' => 'Oops... Something went wrong. Please try again'], 500);
        }

        return $result;
    }


    public function axies($ronin)
    {

        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::contentType("application/json")->send('POST', 'https://graphql-gateway.axieinfinity.com/graphql', [
            'body' => '{"operationName":"GetAxieBriefList","variables":{"from":0,"size":24,"sort":"IdDesc","auctionType":"All","owner":"' . $toMetamask . '","criteria":{"region":null,"parts":null,"bodyShapes":null,"classes":null,"stages":null,"numMystic":null,"pureness":null,"title":null,"breedable":null,"breedCount":null,"hp":[],"skill":[],"speed":[],"morale":[]}},"query":"query GetAxieBriefList($auctionType: AuctionType, $criteria: AxieSearchCriteria, $from: Int, $sort: SortBy, $size: Int, $owner: String) {\n  axies(auctionType: $auctionType, criteria: $criteria, from: $from, sort: $sort, size: $size, owner: $owner) {\n    total\n    results {\n      ...AxieBrief\n      __typename\n    }\n    __typename\n  }\n}\n\nfragment AxieBrief on Axie {\n  id\n  name\n  stage\n  class\n  breedCount\n  image\n  title\n  battleInfo {\n    banned\n    __typename\n  }\n  auction {\n    currentPrice\n    currentPriceUSD\n    __typename\n  }\n  parts {\n    id\n    name\n    class\n    type\n    specialGenes\n    __typename\n  }\n  __typename\n}\n"}'
        ]);

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        return $response->json();
    }

    public function teams($ronin, $access_token)
    {

        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token

        ])->get('https://game-api.skymavis.com/game-api/clients/' . $toMetamask . '/teams');

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }


        $result = $response->json();

        if ($result['success'] === false) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        return response()->json($result);
    }


    public function stats($ronin, $access_token)
    {


        $toMetamask = str_replace('ronin:', '0x', $ronin);



        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token

        ])->get('https://game-api.skymavis.com/game-api/clients/' . $toMetamask . '/player-stats');

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        $result = $response->json();

        return $result['success'] === false ? ['remaining_energy' => 99 ] : [
            'remaining_energy' => $result['player_stat']['remaining_energy'],
        ];
    }

    public function quests($ronin, $access_token)
    {

        $toMetamask = str_replace('ronin:', '0x', $ronin);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token

        ])->get('https://game-api.skymavis.com/game-api/clients/' . $toMetamask . '/quests');

        if ($response->serverError()) {
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        $result = $response->json();


        return $result['success'] === false ? ['reward' => 99] : ['reward' => $result['items'][0]['claimed'] === null ? 0 : 1 ];
    }

    public function doneDaily()
    {
        $scholarship = Scholarship::where('scholar_id', Auth::user()->id)->first();
        $earnings = $this->earnings($scholarship->manager_ronin);

        $yesterday = DB::table('daily_scholarship')->where('scholarship_id',  $scholarship->id)->whereDate('utc_datetime', Carbon::yesterday())->first();

        if ($yesterday->slp_inventory > $earnings['slp_inventory']) {
            $slp = $earnings['slp_inventory'] - $yesterday->slp;
        } else {
            $slp = $earnings['slp_inventory'] - $yesterday->slp_inventory;
        }


        $battles = $this->battles($scholarship->manager_ronin, 100)['items'];
        $toMetamask = str_replace('ronin:', '0x', $scholarship->manager_ronin);
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

        if ($scholarship->access_token) {
            $stats = $this->stats($scholarship->manager_ronin, $scholarship->access_token);
            $quests = $this->quests($scholarship->manager_ronin, $scholarship->access_token);
            if($stats['remaining_energy'] && $quests['reward'] === 99){
                return response()->json(['message' => 'Invalid Token. Please insert new token.'], 403);
            }
            $energy = $stats['remaining_energy'];
            $reward = $quests['reward'];
        } else {
            return response()->json(['message' => 'No Token Found']);
        }

        $mmr = $this->leaderboards($scholarship->manager_ronin, 0)['items'][1]['elo'];

        $dateToday = DB::table('daily_scholarship')->where('scholarship_id',  $scholarship->id)->whereDate('utc_datetime', Carbon::today())->first();

        if (empty($dateToday)) {
            $data  = [
                'user_id' => Auth::user()->id,
                'scholarship_id' => $scholarship->id,
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
            return $data;
        } else {
            $update = tap(DB::table('daily_scholarship')
                ->where('user_id', Auth::user()->id)->whereDate('utc_datetime', Carbon::today()))
                ->update([
                    'user_id' => Auth::user()->id,
                    'scholarship_id' => $scholarship->id,
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

            return $update;
        }
    }
}
