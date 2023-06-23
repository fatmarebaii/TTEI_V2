<?php

class ProductReferenceController
{
    public function __construct(private ProductReferenceGateway $productReferenceGateway)
    {
    }

    public function processRequest(): void
    {
        $lineCode = $_GET["code-line"] ?? NULL;

        // GUARD
        if (is_null($lineCode)) {
            http_response_code(400);
            echo json_encode([
                "error" => "BAD_REQUEST",
            ]);
            return;
        }

        // $currentHour = (int) date("H");
        // $shiftStartTime = "";
        // $shiftEndTime = "";

        // // Determine the shift based on current hour
        // if ($currentHour >= 6 && $currentHour < 14) {
        //     $shiftStartTime = "06:00:00";
        //     $shiftEndTime = "14:00:00";
        // } elseif ($currentHour >= 14 && $currentHour < 22) {
        //     $shiftStartTime = "14:00:00";
        //     $shiftEndTime = "22:00:00";
        // } else {
        //     $shiftStartTime = "22:00:00";
        //     $shiftEndTime = "06:00:00";
        // }

        $latestReference = $this->productReferenceGateway->getLatestReference($lineCode);
        $OK = $this->productReferenceGateway->countOK($lineCode, $latestReference);
        $NOK = $this->productReferenceGateway->countNOK($lineCode, $latestReference);

        $ppm = 0;
        if (($OK + $NOK) > 0) {
            $ppm = ($NOK / ($OK + $NOK)) * 1000000;
        }

        echo json_encode([
            "error" => "NO_ERROR",
            "latest_reference" => $latestReference,
            "ok_pieces" => $OK,
            "nok_pieces" => $NOK,
            "ppm" => $ppm,
        ]);
    }
}
