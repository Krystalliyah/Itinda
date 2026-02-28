// app/Services/CDCDataService.php
<?php


use Illuminate\Support\Facades\Http;

class CDCDataService
{
    private $baseUrl = 'https://data.cdc.gov/resource/rsk5-566a.json';

    public function getCardiovascularData($limit = 100, $offset = 0)
    {
        $response = Http::get($this->baseUrl, [
            '$limit' => $limit,
            '$offset' => $offset,
            '$order' => 'year DESC'
        ]);

        return $response->json();
    }

    public function filterByState($state, $limit = 100)
    {
        $response = Http::get($this->baseUrl, [
            '$where' => "locationabbr = '{$state}'",
            '$limit' => $limit
        ]);

        return $response->json();
    }

    public function filterByYear($year)
    {
        $response = Http::get($this->baseUrl, [
            '$where' => "year = '{$year}'",
            '$limit' => 1000
        ]);

        return $response->json();
    }
}