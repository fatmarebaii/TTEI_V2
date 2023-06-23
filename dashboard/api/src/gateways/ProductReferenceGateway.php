<?php

class ProductReferenceGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getLatestReference(string $lineCode): string
    {
        $sql = "SELECT `reference` FROM `prod__production` WHERE `line_code` = :line_code ORDER BY `id` DESC LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":line_code" => $lineCode]);
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
}
