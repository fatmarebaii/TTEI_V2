<?php

class ProductShiftGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
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
