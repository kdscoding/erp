<?php

namespace App\Console\Commands;

use App\Support\LabelRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LabelAudit extends Command
{
    protected $signature = 'labels:audit 
                            {--module= : Specific module to audit}
                            {--fix : Show suggested fixes}
                            {--json : Output as JSON}';

    protected $description = 'Audit hardcoded labels in views and controllers against LabelRegistry';

    public function handle(): int
    {
        LabelRegistry::init();

        $modules = $this->option('module') 
            ? [$this->option('module')] 
            : LabelRegistry::allModules();

        $results = [];

        foreach ($modules as $module) {
            $this->info("Auditing module: {$module}");
            $results[$module] = $this->auditModule($module);
        }

        if ($this->option('json')) {
            $this->output->write(json_encode($results, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->displayResults($results);

        return 0;
    }

    protected function auditModule(string $module): array
    {
        $registryLabels = $this->collectRegistryLabels($module);
        $viewLabels = $this->scanViews($module);
        $controllerLabels = $this->scanControllers($module);

        $allFoundLabels = array_merge($viewLabels, $controllerLabels);
        $missingInRegistry = [];
        $unusedInRegistry = [];

        foreach ($allFoundLabels as $label) {
            if (!isset($registryLabels[$label])) {
                $missingInRegistry[] = $label;
            }
        }

        foreach ($registryLabels as $key => $value) {
            $found = false;
            foreach ($allFoundLabels as $label) {
                if (Str::contains($label, $key) || Str::contains($key, $label)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $unusedInRegistry[] = $key;
            }
        }

        return [
            'module' => $module,
            'registry_labels_count' => count($registryLabels),
            'found_labels_count' => count($allFoundLabels),
            'missing_in_registry' => array_values(array_unique($missingInRegistry)),
            'unused_in_registry' => array_values(array_unique($unusedInRegistry)),
        ];
    }

    protected function collectRegistryLabels(string $module): array
    {
        $data = LabelRegistry::get($module);
        $labels = [];

        $this->flattenLabels($data, '', $labels);

        return $labels;
    }

    protected function flattenLabels(array $data, string $prefix, array &$labels): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->flattenLabels($value, $fullKey, $labels);
            } elseif (is_string($value)) {
                $labels[$fullKey] = $value;
            }
        }
    }

    protected function scanViews(string $module): array
    {
        $labels = [];
        $viewPaths = $this->getViewPathsForModule($module);

        foreach ($viewPaths as $path) {
            if (is_file($path)) {
                $labels = array_merge($labels, $this->extractLabelsFromFile($path));
            } elseif (is_dir($path)) {
                $files = File::glob($path . '/*.blade.php');
                foreach ($files as $file) {
                    $labels = array_merge($labels, $this->extractLabelsFromFile($file));
                }
            }
        }

        return $labels;
    }

    protected function getViewPathsForModule(string $module): array
    {
        $map = [
            'supplier' => [
                resource_path('views/suppliers'),
            ],
            'item' => [
                resource_path('views/masters/items'),
            ],
            'item_category' => [
                resource_path('views/masters/item-categories'),
            ],
            'unit' => [
                resource_path('views/masters/units'),
            ],
            'purchase_order' => [
                resource_path('views/po'),
            ],
            'shipment' => [
                resource_path('views/shipments'),
            ],
            'tracking' => [
                resource_path('views/tracking.blade.php'),
            ],
        ];

        return $map[$module] ?? [resource_path("views/{$module}"), resource_path("views/masters/{$module}")];
    }

    protected function extractLabelsFromFile(string $file): array
    {
        $content = File::get($file);
        $labels = [];

        // Remove Blade template syntax to avoid false positives
        $content = preg_replace('/\{\{.*?\}\}/', '', $content);
        $content = preg_replace('/\{!!.*?!!\}/', '', $content);
        $content = preg_replace('/@php.*?@endphp/is', '', $content);
        $content = preg_replace('/@.*?\(/is', '', $content);

        // Extract <th>...</th>
        preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $content, $matches);
        foreach ($matches[1] as $match) {
            $clean = strip_tags($match);
            $clean = trim(preg_replace('/\s+/', ' ', $clean));
            if ($clean && !Str::startsWith($clean, '{') && !Str::startsWith($clean, '@') && !Str::startsWith($clean, '$')) {
                $labels[] = $clean;
            }
        }

        // Extract <label>...</label>
        preg_match_all('/<label[^>]*>(.*?)<\/label>/is', $content, $matches);
        foreach ($matches[1] as $match) {
            $clean = strip_tags($match);
            $clean = trim(preg_replace('/\s+/', ' ', $clean));
            if ($clean && !Str::startsWith($clean, '{') && !Str::startsWith($clean, '@') && !Str::startsWith($clean, '$')) {
                $labels[] = $clean;
            }
        }

        // Extract placeholder="..."
        preg_match_all('/placeholder\s*=\s*["\']([^"\']+)["\']/i', $content, $matches);
        foreach ($matches[1] as $match) {
            $clean = trim($match);
            if ($clean && !Str::startsWith($clean, '{') && !Str::startsWith($clean, '@') && !Str::startsWith($clean, '$')) {
                $labels[] = $clean;
            }
        }

        // Extract title="..."
        preg_match_all('/title\s*=\s*["\']([^"\']+)["\']/i', $content, $matches);
        foreach ($matches[1] as $match) {
            $clean = trim($match);
            if ($clean && !Str::startsWith($clean, '{') && !Str::startsWith($clean, '@') && !Str::startsWith($clean, '$')) {
                $labels[] = $clean;
            }
        }

        // Extract aria-label="..."
        preg_match_all('/aria-label\s*=\s*["\']([^"\']+)["\']/i', $content, $matches);
        foreach ($matches[1] as $match) {
            $clean = trim($match);
            if ($clean && !Str::startsWith($clean, '{') && !Str::startsWith($clean, '@') && !Str::startsWith($clean, '$')) {
                $labels[] = $clean;
            }
        }

        return $labels;
    }

    protected function scanControllers(string $module): array
    {
        $labels = [];
        $controllerPaths = [
            app_path("Http/Controllers/{$module}Controller.php"),
            app_path("Http/Controllers/Masters/{$module}Controller.php"),
        ];

        foreach ($controllerPaths as $path) {
            if (File::exists($path)) {
                $content = File::get($path);

                // Extract validation messages
                preg_match_all("/'([^']+\.(?:required|unique|max|min|exists|in))'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $matches);
                foreach ($matches[2] as $match) {
                    $labels[] = $match;
                }

                // Extract generic required messages
                preg_match_all("/'required'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $matches);
                foreach ($matches[1] as $match) {
                    $labels[] = $match;
                }
            }
        }

        return $labels;
    }

    protected function displayResults(array $results): void
    {
        $totalMissing = 0;
        $totalUnused = 0;

        foreach ($results as $module => $result) {
            $this->newLine();
            $this->line("<fg=cyan>=== {$module} ===</>");
            $this->line("Registry labels: {$result['registry_labels_count']}");
            $this->line("Found in views/controllers: {$result['found_labels_count']}");
            $this->line("Missing in registry: " . count($result['missing_in_registry']));
            $this->line("Unused in registry: " . count($result['unused_in_registry']));

            $totalMissing += count($result['missing_in_registry']);
            $totalUnused += count($result['unused_in_registry']);

            if ($this->option('fix') && !empty($result['missing_in_registry'])) {
                $this->line("<fg=yellow>Missing labels (add to resources/labels/{$module}.php):</>");
                foreach ($result['missing_in_registry'] as $label) {
                    $this->line("  - {$label}");
                }
            }
        }

        $this->newLine();
        $this->line("<fg=green>=== SUMMARY ===</>");
        $this->line("Total missing in registry: {$totalMissing}");
        $this->line("Total unused in registry: {$totalUnused}");

        if ($totalMissing > 0) {
            $this->warn("Run with --fix to see suggested labels to add.");
        }
    }
}