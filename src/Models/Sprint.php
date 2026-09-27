<?php

class Sprint {
    public static function getActive(int $programId): ?array {
        return db()->fetchOne(
            "SELECT * FROM sprints WHERE program_id=? AND status='active' ORDER BY start_date DESC LIMIT 1",
            [$programId]
        );
    }

    public static function getAll(int $programId): array {
        return db()->fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM tasks t WHERE t.sprint_id = s.id) as total_tasks,
                    (SELECT SUM(t.story_points) FROM tasks t WHERE t.sprint_id = s.id) as total_points,
                    (SELECT SUM(t.story_points) FROM tasks t WHERE t.sprint_id = s.id AND t.status='completed') as completed_points
             FROM sprints s
             WHERE s.program_id=?
             ORDER BY s.id DESC",
            [$programId]
        );
    }

    public static function getMetrics(int $sprintId): array {
        $sprint = db()->fetchOne("SELECT * FROM sprints WHERE id=?", [$sprintId]);
        if (!$sprint) return [];

        $tasks = db()->fetchAll("SELECT * FROM tasks WHERE sprint_id=?", [$sprintId]);
        $totalPoints = 0;
        $completedPoints = 0;
        $inProgressPoints = 0;
        $totalHours = 0;
        $completedHours = 0;

        foreach ($tasks as $t) {
            $pts = (int)($t['story_points'] ?? 3);
            $totalPoints += $pts;
            if ($t['status'] === 'completed') {
                $completedPoints += $pts;
                $completedHours += (float)($t['effort_act'] ?? $t['effort_est'] ?? 0);
            } elseif ($t['status'] === 'in_progress') {
                $inProgressPoints += $pts;
            }
            $totalHours += (float)($t['effort_est'] ?? 0);
        }

        $pctPoints = $totalPoints > 0 ? round(($completedPoints / $totalPoints) * 100) : 0;

        return [
            'sprint'            => $sprint,
            'tasks_count'       => count($tasks),
            'total_points'      => $totalPoints,
            'completed_points'  => $completedPoints,
            'in_progress_points'=> $inProgressPoints,
            'remaining_points'  => $totalPoints - $completedPoints,
            'total_hours'       => $totalHours,
            'completed_hours'   => $completedHours,
            'completion_pct'    => $pctPoints,
        ];
    }
}
