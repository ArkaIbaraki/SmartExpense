<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * Menghitung statistik dan insight pengeluaran.
 */
class Statistics
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
    }

    public function getTotalTransactions(): int
    {
        return (int) $this->connection->query('SELECT COUNT(*) FROM expenses')->fetchColumn();
    }

    public function getTotalExpenseAmount(): float
    {
        return (float) $this->connection->query('SELECT COALESCE(SUM(amount), 0) FROM expenses')->fetchColumn();
    }

    public function getCategoryBreakdown(): array
    {
        $sql = 'SELECT c.id, c.name, c.icon, c.color,
                       COALESCE(SUM(e.amount), 0) AS total_amount,
                       COUNT(e.id) AS total_transactions
                FROM categories c
                LEFT JOIN expenses e ON e.category_id = c.id
                GROUP BY c.id, c.name, c.icon, c.color
                ORDER BY total_amount DESC, c.name ASC';
        $statement = $this->connection->query($sql);
        $rows = $statement->fetchAll();

        $grandTotal = array_sum(array_map(static fn (array $row): float => (float) $row['total_amount'], $rows));

        foreach ($rows as &$row) {
            $totalAmount = (float) $row['total_amount'];
            $row['percentage'] = $grandTotal > 0 ? round(($totalAmount / $grandTotal) * 100, 2) : 0.0;
        }
        unset($row);

        return $rows;
    }

    public function getLargestExpense(): ?array
    {
        $sql = 'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                FROM expenses e
                INNER JOIN categories c ON c.id = e.category_id
                ORDER BY e.amount DESC, e.expense_date DESC
                LIMIT 1';
        $statement = $this->connection->query($sql);
        $expense = $statement->fetch();

        return $expense !== false ? $expense : null;
    }

    public function getSmallestExpense(): ?array
    {
        $sql = 'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                FROM expenses e
                INNER JOIN categories c ON c.id = e.category_id
                ORDER BY e.amount ASC, e.expense_date ASC
                LIMIT 1';
        $statement = $this->connection->query($sql);
        $expense = $statement->fetch();

        return $expense !== false ? $expense : null;
    }

    public function getAverageDailyExpense(): float
    {
        $sql = 'SELECT COALESCE(AVG(daily_total), 0)
                FROM (
                    SELECT expense_date, SUM(amount) AS daily_total
                    FROM expenses
                    GROUP BY expense_date
                ) AS daily_expenses';
        return (float) $this->connection->query($sql)->fetchColumn();
    }

    public function getMostUsedCategory(): ?array
    {
        $sql = 'SELECT c.id, c.name, c.icon, c.color, COUNT(e.id) AS total_transactions
                FROM categories c
                INNER JOIN expenses e ON e.category_id = c.id
                GROUP BY c.id, c.name, c.icon, c.color
                ORDER BY total_transactions DESC, c.name ASC
                LIMIT 1';
        $statement = $this->connection->query($sql);
        $category = $statement->fetch();

        return $category !== false ? $category : null;
    }

    public function getWeeklyExpenseTotals(int $weeks = 8): array
    {
        $weeks = max(1, $weeks);
        $sql = 'SELECT YEARWEEK(expense_date, 1) AS week_key,
                       MIN(expense_date) AS week_start,
                       MAX(expense_date) AS week_end,
                       SUM(amount) AS total_amount
                FROM expenses
                GROUP BY YEARWEEK(expense_date, 1)
                ORDER BY week_key DESC
                LIMIT ' . $weeks;

        $statement = $this->connection->query($sql);
        $rows = $statement->fetchAll();

        foreach ($rows as &$row) {
            $row['label'] = date('d M', strtotime((string) $row['week_start'])) . ' - ' . date('d M', strtotime((string) $row['week_end']));
            $row['total_amount'] = (float) $row['total_amount'];
        }
        unset($row);

        return array_reverse($rows);
    }

    public function getInsightMessages(): array
    {
        $messages = [];
        $topCategory = $this->getMostUsedCategory();
        $largestExpense = $this->getLargestExpense();
        $weeklyTrend = $this->getWeeklyGrowthPercentage();

        if ($topCategory !== null) {
            $messages[] = 'Sebagian besar pengeluaran Anda berada pada kategori ' . $topCategory['name'] . '.';
        }

        if ($largestExpense !== null) {
            $messages[] = 'Pengeluaran terbesar Anda adalah ' . $largestExpense['name'] . ' sebesar Rp ' . number_format((float) $largestExpense['amount'], 0, ',', '.') . '.';
        }

        if ($weeklyTrend !== null) {
            $trendLabel = $weeklyTrend >= 0 ? 'meningkat' : 'menurun';
            $messages[] = 'Pengeluaran minggu ini ' . $trendLabel . ' ' . number_format(abs($weeklyTrend), 2, ',', '.') . '% dibanding minggu sebelumnya.';
        }

        return $messages;
    }

    public function getWeeklyGrowthPercentage(): ?float
    {
        $sql = 'SELECT YEARWEEK(expense_date, 1) AS week_key, SUM(amount) AS total_amount
                FROM expenses
                GROUP BY YEARWEEK(expense_date, 1)
                ORDER BY week_key DESC
                LIMIT 2';
        $statement = $this->connection->query($sql);
        $rows = $statement->fetchAll();

        if (count($rows) < 2) {
            return null;
        }

        $currentWeek = (float) $rows[0]['total_amount'];
        $previousWeek = (float) $rows[1]['total_amount'];

        if ($previousWeek == 0.0) {
            return null;
        }

        return (($currentWeek - $previousWeek) / $previousWeek) * 100;
    }
}
