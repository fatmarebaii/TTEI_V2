<?php

class ProdlineGateway
{
     private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getProdlineDesc(string $lineCode): string
    {
        $sql = "SELECT `line_desc` FROM `init__prod_line` WHERE `line_code` = :line_code";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":line_code" => $lineCode]);
        $prodlineDesc = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $prodlineDesc ?? "";
    }

    public function getOperatorCount(string $lineCode): int
    {
        $shiftStartTimes = [
            "morning" => "06:00:00",
            "afternoon" => "14:00:00",
            "night" => "22:00:00"
        ];

        $currentDateTime = date("Y-m-d H:i:s");
        $shift = $this->getCurrentShiftPresence($currentDateTime, $shiftStartTimes);

        $sql = "SELECT
        (COUNT(CASE WHEN `p_state` = 1 THEN 1 END) - COUNT(CASE WHEN `p_state` = 0 THEN 1 END)) operator_count
    FROM
        `prod__presence`
    WHERE
        `line_code` = :line_code 
        AND `cur_dt` >= :shift_start_time
        AND `cur_dt` < :shift_end_time;";

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

    private function getCurrentShiftPresence(string $currentDateTime, array $shiftStartTimes): array
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

    public function getLatestReference(string $lineCode): string
    {
        $sql = "SELECT `reference` FROM `prod__production` WHERE `line_code` = :line_code AND `cur_dt` >= :shift_start_time 
        AND `cur_dt` <= :shift_end_time ORDER BY `id` DESC LIMIT 1";

        $shifts = $this->getCurrentShifts();
        $startTime = $shifts['start_time'];
        $endTime = $shifts['end_time'];

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":line_code" => $lineCode,
        ":shift_start_time" => $startTime,
        ":shift_end_time" => $endTime,]);
        $latestReference = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $latestReference ?? "";
    }

    public function countOK(string $lineCode, string $reference): int
    {
        $sql = "SELECT COUNT(*) FROM `prod__production` 
                WHERE `line_code` = :line_code 
                AND `cur_dt` >= :shift_start_time 
                AND `cur_dt` <= :shift_end_time 
                AND `reference` = :reference 
                AND `w_status` = 1";

        $shifts = $this->getCurrentShifts();
        $startTime = $shifts['start_time'];
        $endTime = $shifts['end_time'];

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":line_code" => $lineCode,
            ":shift_start_time" => $startTime,
            ":shift_end_time" => $endTime,
            ":reference" => $reference
        ]);
        $count = (int) $stmt->fetchColumn();
        $stmt->closeCursor();

        return $count;
    }

    public function countNOK(string $lineCode, string $reference): int
    {
        $sql = "SELECT COUNT(*) FROM `prod__production` 
                WHERE `line_code` = :line_code 
                AND `cur_dt` >= :shift_start_time 
                AND `cur_dt` <= :shift_end_time 
                AND `reference` = :reference 
                AND `w_status` = 0";
        
        $shifts = $this->getCurrentShifts();
        $startTime = $shifts['start_time'];
        $endTime = $shifts['end_time'];

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":line_code" => $lineCode,
            ":shift_start_time" => $startTime,
            ":shift_end_time" => $endTime,
            ":reference" => $reference
        ]);
        $count = (int) $stmt->fetchColumn();
        $stmt->closeCursor();

        return $count;
    }

    private function getCurrentShifts(): array
    {
        $currentTime = time();
        $startTimes = [
            strtotime('today 06:00:00'),
            strtotime('today 14:00:00'),
            strtotime('today 22:00:00')
        ];

        foreach ($startTimes as $index => $startTime) {
            $endTime = $index === 2 ? strtotime('tomorrow 06:00:00') : $startTimes[$index + 1];
            if ($currentTime >= $startTime && $currentTime < $endTime) {
                return [
                    'start_time' => date('Y-m-d H:i:s', $startTime),
                    'end_time' => date('Y-m-d H:i:s', $endTime)
                ];
            }
        }

        // If current time doesn't fall into any shift, return the first shift
        return [
            'start_time' => date('Y-m-d H:i:s', $startTimes[0]),
            'end_time' => date('Y-m-d H:i:s', $startTimes[1])
        ];
    }

    public function getLatestReferenceStats(string $lineCode): array
    {
        $sql = "SELECT 
                    (SELECT `prod__production`.`reference` FROM `prod__production` ORDER BY `id` DESC LIMIT 1) AS last_reference,
                    SUM(CASE WHEN `prod__production`.`w_status` = 1 THEN 1 ELSE 0 END) AS OK,
                    SUM(CASE WHEN `prod__production`.`w_status` = 0 THEN 1 ELSE 0 END) AS NOK
                FROM 
                    `prod__production`
                WHERE 
                    `prod__production`.`line_code` = :line_code
                    AND `prod__production`.`cur_dt` BETWEEN :start_time AND :end_time";

        $shifts = $this->getCurrentShifts();
        $startTime = $shifts['start_time'];
        $endTime = $shifts['end_time'];

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":line_code" => $lineCode,
            ":start_time" => $startTime,
            ":end_time" => $endTime
        ]);
        $referenceStats = $stmt->fetch();
        $stmt->closeCursor();

        return $referenceStats ?? [];
    }

    public function getOperatorProductivity(string $lineCode): int
    {
        $shiftStartTimes = [
            "morning" => "06:00:00",
            "afternoon" => "14:00:00",
            "night" => "22:00:00"
        ];

        $currentDateTime = date("Y-m-d H:i:s");
        $shift = $this->getCurrentShiftPresence($currentDateTime, $shiftStartTimes);

        $sql = "SELECT `productivity` FROM `prod__productivity`
    WHERE
        `line_code` = :line_code 
        AND `cur_dt` >= :shift_start_time
        AND `cur_dt` < :shift_end_time
        ORDER BY `id` DESC LIMIT 1   ;";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":line_code" => $lineCode,
            ":shift_start_time" => $shift["start_time"],
            ":shift_end_time" => $shift["end_time"]
        ]);

        $productivity = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $productivity;
    }

}