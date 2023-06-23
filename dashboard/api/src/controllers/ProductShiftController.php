<?php

class ProductShiftController
{
    public function __construct(private ProductShiftGateway $ProductShiftGateway)
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

        $referenceStats = $this->ProductShiftGateway->getLatestReferenceStats($lineCode);

        if ($referenceStats['last_reference']===null){
            $productref='NO REFERNCE AVAILABLE';
        }else {
            $productref=$referenceStats['last_reference'];
        }

        // Calculate PPM
        $OK = $referenceStats['OK'] ?? 0;
        $NOK = $referenceStats['NOK'] ?? 0;
        if (($OK+$NOK)!=0){
        $PPM = ($NOK / ($OK + $NOK)) * 1000000;
        } else {$PPM=0;}

        echo json_encode([
            "error" => "NO_ERROR",
            "last_reference" => $productref,
            "OK" => $OK,
            "NOK" => $NOK,
            "PPM" => $PPM
        ]);
    }
}
