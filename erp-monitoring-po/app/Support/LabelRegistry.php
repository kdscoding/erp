<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LabelRegistry
{
    protected static array $registry = [];
    protected static bool $loaded = false;
    protected static string $labelsPath = '';

    public static function init(): void
    {
        self::$labelsPath = resource_path('labels');
        self::load();
    }

    protected static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        if (self::$labelsPath === '') {
            self::$labelsPath = resource_path('labels');
        }

        self::$registry = Cache::rememberForever('label_registry', function () {
            $registry = [];
            $files = File::glob(self::$labelsPath . '/*.php');

            foreach ($files as $file) {
                $module = basename($file, '.php');
                $data = require $file;

                if (is_array($data)) {
                    $registry[$module] = $data;
                }
            }

            return $registry;
        });

        self::$loaded = true;
    }

    public static function get(string $module, string $key = '', mixed $default = null): mixed
    {
        self::load();

        if ($key === '') {
            return self::$registry[$module] ?? $default;
        }

        $keys = explode('.', $key);
        $data = self::$registry[$module] ?? null;

        foreach ($keys as $k) {
            if (!is_array($data) || !array_key_exists($k, $data)) {
                return $default;
            }
            $data = $data[$k];
        }

        return $data;
    }

    public static function entity(string $module, string $type = 'singular'): string
    {
        return self::get($module, "entity.{$type}", Str::studly($module));
    }

    public static function field(string $module, string $field, string $attribute = 'label'): string
    {
        return self::get($module, "fields.{$field}.{$attribute}", Str::ucfirst(str_replace('_', ' ', $field)));
    }

    public static function fieldLabel(string $module, string $field): string
    {
        return self::field($module, $field, 'label');
    }

    public static function fieldPlaceholder(string $module, string $field): string
    {
        return self::field($module, $field, 'placeholder');
    }

    public static function fieldHelp(string $module, string $field): string
    {
        return self::field($module, $field, 'help');
    }

    public static function tableColumns(string $module, string $view = 'index'): array
    {
        return self::get($module, "table_columns.{$view}", []);
    }

    public static function columnLabel(string $module, string $column, string $view = 'index'): string
    {
        $columns = self::tableColumns($module, $view);
        return $columns[$column] ?? self::fieldLabel($module, $column);
    }

    public static function action(string $module, string $action): string
    {
        return self::get($module, "actions.{$action}", Str::ucfirst($action));
    }

    public static function validation(string $module, string $rule): string
    {
        return self::get($module, "validation.{$rule}", self::get('shared', "validation.{$rule}", ''));
    }

    public static function validationMessages(string $module): array
    {
        $moduleMessages = self::get($module, 'validation', []);
        $sharedMessages = self::get('shared', 'validation', []);

        return array_merge($sharedMessages, $moduleMessages);
    }

    public static function statusOptions(string $scope): array
    {
        return self::get('statuses', "{$scope}.options", []);
    }

    public static function statusLabel(string $scope, string $value): string
    {
        $options = self::statusOptions($scope);
        return $options[$value] ?? $value;
    }

    public static function filterFields(string $module): array
    {
        return self::get($module, 'filter_fields', []);
    }

    public static function allModules(): array
    {
        self::load();
        return array_keys(self::$registry);
    }

    public static function hasModule(string $module): bool
    {
        self::load();
        return isset(self::$registry[$module]);
    }

    public static function clearCache(): void
    {
        Cache::forget('label_registry');
        self::$loaded = false;
        self::$registry = [];
    }

    public static function refresh(): void
    {
        self::clearCache();
        self::load();
    }
}