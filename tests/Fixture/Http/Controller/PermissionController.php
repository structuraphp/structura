<?php

declare(strict_types=1);

namespace Structura\Tests\Fixture\Http\Controller;

use Structura\Tests\Fixture\Concerns;
use Structura\Tests\Fixture\Contract;
use Structura\Tests\Fixture\Http\ControllerBase;
use Structura\Tests\Fixture\Models\User;

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
