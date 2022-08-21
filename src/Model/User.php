<?php

declare(strict_types=1);

namespace App\Model;

use App\Attribute\ORM\Column;
use App\Attribute\ORM\Table;
use App\Core\Model;
use App\Exception\AppException;
use App\Util\DateTimeUtils;
use Ramsey\Uuid\Uuid;

#[Table(name: 'users')]
class User extends Model
{
    #[Column(name: 'id', type: 'varchar', length: 30)]
    public string $id;

    #[Column(name: 'name', type: 'varchar', length: 200, order: 'asc')]
    public string $name;

    #[Column(name: 'created_at', type: 'datetime')]
    public \DateTimeImmutable $createdAt;

    /**
     * @throws AppException
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->name = '';
        $this->createdAt = DateTimeUtils::now();
    }
}
