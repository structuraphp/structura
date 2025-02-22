<?php

declare(strict_types=1);

namespace Structura\Tests\Fixture\Http\Request;

class UserRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
