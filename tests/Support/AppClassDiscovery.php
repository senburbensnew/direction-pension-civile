<?php

namespace Tests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class AppClassDiscovery
{
    /**
     * @return list<class-string>
     */
    public static function phpClassesUnder(string $relativeAppPath = ''): array
    {
        $appRoot = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app';
        $root = $relativeAppPath === ''
            ? $appRoot
            : $appRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeAppPath);

        if (! is_dir($root)) {
            return [];
        }

        $finder = Finder::create()->files()->in($root)->name('*.php');
        $classes = [];

        foreach ($finder as $file) {
            $relative = Str::after($file->getRealPath(), $appRoot.DIRECTORY_SEPARATOR);
            $class = 'App\\'.str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

            if (! class_exists($class)) {
                continue;
            }

            $classes[] = $class;
        }

        sort($classes);

        return $classes;
    }

    /**
     * @return list<class-string>
     */
    public static function concreteClassesUnder(string $relativeAppPath = ''): array
    {
        return array_values(array_filter(
            self::phpClassesUnder($relativeAppPath),
            static function (string $class): bool {
                $reflection = new ReflectionClass($class);

                return ! $reflection->isAbstract()
                    && ! $reflection->isInterface()
                    && ! $reflection->isTrait();
            }
        ));
    }

    /**
     * @return list<class-string<FormRequest>>
     */
    public static function formRequestClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Http/Requests'),
            static fn (string $class): bool => is_subclass_of($class, FormRequest::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function enumClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Enums'),
            static fn (string $class): bool => (new ReflectionClass($class))->isEnum()
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function validationRuleClasses(): array
    {
        return self::concreteClassesUnder('Rules');
    }

    /**
     * @return list<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public static function eloquentModelClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Models'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function serviceClasses(): array
    {
        return self::concreteClassesUnder('Services');
    }

    /**
     * @return list<class-string>
     */
    public static function supportClasses(): array
    {
        return self::concreteClassesUnder('Support');
    }

    /**
     * @return list<class-string>
     */
    public static function helperClasses(): array
    {
        return self::concreteClassesUnder('Helpers');
    }

    /**
     * @return list<class-string>
     */
    public static function httpControllerClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Http/Controllers'),
            static fn (string $class): bool => is_subclass_of($class, \App\Http\Controllers\Controller::class)
                && $class !== \App\Http\Controllers\Controller::class
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function middlewareClasses(): array
    {
        return self::concreteClassesUnder('Http/Middleware');
    }

    /**
     * @return list<class-string>
     */
    public static function viewComponentClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('View/Components'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\View\Component::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function notificationClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Notifications'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Notifications\Notification::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function notificationChannelClasses(): array
    {
        return self::concreteClassesUnder('Notifications/Channels');
    }

    /**
     * @return list<class-string>
     */
    public static function policyClasses(): array
    {
        return self::concreteClassesUnder('Policies');
    }

    /**
     * @return list<class-string>
     */
    public static function mailableClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Mail'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Mail\Mailable::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function jobClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Jobs'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Contracts\Queue\ShouldQueue::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function listenerClasses(): array
    {
        return self::concreteClassesUnder('Listeners');
    }

    /**
     * @return list<class-string>
     */
    public static function eventClasses(): array
    {
        return self::concreteClassesUnder('Events');
    }

    /**
     * @return list<class-string>
     */
    public static function serviceProviderClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Providers'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Support\ServiceProvider::class)
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function observerClasses(): array
    {
        return self::concreteClassesUnder('Observers');
    }

    /**
     * @return list<class-string>
     */
    public static function consoleCommandClasses(): array
    {
        return array_values(array_filter(
            self::concreteClassesUnder('Console/Commands'),
            static fn (string $class): bool => is_subclass_of($class, \Illuminate\Console\Command::class)
        ));
    }
}
