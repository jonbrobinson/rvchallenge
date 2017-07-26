<?php

namespace App\Http\Controllers;

use App\Aaulyp\Tools\Api\GoogleMapsApi;
use App\City;
use App\User;
use App\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    /**
     * @param string $state
     *
     * @return array
     */
    public function getCities($state)
    {
        $validStates = $this->getDBStateNames();

        $abbreviation = strtoupper($state);

        if (strlen($abbreviation) != 2 || in_array($abbreviation, $validStates)) {
            return response([
                'message' => 'Invalid Request'
            ], 400)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ]);
        }

        $body = [];

        $state = State::with('cities')->where('abbreviation', strtoupper($state))->first();

        $cities = [];
        foreach ($state->cities as $city) {
            $cities[] = $city->name;
        }

        $body['state'] = $state->name;
        $body['cities'] = $cities;

        return response()->json($body);
    }

    /**
     * @param string       $userId
     * @param Request $request
     *
     * @return mixed
     */
    public function userVisit($userId, Request $request)
    {
        $googleMaps = new GoogleMapsApi();

        $this->validate($request, [
            'city' => 'required',
            'state' => 'required'
        ]);

        $city = $request->get('city');
        $state = $request->get('state');
        $user = User::find($userId);
        $details = $googleMaps->getCityDetails($city, $state);

        if ($details == false) {
            return response(['message' => 'Could Find Location'], 400);
        }

        $state = State::where('abbreviation', strtoupper($details['state']))->first();

        $city = City::where('name', trim($details['city']))->firstOrCreate([
            'name' => trim($details['city']),
            'state_id' => $state->id,
            'longitude' => floatval($details['longitude']),
            'latitude' => floatval($details['latitude']),
            'status' => 'verified',

        ]);

        $city->users()->attach($user->id, ['visited' => true]);

        return response(['message' => 'Success! Visit Posted']);
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function userCities($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response(["message" => "can not locate user"], 400);
        }

        $visited = [];
        $cities = [];
        foreach($user->cities as $visitCity) {
            $valid =[];
            if ($visitCity->pivot->visited) {
                $city =  City::with('state')->where('id', $visitCity->id)->first();
                $valid['city'] = $city->name;
                $valid['state'] = $city->state->abbreviation;
                $valid['longitude'] = $city->longitude;
                $valid['latitude'] = $city->latitude;
                $cities[] = $valid;
            }
        }

        $visited['user']['id'] = $user->id;
        $visited['user']['first_name'] = $user->first_name;
        $visited['user']['last_name'] = $user->last_name;
        $visited['visited_cities'] = $cities;

        return $visited;
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function userStates($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response(["message" => "can not locate user"], 400);
        }

        $visited = [];
        $states = [];
        foreach($user->cities as $visitCity) {
            if ($visitCity->pivot->visited) {
                $city =  City::with('state')->where('id', $visitCity->id)->first();
                if (in_array($city->state->abbreviation, $states)) {
                    continue;
                }
                $states[] = $city->state->abbreviation;
            }
        }

        $visited['user']['id'] = $user->id;
        $visited['user']['first_name'] = $user->first_name;
        $visited['user']['last_name'] = $user->last_name;
        $visited['visited_states'] = $states;

        return $visited;
    }

    /**
     * @param string $userId
     *
     * @param string $visitId
     *
     * @return array
     */
    public function userDeleteCity($userId, $visitId)
    {
        $user = User::with('cities')->where('id', $userId)->first();

        if (!$user) {
            return response(["message" => "can not locate user"], 400);
        }

        $success = DB::table('city_user')->where('id', '=', $visitId)->delete();

        if (!$success) {
            return response(['message' => 'Internal Server Error'], 500);
        }

        return response(['message' => 'Success! Visit Posted']);
    }

    /**
     * @return array
     */
    protected function getDBStateNames()
    {
        $states = State::all();

        $names = [];
        foreach ($states as $state) {
            $names[] = $state->name;
        }

        return $names;
    }
}
