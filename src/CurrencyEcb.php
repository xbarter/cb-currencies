<?php

namespace Currency;

class CurrencyECB {

    /**
     * Raw data
     *
     * @var array
     */
    public $data = null;

    /**
     * Timeout second
     * @var int
     */
    private $timeOut = 15;


    /**
     * Get data from bank's xml service
     * @throws \Exception
     */
    private function getDataFromBank()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FAILONERROR,1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeOut);
        $data = curl_exec($curl);

        if(curl_errno($curl)) {
            throw new \Exception("Connection problem: " . curl_error($curl));
        }
        curl_close($curl);

        $this->data =  $this->formatData($data);

    }

    /**
     * Format banks xml data
     * @param $data
     * @return array
     */
    private function formatData($data)
    {

        $xml = new \SimpleXMLElement($data);
        $currency = array();

        $date = current($xml->Cube->Cube->attributes());

        foreach($xml->Cube->Cube->Cube as $rate)
        {
            $prefix = (string) $rate["currency"];
            $currency[$prefix] = money_format('%.5n',(float) $rate['rate']);
        }

        return array(
            'date' => $date['time'],
            'currencies' => $currency
        );
    }

    /**
     * Get currency data as array
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        if(is_null($this->data))
            $this->getDataFromBank();

        return $this->data;
    }


}