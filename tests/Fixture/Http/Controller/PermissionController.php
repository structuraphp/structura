<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Fixture\Http\Controller;

use StructuraPhp\Structura\Tests\Fixture\Concerns;
use StructuraPhp\Structura\Tests\Fixture\Contract;
use StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase;
use StructuraPhp\Structura\Tests\Fixture\Models\User;

class PermissionController extends ControllerBase implements Contract\ShouldQueueInterface
{
    use Concerns\HasFactory;

    public function foo(User $user, int $roleId): RoleController
    {
        $queue = new class implements Contract\ShouldQueueInterface {
            public function applyDummyDomainEvent(int $anInteger): void {}

            public function getEventsTypes(): string
            {
                return '';
            }
        };

        echo $queue->getEventsTypes();

        return new RoleController();
    }
}
