<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * Mengelola data kategori pengeluaran.
 */
class Category
{
    private PDO $connection;
    private ?int $id = null;
    private string $name = '';
    private ?string $icon = null;
    private ?string $color = null;

    public function __construct(array $data = [], ?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
        $this->fill($data);
    }

    public function fill(array $data): void
    {
        if (isset($data['id'])) {
            $this->setId((int) $data['id']);
        }

        if (isset($data['name'])) {
            $this->setName((string) $data['name']);
        }

        if (array_key_exists('icon', $data)) {
            $this->setIcon($data['icon'] !== null ? (string) $data['icon'] : null);
        }

        if (array_key_exists('color', $data)) {
            $this->setColor($data['color'] !== null ? (string) $data['color'] : null);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon !== null ? trim($icon) : null;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color !== null ? trim($color) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
        ];
    }

    public function save(): bool
    {
        if ($this->id === null) {
            $sql = 'INSERT INTO categories (name, icon, color) VALUES (:name, :icon, :color)';
            $statement = $this->connection->prepare($sql);
            $result = $statement->execute([
                ':name' => $this->name,
                ':icon' => $this->icon,
                ':color' => $this->color,
            ]);

            if ($result) {
                $this->id = (int) $this->connection->lastInsertId();
            }

            return $result;
        }

        $sql = 'UPDATE categories SET name = :name, icon = :icon, color = :color WHERE id = :id';
        $statement = $this->connection->prepare($sql);

        return $statement->execute([
            ':id' => $this->id,
            ':name' => $this->name,
            ':icon' => $this->icon,
            ':color' => $this->color,
        ]);
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $statement = $this->connection->prepare('DELETE FROM categories WHERE id = :id');
        return $statement->execute([':id' => $this->id]);
    }

    public static function all(?PDO $connection = null): array
    {
        $database = $connection ?? Database::getInstance()->getConnection();
        $statement = $database->query('SELECT * FROM categories ORDER BY name ASC');

        return $statement->fetchAll();
    }

    public static function findById(int $id, ?PDO $connection = null): ?array
    {
        $database = $connection ?? Database::getInstance()->getConnection();
        $statement = $database->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $category = $statement->fetch();

        return $category !== false ? $category : null;
    }

    public static function findByName(string $name, ?PDO $connection = null): ?array
    {
        $database = $connection ?? Database::getInstance()->getConnection();
        $statement = $database->prepare('SELECT * FROM categories WHERE name = :name LIMIT 1');
        $statement->execute([':name' => trim($name)]);
        $category = $statement->fetch();

        return $category !== false ? $category : null;
    }
}
