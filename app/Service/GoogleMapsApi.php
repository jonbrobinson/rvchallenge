<?php

namespace App\Aaulyp\Tools\Api;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Request;

class GoogleMapsApi
{
    const GOOGLE_MAPS_BASE_URL = "https://maps.googleapis.com/maps/api";

    protected $guzzle;

    public function __construct()
    {
        $this->guzzle = new Guzzle([
            // Base URI is used with relative requests
            'base_uri' => self::GOOGLE_MAPS_BASE_URL,
        ]);
    }

    /**
     * Gets contents of a single folder
     *
     * @return array
     */
    public function getAddressFromLatLong($latitude, $longitude)
    {
        $url = self::GOOGLE_MAPS_BASE_URL . '/geocode/json';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $query = [
            'latlng' => $latitude . ',' . $longitude,
            'result_type' => 'street_address',
            'key' => 'AIzaSyDBjraWiz5BuIM0LnJl-AP5p8PF9fBbQQY',
        ];

        $options = [
            'headers' => $headers,
            'query' => $query
        ];

        $response = $this->guzzle->request('GET', $url, $options);

        $addressJson = json_decode($response->getBody()->getContents());

        if ($addressJson->results) {
            $address = $this->sanitizeGoogleMapsLocation($addressJson->results[0]);
        } else {
            $address = array();
        }

        return $address;
    }

    public function getCityDetails($city = "", $state = "")
    {
        $search = "";

        if (!empty($city) && !empty($state)) {
            $search .= $city.",".$state;
        } elseif (!empty($city) && empty($state)) {
            $search .= $city;
        } elseif (empty($city) && !empty($state)) {
            $search .= $state;
        } else {
            return false;
        }

        $body = $this->search($search);

        $response = json_decode($body, true);

        if (count($response['results']) < 1 || !array_key_exists('place_id', $response['results'][0])) {
            return false;
        }

        $respDetails = $response['results'][0];
        $longitude = $respDetails['geometry']['location']['lng'];
        $latitude = $respDetails['geometry']['location']['lat'];
        $details = $this->getAddressFromLatLong($latitude, $longitude);
        $details['latitude'] = $latitude;
        $details['longitude'] = $longitude;

        return $details;
    }

    public function search($searchString)
    {
        $url = self::GOOGLE_MAPS_BASE_URL.'/place/textsearch/json';

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $query = [
            'query' => $searchString,
            'key' => env('GOOGLE_API_KEY'),
        ];

        $options = [
            'headers' => $headers,
            'query' => $query
        ];

        $response = $this->guzzle->request('GET', $url, $options);

        return $response->getBody()->getContents();
    }

    protected function sanitizeGoogleMapsLocation($location)
    {
        $details = array();
        $locationArray = json_decode(json_encode($location), true);
        $components = $locationArray['address_components'];

        foreach ($components as $component) {
            if ('street_number' == $component['types'][0]) {
                $details[$component['types'][0]] = $component['long_name'];
            }

            if ('route' == $component['types'][0]) {
                $details['street'] = $component['short_name'];
            }

            if ('locality' == $component['types'][0]) {
                $details['city'] = $component['long_name'];
            }

            if ('locality' == $component['types'][0]) {
                $details['city'] = $component['long_name'];
            }

            if ('administrative_area_level_1' == $component['types'][0]) {
                $details['state'] = $component['short_name'];
            }

            if ('postal_code' == $component['types'][0]) {
                $details['zip'] = $component['long_name'];
            }
        }

        $details['formatted_address'] = $locationArray['formatted_address'];

        return $details;
    }
}