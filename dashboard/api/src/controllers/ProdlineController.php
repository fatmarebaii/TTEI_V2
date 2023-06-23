<?php

class ProdlineController
{
    public function __construct(private ProdlineGateway $prodlineGateway)
    {
    }

    public function processRequest(): void
    {
        $action = explode("/", $_SERVER['REQUEST_URI'])[5] ?? NULL;

        // GUARD
        if (is_null($action)) {
            http_response_code(400);
            echo json_encode([
                "error" => "BAD_REQUEST",
            ]);
            return;
        }

        // $method = $_SERVER["REQUEST_METHOD"];

        switch ($action) {
            case "prodline":
                $this->ProdlineDesc();
                // $this->OperatorCount();
                // $this->ProductReference();
                // $this->ProductShift();

                break;

            case "operator":
                $this->OperatorCount();
                break;

            case "prod-reference":
                $this->ProductReference();
                break;

            case "prod-shift":
                $this->ProductShift();
                break;

            default:
                http_response_code(404);
                echo json_encode([
                    "error" => "NOT_FOUND",
                ]);
                break;
        }
    }

    public function ProdlineDesc(): void
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

        $prodlineDesc = $this->prodlineGateway->getProdlineDesc($lineCode);

        echo json_encode([
            "error" => "NO_ERROR",
            "line_desc" => $prodlineDesc,
        ]);
    }

    public function OperatorCount(): void
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

        $operatorCount = $this->prodlineGateway->getOperatorCount($lineCode);

        echo json_encode([
            // "error" => "NO_ERROR",
            "operator_count" => $operatorCount,
        ]);
    }

    public function ProductReference(): void
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

        $latestReference = $this->prodlineGateway->getLatestReference($lineCode);
        $OK = $this->prodlineGateway->countOK($lineCode, $latestReference);
        $NOK = $this->prodlineGateway->countNOK($lineCode, $latestReference);

        $ppm = 0;
        if (($OK + $NOK) > 0) {
            $ppm = ($NOK / ($OK + $NOK)) * 1000000;
        }

        echo json_encode([
            // "error" => "NO_ERROR",
            "latest_reference" => $latestReference,
            "qOK" => $OK,
            "qNOK" => $NOK,
            "qPPM" => round($ppm,2),
        ]);
    }

    public function ProductShift(): void
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

        $referenceStats = $this->prodlineGateway->getLatestReferenceStats($lineCode);

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
            // "error" => "NO_ERROR",
            "last_reference" => $productref,
            "qOK" => $OK,
            "qNOK" => $NOK,
            "qPPM" => round($PPM,2)
        ]);
    }
}
