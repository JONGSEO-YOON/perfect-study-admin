<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParseTextFileCommand extends Command
{
    protected $signature = 'parse:text {filename}';
    protected $description = 'Parse text file and convert to PHP array structure';

    public function handle()
    {
        $_filename = $this->argument('filename');
        $store_path = storage_path('app');
        $filename = storage_path('app/' . $_filename);


        if (!file_exists($filename)) {
            $this->error("File not found: {$filename}");
            return 1;
        }

        // Read the file
        $content = file_get_contents($filename);
        $lines = explode("\n", $content);

        $sections = [];
        $currentSection = null;
        $order = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a main section (starts with number and dot)
            if (preg_match('/^\d+-\d+\.\s+(.+)$/', $line, $matches)) {
                if ($currentSection) {
                    $sections[] = $currentSection;
                }
                $currentSection = [
                    'name' => $matches[1],
                    'type' => 'scope',
                    'depth' => 2,
                    'order' => $order++,
                    'children' => []
                ];
            }
            // Check if it's a question type (starts with [유형])
            elseif (preg_match('/^\[유형\s+(\d+)\]\s+(.+)$/', $line, $matches)) {
                if ($currentSection) {
                    $currentSection['children'][] = [
                        'name' => $matches[2],
                        'type' => 'question_type',
                        'depth' => 3,
                        'order' => (int)$matches[1]
                    ];
                }
            }
        }

        // Add the last section
        if ($currentSection) {
            $sections[] = $currentSection;
        }

        // Generate output filename
        $outputFilename = pathinfo($filename, PATHINFO_FILENAME) . '.php';
        $outputFilename = $store_path . '/' . $outputFilename;

        // Create PHP file content
        $output = "<?php\n\nreturn " . $this->varExport($sections) . ";\n";

        // Save to file
        file_put_contents($outputFilename, $output);

        $this->info("Successfully created {$outputFilename}");
        return 0;
    }

    private function varExport($expression, $indent = '')
    {
        switch (gettype($expression)) {
            case 'array':
                $lines = [];
                $association = array_keys($expression) !== range(0, count($expression) - 1);
                $indent2 = $indent . '    ';

                foreach ($expression as $key => $value) {
                    $key = $association ? "'" . addslashes($key) . "'" : '';
                    $lines[] = $indent2 . ($association ? $key . ' => ' : '')
                        . $this->varExport($value, $indent2);
                }

                return "[\n" . implode(",\n", $lines) . "\n" . $indent . "]";

            case 'string':
                return "'" . addslashes($expression) . "'";

            default:
                return $expression;
        }
    }
}
