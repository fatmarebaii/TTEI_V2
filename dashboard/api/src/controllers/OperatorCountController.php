<?php

class OperatorCountController
{
    public function __construct(private OperatorCountGateway $operatorCountGateway)
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

        $operatorCount = $this->operatorCountGateway->getOperatorCount($lineCode);

        echo json_encode([
            "error" => "NO_ERROR",
            "operator_count" => $operatorCount,
        ]);
    }
}
