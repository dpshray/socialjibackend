<?php

namespace App\Console\Commands;

use App\Models\EntityTrustapTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TriggerTrustapHandover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TrustapHandover:Trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Trustap Handover(Cron Job) running at " . now());
        $err_count = 0;
        EntityTrustapTransaction::whereDate('complaintPeriodDeadline', now()->toDateString())
            ->where('complaintPeriodDeadline', '<=', now())
            ->chunk(100, function ($transactions) use($err_count) {
                foreach ($transactions as $transaction) {
                    try {
                        $transaction->trustapPaymentGateway->buyerConfirmsHandover($transaction);
                    } catch (\Exception $e) {
                        Log::channel('payment')->error('Failed to confirm handover', [
                            'transaction_id' => $transaction->id,
                            'error' => $e->getMessage(),
                        ]);
                        $err_count++;
                    }
                }
            });
        info("Trustap Handover(Cron Job) finished at " . now().' with '.$err_count.' errors');
    }
}
