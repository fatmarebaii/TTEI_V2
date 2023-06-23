<?php

class OperatorCountGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getOperatorCount(string $lineCode): int
    {
        $shiftStartTimes = [
            "morning" => "06:00:00",
            "afternoon" => "14:00:00",
            "night" => "22:00:00"
        ];

        $currentDateTime = date("Y-m-d H:i:s");
        $shift = $this->getCurrentShift($currentDateTime, $shiftStartTimes);

        $sql = "SELECT COUNT(*) as operator_count 
                FROM `prod__presence`
                WHERE `line_code` = :line_code 
                AND `cur_dt` >= :shift_start_time 
                AND `cur_dt` < :shift_end_time
                AND `p_state` = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":line_code" => $lineCode,
            ":shift_start_time" => $shift["start_time"],
            ":shift_end_time" => $shift["end_time"]
        ]);

        $operatorCount = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $operatorCount;
    }

    private function getCurrentShift(string $currentDateTime, array $shiftStartTimes): array
    {
        $currentTime = date("H:i:s", strtotime($currentDateTime));
        $shift = "";

        if ($currentTime >= $shiftStartTimes["morning"] && $currentTime < $shiftStartTimes["afternoon"]) {
            $shift = "morning";
        } elseif ($currentTime >= $shiftStartTimes["afternoon"] && $currentTime < $shiftStartTimes["night"]) {
            $shift = "afternoon";
        } else {
            $shift = "night";
        }

        $shiftStartTime = date("Y-m-d") . " " . $shiftStartTimes[$shift];
        $shiftEndTime = date("Y-m-d", strtotime("+1 day")) . " " . $shiftStartTimes[$shift];

        return [
            "start_time" => $shiftStartTime,
            "end_time" => $shiftEndTime
        ];
    }
}
