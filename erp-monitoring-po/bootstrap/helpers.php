<?php

use App\Support\LabelRegistry;

if (!function_exists('label')) {
    function label(string $module, string $key = '', mixed $default = null): mixed
    {
        return LabelRegistry::get($module, $key, $default);
    }
}

if (!function_exists('entity_label')) {
    function entity_label(string $module, string $type = 'singular'): string
    {
        return LabelRegistry::entity($module, $type);
    }
}

if (!function_exists('field_label')) {
    function field_label(string $module, string $field): string
    {
        return LabelRegistry::fieldLabel($module, $field);
    }
}

if (!function_exists('field_placeholder')) {
    function field_placeholder(string $module, string $field): string
    {
        return LabelRegistry::fieldPlaceholder($module, $field);
    }
}

if (!function_exists('field_help')) {
    function field_help(string $module, string $field): string
    {
        return LabelRegistry::fieldHelp($module, $field);
    }
}

if (!function_exists('table_columns')) {
    function table_columns(string $module, string $view = 'index'): array
    {
        return LabelRegistry::tableColumns($module, $view);
    }
}

if (!function_exists('column_label')) {
    function column_label(string $module, string $column, string $view = 'index'): string
    {
        return LabelRegistry::columnLabel($module, $column, $view);
    }
}

if (!function_exists('action_label')) {
    function action_label(string $module, string $action): string
    {
        return LabelRegistry::action($module, $action);
    }
}

if (!function_exists('validation_message')) {
    function validation_message(string $module, string $rule): string
    {
        return LabelRegistry::validation($module, $rule);
    }
}

if (!function_exists('validation_messages')) {
    function validation_messages(string $module): array
    {
        return LabelRegistry::validationMessages($module);
    }
}

if (!function_exists('status_options')) {
    function status_options(string $scope): array
    {
        return LabelRegistry::statusOptions($scope);
    }
}

if (!function_exists('status_label')) {
    function status_label(string $scope, string $value): string
    {
        return LabelRegistry::statusLabel($scope, $value);
    }
}

if (!function_exists('filter_fields')) {
    function filter_fields(string $module, string $context = 'default'): array
    {
        $fields = LabelRegistry::filterFields($module);
        return $fields[$context] ?? $fields;
    }
}