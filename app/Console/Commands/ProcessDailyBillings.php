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
    protected $description = 'Processa cobranças diárias e encerra assinaturas canceladas que chegaram ao fim do ciclo';

    public function handle(): void
    {
        $this->info('Iniciando rotina diária de assinaturas...');
        $today = Carbon::today();

        $this->info('1/2: Buscando assinaturas para faturamento...');
        $subscriptionsToBill = Subscription::with('plan')
            ->where('status', SubscriptionStatus::ACTIVE)
            ->whereDate('next_billing_date', '<=', $today)
            ->whereNull('cancelled_at')
            ->get();

        if ($subscriptionsToBill->isEmpty()) {
            $this->warn('Nenhuma assinatura para cobrar hoje.');
        } else {
            $bar = $this->output->createProgressBar($subscriptionsToBill->count());
            foreach ($subscriptionsToBill as $subscription) {
                ProcessBillingJob::dispatch($subscription);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info($subscriptionsToBill->count() . ' cobranças enviadas para a fila.');
        }

        $this->newLine();

        $this->info('2/2: Verificando assinaturas canceladas que expiraram hoje...');
        $subscriptionsToExpire = Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->whereDate('next_billing_date', '<=', $today)
            ->whereNotNull('cancelled_at')
            ->get();

        if ($subscriptionsToExpire->isEmpty()) {
            $this->warn('Nenhuma assinatura para expirar hoje.');
        } else {
            foreach ($subscriptionsToExpire as $subscription) {
                $subscription->update([
                    'status' => SubscriptionStatus::CANCELED
                ]);
            }
            $this->info($subscriptionsToExpire->count() . ' assinaturas foram encerradas definitivamente.');
        }

        $this->newLine();
        $this->info('Rotina diária finalizada com sucesso!');
    }
}
