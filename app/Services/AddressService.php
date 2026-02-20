<?php
namespace App\Services;

use App\Models\Address;
use App\Models\Location;

class AddressService
{
    public function getOrCreateAddress(string $address, string $location, string $postcode, int $countryId): Address
    {
        $location = Location::addData(
            location: $location,
            postcode: $postcode,
            countryId: $countryId,
            save: true
        );
        $address = Address::addData(
            address: $address,
            locationId: $location->location_id,
            save: true
        );

        return $address;
    }


    public function getOrCreatLocation(string $location, string $postcode, int $countryId): Location
    {
        $location = Location::addData(
            location: $location,
            postcode: $postcode,
            countryId: $countryId,
            save: true
        );

        return $location;
    }
}