<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventRegistration;
use Illuminate\Support\Str;

class GenerateAttendanceCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-codes {--force : Force regenerate all codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate attendance codes for event registrations that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        if ($force) {
            $registrations = EventRegistration::all();
            $this->info('Regenerating attendance codes for ALL registrations...');
        } else {
            $registrations = EventRegistration::whereNull('attendance_code')->get();
            $this->info('Generating attendance codes for registrations without codes...');
        }

        if ($registrations->count() === 0) {
            $this->info('No registrations need attendance codes.');
            return Command::SUCCESS;
        }

        $this->info("Found {$registrations->count()} registrations to process.");

        $progressBar = $this->output->createProgressBar($registrations->count());
        $progressBar->start();

        $generated = 0;
        foreach ($registrations as $registration) {
            $code = $this->generateUniqueCode();
            $registration->update(['attendance_code' => $code]);
            $generated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Successfully generated {$generated} attendance codes.");

        return Command::SUCCESS;
    }

    /**
     * Generate a unique attendance code
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (EventRegistration::where('attendance_code', $code)->exists());

        return $code;
    }
}
