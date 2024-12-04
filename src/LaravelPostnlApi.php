<?php

namespace Adequaat\LaravelPostnlApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LaravelPostnlApi
{

    protected mixed $customer;

    public function __construct($customer = null)
    {
        if(null === $customer) {
            $this->customer = [
                'Address' => [
                        'AddressType' => '02',
                        'City' => config('postnl-api.customer.address.city'),
                        'CompanyName' => config('postnl-api.customer.address.company_name'),
                        'Countrycode' => config('postnl-api.customer.address.country_code'),
                        'HouseNr' => config('postnl-api.customer.address.house_number'),
                        'Zipcode' => config('postnl-api.customer.address.postal_code'),
                        'Street' => config('postnl-api.customer.address.street'),
                    ],
                    'CollectionLocation' => config('postnl-api.customer.collection_location'),
                    'CustomerCode' => config('postnl-api.customer.code'),
                    'CustomerNumber' => config('postnl-api.customer.number'),
                    'Email' => config('postnl-api.customer.email'),
            ];
        } else {
            $this->customer = $customer;
        }
    }

    public function generateBarcode(
        $type = '3S',
        $serie = '000000000-999999999'
    )
    {
        $barcode = Http::withHeaders([
            'apikey' => env('POSTNL_API_KEY'),
        ])->get(
            env('POSTNL_API_BASE_URL').'shipment/v1_1/barcode?CustomerCode='.config('postnl-api.customer.code').'&CustomerNumber='.config('postnl-api.customer.number').'&Type='.$type.'&Serie='.$serie
        );

        return $barcode->collect(['Barcode'])->first();
    }

    /**
     * @param $barcode string|null
     * @param string $printertype
     * @param array|null $address [AddressType, City, CompanyName, Countrycode, HouseNr, Zipcode, Street]
     * @param array|null $contact [ContactType, Email, SMSNr]
     * @param string $productCodeDelivery
     * @param $reference string|null
     * @param $remark string|null
     * @param bool $fullLabel
     * @return void
     */
    public function generateLabel(
        string $barcode = null,
        string $printertype = 'GraphicFile|PDF',
        array  $address = null,
        array  $contact = null,
        string $productCodeDelivery = '3085',
        array  $productOptions = null,
        string $deliveryDate = null,
        string $reference = null,
        string $remark = null,
        bool   $fullLabel = false,
    )
    {
        try {
            $response = Http::withHeaders([
                'apikey' => config('postnl-api.api.key'),
                'Content-Type' => 'application/json'
            ])->post(env('POSTNL_API_BASE_URL').'shipment/v2_2/label',
                [
                    'Customer' => $this->customer,
                    'Message' => [
                        'MessageTimeStamp' => Carbon::now()->format('dd-mm-yyyy hh:mm:ss'),
                        'Printertype' => $printertype,
                    ],
                    'Shipments' => [
                        'Addresses' => $address,
                        'Contacts' => $contact,
                        'ProductCodeDelivery' => $productCodeDelivery,
                        'ProductOptions' => $productOptions,
                        'DeliveryDate' => Carbon::parse($deliveryDate)->format('d-m-Y H:i:s'),
                        'Barcode' => $barcode,
                        'Reference' => $reference,
                        'Remark' => $remark,
                    ],
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $responseObject = $response->object();

        if ($responseObject && property_exists($responseObject, 'ResponseShipments')) {
            $responseShipments = collect($responseObject->ResponseShipments)->first();

            if ($fullLabel) {
                return $responseShipments;
            }

            return collect($responseShipments->Labels)->first()->Content;
        } else {
            throw new Exception('PostNL API Error: ' . json_encode($responseObject));
        }

    }

    /**
     * @param array|null $barcodes
     * @param string $printertype
     * @param array|null $address [AddressType, City, CompanyName, Countrycode, HouseNr, Zipcode, Street]
     * @param array|null $contact [ContactType, Email, SMSNr]
     * @param string $productCodeDelivery
     * @param $reference string|null
     * @param $remark string|null
     * @return void
     */
    public function generateLabelForMultiCollo(
        array $barcodes = null,
        string $printertype = 'GraphicFile|PDF',
        array  $address = null,
        array  $contact = null,
        string $productCodeDelivery = '3085',
        array  $productOptions = null,
        string $deliveryDate = null,
        string $reference = null,
        string $remark = null,
    )
    {

        // Create a shipment for each barcode
        $shipments = [];

        foreach($barcodes as $index => $barcode) {
            $shipments[] = [
                'Addresses' => $address,
                'Contacts' => $contact,
                'ProductCodeDelivery' => $productCodeDelivery,
                'ProductOptions' => $productOptions,
                'DeliveryDate' => Carbon::parse($deliveryDate)->format('d-m-Y H:i:s'),
                'Barcode' => $barcode,
                'Reference' => $reference,
                'Remark' => $remark,
                'Groups' => [
                    'GroupSequence' => $index + 1,
                    'GroupType' => '03',
                    'GroupCount' => count($barcodes),
                    'MainBarcode' => $barcodes[0],
                ]
            ];
        }

        try {
            $response = Http::withHeaders([
                'apikey' => config('postnl-api.api.key'),
                'Content-Type' => 'application/json'
            ])->post(env('POSTNL_API_BASE_URL') . 'shipment/v2_2/label',
                [
                    'Customer' => $this->customer,
                    'Message' => [
                        'MessageTimeStamp' => Carbon::now()->format('dd-mm-yyyy hh:mm:ss'),
                        'Printertype' => $printertype,
                    ],
                    'Shipments' => $shipments
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $responseObject = $response->object();

        if ($responseObject && property_exists($responseObject, 'ResponseShipments')) {
            $responseShipments = collect($responseObject->ResponseShipments);

            return $responseShipments;
        } else {
            throw new Exception('PostNL API Error for multicollo: ' . json_encode($responseObject));
        }

    }

    /**
     * @param $barcode string|null
     * @param string $printertype
     * @param array|null $address [AddressType, City, CompanyName, Countrycode, HouseNr, Zipcode, Street]
     * @param array|null $contact [ContactType, Email, SMSNr]
     * @param string $productCodeDelivery
     * @param $reference string|null
     * @param $remark string|null
     * @return void
     *
     * This method uses the Confirming API
     * https://developer.postnl.nl/browse-apis/send-and-track/confirming-webservice/
     *
     * BvdT needs this because we create the labels locally (custom labels)
     * and confirm the parcels for PostNL later on.
     */
    public function confirmShipment(
        string $barcode = null,
        string $printertype = 'GraphicFile|PDF',
        array  $address = null,
        array  $contact = null,
        string $productCodeDelivery = '3085',
        array  $productOptions = null,
        string $deliveryDate = null,
        string $reference = null,
        string $remark = null
    )
    {
        $response = Http::withHeaders([
            'apikey' => config('postnl-api.api.key'),
            'Content-Type' => 'application/json'
        ])->post(env('POSTNL_API_BASE_URL').'shipment/v2/confirm',
        [
            'Customer' => $this->customer,
            'Message' => [
                'MessageTimeStamp' => Carbon::now()->format('dd-mm-yyyy hh:mm:ss'),
                'Printertype' => $printertype,
            ],
            'Shipments' => [
                'Addresses' => $address,
                'Contacts' => $contact,
                'ProductCodeDelivery' => $productCodeDelivery,
                'ProductOptions' => $productOptions,
                'DeliveryDate' => Carbon::parse($deliveryDate)->format('d-m-Y H:i:s'),
                'Barcode' => $barcode,
                'Reference' => $reference,
                'Remark' => $remark,
            ],
        ]);

        if($response->successful()) {
            return $response->object()->ResponseShipments;
        } else {
            abort(500, 'PostNL API Error: '. $response);
        }

    }

    public function findStatusForShipment(
        string $barcode = null,
        string $language = 'NL'
    )
    {
        $response = Http::withHeaders([
            'apikey' => config('postnl-api.api.key'),
            'Content-Type' => 'application/json'
        ])->get(env('POSTNL_API_BASE_URL').'shipment/v2/status/barcode/'.$barcode.'?detail=true&language='.$language);

        if($response->successful()) {
            return $response->object();
        } else {
            abort(500, 'PostNL API Error');
        }
    }
}
