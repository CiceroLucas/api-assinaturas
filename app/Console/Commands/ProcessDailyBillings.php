<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Enums\SubscriptionStatus;
use App\Jobs\ProcessBillingJob;
use Carbon\Carbon;

class ProcessDailyBillings extends Command
{
    protected $signature = 'billing:process';
    protected $description = 'Busca assinaturas ativas com vencimento para hoje ou anterior e envia para a fila de cobrança';

    public function handle(): void
    {
        $this->info('Iniciando varredura de assinaturas...');

        $subscriptions = Subscription::with('plan') 
            ->where('status', SubscriptionStatus::ACTIVE)
            ->whereDate('next_billing_date', '<=', Carbon::today())
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('Nenhuma assinatura para cobrar hoje.');
            return;
        }

        $bar = $this->output->createProgressBar(count($subscriptions));

        foreach ($subscriptions as $subscription) {
            ProcessBillingJob::dispatch($subscription);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Todas as cobranças foram enviadas para a fila.');
    }
}