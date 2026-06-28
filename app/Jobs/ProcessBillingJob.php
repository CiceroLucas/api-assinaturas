<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Enums\InvoiceStatus;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription
    ) {}

    public function handle(): void
    {

        if ($this->subscription->cancelled_at !== null) {
            $this->delete();
            return;
        }

        DB::transaction(function () {
            try {
                $invoice = $this->subscription->invoices()->create([
                    'amount' => $this->subscription->plan->price,
                    'status' => InvoiceStatus::PAID,
                    'due_date' => Carbon::today(),
                    'payment_date' => Carbon::today(),
                ]);

                $this->subscription->update([
                    'next_billing_date' => Carbon::today()->addDays($this->subscription->plan->billing_cycle_in_days)
                ]);

                Log::info("Cobrança processada para assinatura {$this->subscription->id}. Fatura: {$invoice->id}");

            } catch (\Exception $e) {
                Log::error("Falha ao processar assinatura {$this->subscription->id}: {$e->getMessage()}");
                
                throw $e;
            }
        });
    }
}
