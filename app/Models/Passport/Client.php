<?php

namespace App\Models\Passport;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    public function skipsAuthorization(): bool
    {
        return $this->firstParty();
    }
}