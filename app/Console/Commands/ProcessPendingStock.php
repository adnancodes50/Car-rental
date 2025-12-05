<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EquipmentPurchase;
use App\Models\EquipmentStock;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessPendingStock extends Command
{
    protected $signature = 'stock:process-pending';
    protected $description = 'Update stock for purchases based on booking end date';

    public function handle()
    {
        $today = Carbon::today();

        // Get purchases that are paid and have a booking ending today or earlier
        $purchases = EquipmentPurchase::with('booking')
            ->where('payment_status', 'paid')
            ->get()
            ->filter(function($purchase) use ($today) {
                return $purchase->booking && $purchase->booking->end_date <= $today;
            });

        foreach ($purchases as $purchase) {
            DB::transaction(function() use ($purchase) {
                $stockRow = EquipmentStock::where('equipment_id', $purchase->equipment_id)
                    ->where('location_id', $purchase->location_id)
                    ->lockForUpdate()
                    ->first();

                if ($stockRow) {
                    $stockBefore = $stockRow->stock;
                    $stockRow->stock = max($stockBefore - $purchase->quantity, 0);
                    $stockRow->save();

                    $purchase->stock_before = $stockBefore;
                    $purchase->stock_after = $stockRow->stock;
                    $purchase->save();
                }
            });
        }

        $this->info('Processed stock for ' . $purchases->count() . ' purchases.');
    }
}
