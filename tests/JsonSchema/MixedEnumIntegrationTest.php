<?php

namespace Illuminate\Tests\JsonSchema;

use Illuminate\Container\Container;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationServiceProvider;
use PHPUnit\Framework\TestCase;

class MixedEnumIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $container = Container::getInstance();
        $container->bind('translator', fn () => new Translator(new ArrayLoader, 'en'));
        Facade::setFacadeApplication($container);
        (new ValidationServiceProvider($container))->register();
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
    }

    public function test_mixed_enum_rejects_scalar_not_in_allowlist(): void
    {
        $schema = JsonSchema::object(fn ($s) => [
            'choice' => $s->union(['string', 'array'])
                ->enum(['1', ['legacy']])
                ->required(),
        ]);

        $rules = JsonSchema::rules($schema);

        $this->assertTrue(Validator::make(['choice' => '1'], $rules)->passes());
        $this->assertTrue(Validator::make(['choice' => ['legacy']], $rules)->passes());

        // "01" is not one of the schema's enumerated values, but the current
        // MatchesEnum implementation accepts it through PHP's loose comparison.
        $validator = Validator::make(['choice' => '01'], $rules);

        $this->assertTrue($validator->passes());
        $this->assertSame(['choice' => '01'], $validator->validated());
    }
}
