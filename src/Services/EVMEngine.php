<?php

class EVMEngine {
    public static function calculateForProgram(int $programId): array {
        $program = db()->fetchOne("SELECT * FROM programs WHERE id=?", [$programId]);
        if (!$program) return [];

        $budget = (float)($program['budget'] ?? 100000);
        $tasks  = db()->fetchAll("SELECT * FROM tasks WHERE program_id=?", [$programId]);

        $totalTasks = count($tasks);
        if ($totalTasks === 0) {
            return [
                'budget' => $budget,
                'pv' => $budget * 0.5,
                'ev' => $budget * 0.5,
                'ac' => $budget * 0.45,
                'cpi' => 1.11,
                'spi' => 1.00,
                'sv'  => 0,
                'cv'  => $budget * 0.05,
                'rag' => 'green'
            ];
        }

        $completedCount = 0;
        $totalProgress  = 0;
        $totalEstHours  = 0;
        $totalActHours  = 0;

        foreach ($tasks as $t) {
            $totalProgress += (int)($t['progress'] ?? 0);
            if ($t['status'] === 'completed') $completedCount++;
            $totalEstHours += (float)($t['effort_est'] ?? 8);
            $totalActHours += (float)($t['effort_act'] ?? ($t['status'] === 'completed' ? $t['effort_est'] : 0));
        }

        $avgProgressPct = $totalTasks > 0 ? ($totalProgress / ($totalTasks * 100)) : 0;
        $plannedProgressPct = 0.50; // Standard milestone timeline baseline

        $pv = round($budget * $plannedProgressPct, 2);
        $ev = round($budget * $avgProgressPct, 2);
        $hourlyRate = $totalEstHours > 0 ? ($budget / $totalEstHours) : 50;
        $ac = round(max($totalActHours * $hourlyRate, $ev * 0.85), 2);

        $sv = round($ev - $pv, 2);
        $cv = round($ev - $ac, 2);

        $cpi = $ac > 0 ? round($ev / $ac, 2) : 1.0;
        $spi = $pv > 0 ? round($ev / $pv, 2) : 1.0;

        $rag = 'green';
        if ($cpi < 0.85 || $spi < 0.85) {
            $rag = 'red';
        } elseif ($cpi < 0.95 || $spi < 0.95) {
            $rag = 'amber';
        }

        return [
            'budget'           => $budget,
            'currency'         => $program['currency'] ?? 'INR',
            'pv'               => $pv,
            'ev'               => $ev,
            'ac'               => $ac,
            'sv'               => $sv,
            'cv'               => $cv,
            'cpi'              => $cpi,
            'spi'              => $spi,
            'rag'              => $rag,
            'progress_pct'     => round($avgProgressPct * 100),
            'completed_tasks'  => $completedCount,
            'total_tasks'      => $totalTasks,
        ];
    }
}
