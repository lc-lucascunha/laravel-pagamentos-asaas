<?php

namespace App\Services;

class AsaasService
{
    public function __construct(

    )
    {

    }

    public function createClient($data)
    {
        try {

            dd($data);

            //throw new \Exception('erro');

        }
        catch (\Exception $e) {

            //throw new \Exception($e->getMessage());

        }
    }
}
