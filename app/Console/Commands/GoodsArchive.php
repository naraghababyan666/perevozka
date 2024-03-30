<?php

namespace App\Console\Commands;

use App\Models\GoodsOrders;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GoodsArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goods:archive';

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
        $goods = GoodsOrders::all();
//        foreach ($goods as $good){
//            if($good['is_disabled'] == '0'){
//                if($good['end_date'] < Carbon::now()){
//                    GoodsOrders::query()->where('id', $good['id'])->update([
//                        'is_disabled' => '1',
//                        'in_archive_date' => Carbon::now()->addDays(15)]
//                    );
//                }
//            }else{
//                if($good['in_archive_date'] != null){
//                    $now = Carbon::now();
//                    $inArchiveDate = Carbon::parse($good['in_archive_date']);
//                    $diff = $inArchiveDate->diffInDays($now);
//                    if($diff >= 15){
//                        GoodsOrders::query()->where('id', $good['id'])->delete();
//                    }
//                }else{
//                    GoodsOrders::query()->where('id', $good['id'])->update([
//                        'in_archive_date' => Carbon::now()->addDays(15)]
//                    );
//                }
//
//            }
//        }
        Log::info('hiiiiiiiiiiiiiiiiiiiiiiiiiiii');
    }
}
