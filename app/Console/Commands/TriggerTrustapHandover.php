<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatusEnum;
use App\Models\EntityTrustapTransaction;
use App\Services\v1\Payment\TrustapPaymentGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
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
        // info("Trustap Handover(Cron Job) running at " . now());
        $err_count = 0;
        EntityTrustapTransaction::where('status', PaymentStatusEnum::DELIVERED->value)
            ->where('delivered_at', '<=', now()->subDays(EntityTrustapTransaction::COMPLAINT_PERIOD_DAYS_AFTER_DELIVERY))
            ->chunk(100, function ($transactions) use ($err_count) {
                foreach ($transactions as $transaction) {
                    try {
                        $buyerId = $transaction->buyerId;
                        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'Trustap-User' => $buyerId,
                            ])
                            ->post(config('services.trustap.url') . "/p2p/transactions/$transaction->transactionId/confirm_handover_with_guest_user");
                            if ($response->successful()) {
                                $transaction->update([
                                    'status' => PaymentStatusEnum::HANDOVERED->value,
                                ]);
                            } else {
                                Log::channel('payment')->error('Failed to confirm handover', [
                                    'transaction_id' => $transaction->id,
                                    'error' => json_decode($response->body()),
                                ]);                                
                            }
                    } catch (\Exception $e) {
                        Log::channel('payment')->error('Failed to confirm handover', [
                            'transaction_id' => $transaction->id,
                            'error' => $e->getMessage(),
                        ]);
                        $err_count++;
                    }
                }
            });
        // info("Trustap Handover(Cron Job) finished at " . now() . ' with ' . $err_count . ' errors');
    }
}
