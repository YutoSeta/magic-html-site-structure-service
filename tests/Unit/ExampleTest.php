<?php

namespace Tests\Unit;

use App\Support\CanonicalJson;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function test_canonical_json_sorts_object_keys_but_preserves_list_order(): void
    {
        $encoded = CanonicalJson::encode([
            'z' => ['second', 'first'],
            'a' => ['z' => 1, 'a' => 2],
        ]);

        $this->assertSame('{"a":{"a":2,"z":1},"z":["second","first"]}', $encoded);
    }
}
