<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppStart extends Command
{
    protected $signature = 'whatsapp:start
        {--port=3001 : Port untuk WhatsApp service}
        {--log=info : Level log (info/debug/error)}';

    protected $description = 'Start the WhatsApp Baileys service';

    public function handle()
    {
        $port = $this->option('port');
        $logLevel = $this->option('log');

        $serviceDir = base_path('whatsapp-service');
        $logFile = storage_path('logs/whatsapp-service.log');

        $this->info('Starting WhatsApp service...');

        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                'start /B node "%s" --port=%s --log=%s > "%s" 2>&1',
                escapeshellarg("{$serviceDir}/server.js"),
                escapeshellarg($port),
                escapeshellarg($logLevel),
                escapeshellarg($logFile)
            );
        } else {
            $command = sprintf(
                'nohup node "%s" --port=%s --log=%s > "%s" 2>&1 &',
                escapeshellarg("{$serviceDir}/server.js"),
                escapeshellarg($port),
                escapeshellarg($logLevel),
                escapeshellarg($logFile)
            );
        }

        $output = shell_exec($command);
        $this->info("WhatsApp service started on port {$port}");
        $this->info("Log: {$logFile}");
    }
}
