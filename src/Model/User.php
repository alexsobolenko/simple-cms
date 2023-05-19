<?php

declare(strict_types=1);

namespace App\Model;

use App\Attribute\ORM;
use App\Core\ORM\Model;
use App\Util\DateTimeUtils;
use Ramsey\Uuid\Uuid;

#[ORM\Table('users')]
class User extends Model
{
    #[ORM\Column(name: 'id', type: 'varchar', length: 36)]
    public string $id;

    #[ORM\Column(name: 'name', type: 'varchar', length: 200, order: 'ASC')]
    public string $name;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    public \DateTimeImmutable $updatedAt;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->name = $name;
    }

    #[ORM\PreCreate]
    public function onPreCreate(): void
    {
        $now = DateTimeUtils::now();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = DateTimeUtils::now();
    }
}
