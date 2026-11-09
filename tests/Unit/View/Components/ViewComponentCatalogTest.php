<?php

namespace Tests\Unit\View\Components;

use App\View\Components\Date;
use Illuminate\View\Component;
use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ViewComponentCatalogTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<Component>}>
     */
    public static function componentProvider(): iterable
    {
        foreach (AppClassDiscovery::viewComponentClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider componentProvider
     */
    public function test_component_extends_base_view_component(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Component::class));
    }

    /**
     * @dataProvider componentProvider
     */
    public function test_component_declares_render_method(string $class): void
    {
        $this->assertTrue((new ReflectionClass($class))->hasMethod('render'));
    }

    /** @test */
    public function date_component_points_at_date_blade_view(): void
    {
        $view = (new Date)->render();

        $this->assertSame('components.date', $view->name());
    }

    /** @test */
    public function catalog_lists_every_discovered_view_component(): void
    {
        $listed = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(self::componentProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::viewComponentClasses(), $listed);
    }
}
