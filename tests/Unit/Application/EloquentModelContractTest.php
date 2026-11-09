<?php

namespace Tests\Unit\Application;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class EloquentModelContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return iterable<string, array{class-string<Model>}>
     */
    public static function modelProvider(): iterable
    {
        foreach (AppClassDiscovery::eloquentModelClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider modelProvider
     *
     * @param  class-string<Model>  $class
     */
    public function test_model_exposes_non_empty_table_name(string $class): void
    {
        /** @var Model $model */
        $model = new $class;

        $this->assertNotSame('', $model->getTable());
    }

    /**
     * @dataProvider modelProvider
     *
     * @param  class-string<Model>  $class
     */
    public function test_model_query_builder_runs_without_sql_error(string $class): void
    {
        /** @var Model $model */
        $model = new $class;

        $model->newQuery()->limit(1)->get();

        $this->assertTrue(true);
    }
}
