<?php

namespace Tests\Browser\InteractionParity;

use Tests\TestCase;

/**
 * Phase gate test: verifies every row in the interaction checklist
 * maps to an existing Dusk test class and method.
 *
 * Fails the phase gate if any control is unmapped or its test does not exist.
 */
class RunInteractionGateTest extends TestCase
{
    private const CHECKLIST_PATH = 'docs/modernization/interaction-checklist.md';

    private const TEST_NAMESPACE = 'Tests\\Browser\\InteractionParity\\';

    public function test_all_checklist_rows_are_mapped_to_existing_tests(): void
    {
        $checklistPath = base_path(self::CHECKLIST_PATH);
        $this->assertFileExists($checklistPath, 'Interaction checklist not found');

        $content = file_get_contents($checklistPath);
        $rows = $this->parseChecklistRows($content);

        $this->assertNotEmpty($rows, 'No checklist rows found');

        $missing = [];
        $total = count($rows);
        $passing = 0;

        foreach ($rows as $row) {
            $parts = explode('::', $row['dusk_test']);
            if (count($parts) !== 2) {
                $missing[] = "{$row['id']}: Invalid test reference format '{$row['dusk_test']}'";
                continue;
            }

            [$class, $method] = $parts;
            $fqcn = self::TEST_NAMESPACE . $class;

            if (!class_exists($fqcn)) {
                $missing[] = "{$row['id']}: Test class '{$fqcn}' does not exist";
                continue;
            }

            if (!method_exists($fqcn, $method)) {
                $missing[] = "{$row['id']}: Method '{$method}' not found in '{$fqcn}'";
                continue;
            }

            $passing++;
        }

        $result = [
            'total_rows' => $total,
            'passing' => $passing,
            'failing' => count($missing),
            'missing_mappings' => $missing,
            'gate_passed' => empty($missing),
            'timestamp' => now()->toIso8601String(),
        ];

        $outputPath = storage_path('app/interaction-gate-result.json');
        file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->assertEmpty(
            $missing,
            "Phase gate FAILED: " . count($missing) . " unmapped rows:\n" . implode("\n", $missing)
        );
    }

    private function parseChecklistRows(string $content): array
    {
        $rows = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (!preg_match('/^\|\s*([\w-]+)\s*\|.*?\|.*?\|.*?\|.*?\|\s*`([^`]+)`\s*\|/', $line, $matches)) {
                continue;
            }

            $id = trim($matches[1]);
            $testRef = trim($matches[2]);

            if ($id === '#' || $id === 'Screen' || $id === 'Metric') {
                continue;
            }

            $rows[] = [
                'id' => $id,
                'dusk_test' => $testRef,
            ];
        }

        return $rows;
    }
}
