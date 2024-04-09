<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\GoodsOrders;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GoodsArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $goods = GoodsOrders::all();
        foreach ($goods as $good){
            if($good['is_disabled'] == '0'){
                if($good['end_date'] < Carbon::now()){
                    GoodsOrders::query()->where('id', $good['id'])->update([
                            'is_disabled' => '1',
                            'in_archive_date' => Carbon::now()->addDays(15)]
                    );
                }
            }else{
                if($good['in_archive_date'] != null){
                    $now = Carbon::now();
                    $inArchiveDate = Carbon::parse($good['in_archive_date']);
                    $diff = $inArchiveDate->diffInDays($now);
                    if($diff >= 15){
                        GoodsOrders::query()->where('id', $good['id'])->delete();
                    }
                }else{
                    GoodsOrders::query()->where('id', $good['id'])->update([
                            'in_archive_date' => Carbon::now()->addDays(15)]
                    );
                }

            }
        }
//        $now = Carbon::now();
//        $usr = Company::query()->find(1)->first();
//        $usr->phone_number = $now->valueOf();
//        $usr->save();
    }
}
