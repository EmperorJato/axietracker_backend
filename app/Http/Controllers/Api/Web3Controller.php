<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Web3\Web3;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class Web3Controller extends Controller
{

    public function randomMessage(Request $request){
    
            $response = Http::contentType("application/json")->send('POST', 'https://graphql-gateway.axieinfinity.com/graphql', [
                'body' => '{
                    "operationName":"CreateRandomMessage",
                    "variables":{},
                    "query":"mutation CreateRandomMessage {\n  createRandomMessage\n}\n"
                }'             
            ]);

            if($response->serverError()){
                return response()->json(['message' => 'Oops... Something went wrong.'], 500);
            }

            $scholarship = Scholarship::where('scholar_id', $request->scholar_id)->first();

            if(empty($scholarship->private_key)){
                return response()->json(['message' => 'No Private Key Found.'], 405);
            }

            $signature = [
                'ronin' => $scholarship->manager_ronin,
                'random_message' => $response->json()['data']['createRandomMessage'],
                'key' => Crypt::decryptString($scholarship->private_key)
            ];
    
            return response()->json($signature);
    
    }

    public function signature(Request $request){
        return $this->accessToken(str_replace('ronin:', '0x', $request->roninAddress), str_replace("\n", '\\n', $request->message), $request->signature);
    }

    private function accessToken($address, $message, $signature){

        $response = Http::contentType("application/json")->send('POST', 'https://graphql-gateway.axieinfinity.com/graphql', [
            'body' => '{"operationName":"CreateAccessTokenWithSignature","variables":{"input":{"mainnet":"ronin","owner":"'.$address.'","message":"'.$message.'","signature":"'.$signature.'"}},"query":"mutation CreateAccessTokenWithSignature($input: SignatureInput!) {\n  createAccessTokenWithSignature(input: $input) {\n    newAccount\n    result\n    accessToken\n    __typename\n  }\n}\n"}'
        ]);

        if($response->serverError()){
            return response()->json(['message' => 'Oops... Something went wrong.'], 500);
        }

        return $response['data']['createAccessTokenWithSignature']['accessToken'];

    }
}
