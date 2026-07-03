<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * Mengelola data pengeluaran.
 */
class Expense
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO expenses (category_id, name, amount, expense_date, notes)
                VALUES (:category_id, :name, :amount, :expense_date, :notes)';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            ':category_id' => (int) $data['category_id'],
            ':name' => trim((string) $data['name']),
            ':amount' => (float) $data['amount'],
            ':expense_date' => $data['expense_date'],
            ':notes' => $data['notes'] !== '' ? trim((string) $data['notes']) : null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE expenses
                SET category_id = :category_id,
                    name = :name,
                    amount = :amount,
                    expense_date = :expense_date,
                    notes = :notes
                WHERE id = :id';
        $statement = $this->connection->prepare($sql);

        return $statement->execute([
            ':id' => $id,
            ':category_id' => (int) $data['category_id'],
            ':name' => trim((string) $data['name']),
            ':amount' => (float) $data['amount'],
            ':expense_date' => $data['expense_date'],
            ':notes' => $data['notes'] !== '' ? trim((string) $data['notes']) : null,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM expenses WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                FROM expenses e
                INNER JOIN categories c ON c.id = e.category_id
                WHERE e.id = :id
                LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->execute([':id' => $id]);
        $expense = $statement->fetch();

        return $expense !== false ? $expense : null;
    }

    public function getAll(array $filters = []): array
    {
        $sql = 'SELECT e.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color
                FROM expenses e
                INNER JOIN categories c ON c.id = e.category_id';
        $conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(e.name LIKE :search1 OR e.notes LIKE :search2)';
            $params[':search1'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = 'e.category_id = :category_id';
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'e.expense_date >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'e.expense_date <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $allowedSortColumns = ['expense_date', 'amount', 'name', 'created_at'];
        $sortBy = in_array($filters['sort_by'] ?? '', $allowedSortColumns, true) ? $filters['sort_by'] : 'expense_date';
        $sortOrder = strtoupper((string) ($filters['sort_order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= sprintf(' ORDER BY e.%s %s', $sortBy, $sortOrder);

        if (isset($filters['limit'])) {
            $sql .= ' LIMIT ' . max(1, (int) $filters['limit']);
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function getLatest(int $limit = 5): array
    {
        return $this->getAll([
            'sort_by' => 'created_at',
            'sort_order' => 'DESC',
            'limit' => $limit,
        ]);
    }

    public function countAll(): int
    {
        return (int) $this->connection->query('SELECT COUNT(*) FROM expenses')->fetchColumn();
    }

    public function getTotalAmount(): float
    {
        return (float) $this->connection->query('SELECT COALESCE(SUM(amount), 0) FROM expenses')->fetchColumn();
    }

    public function getTodayTotal(): float
    {
        $statement = $this->connection->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date = CURDATE()');
        $statement->execute();

        return (float) $statement->fetchColumn();
    }

    public function getMonthTotal(): float
    {
        $statement = $this->connection->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE YEAR(expense_date) = YEAR(CURDATE()) AND MONTH(expense_date) = MONTH(CURDATE())');
        $statement->execute();

        return (float) $statement->fetchColumn();
    }
}
