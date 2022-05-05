<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Services\TxService;
use App\Models\Tx;

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Collection;

use App\Events\CheckNftHolderEvent;

class TxObserver
{

    public function creating(Tx $tx)
    {
        Log::info('Tx Observer 监听到creating事件');
    }

    /*
    *   发送Tx对应的mint的信息
    *   1.需要发送管理员频道信息
    *   2.需要发送普通频道信息
    */
    public function sendMintMessage($tx) {

        if (!$tx->club) {
            Log::debug('发送mint消息时候发现tx对应的club是空,tx_hash:'.$tx->tx_hash);
            return false;
        }
        
        if (!$tx->club->discord_guild) {
            Log::debug('发送mint消息时候发现tx对应的club的discord社区是空,tx_hash:'.$tx->tx_hash);
        }

        $msg = sprintf(
            '[Mint reminder]  A new Mint transaction was found with the wallet address: %s , price : %f ETH , view here: https://etherscan.io/tx/%s',
            $tx->to_address,
            $tx->price,
            $tx->tx_hash,
        );

        $tx->club->discord_guild->sendMessage($msg);

        $msg = sprintf(
            '[Mint reminder]  A new Mint transaction was found with the wallet address: %s , price : %f ETH , total mint income is : %f ETH, view here: https://etherscan.io/tx/%s',
            $tx->to_address,
            $tx->price,
            $tx->club->total_mint_income,
            $tx->tx_hash,
        );

        $tx->club->discord_guild->sendAdminMessage($msg);

        return  true;
    }


    /*
    *   发送Tx对应的mint的信息
    *   1.需要发送管理员频道信息
    *   2.需要发送普通频道信息
    */
    public function sendSaleMessage($tx) {

        if (!$tx->club) {
            Log::debug('发送mint消息时候发现tx对应的club是空,tx_hash:'.$tx->tx_hash);
            return false;
        }
        
        if (!$tx->club->discord_guild) {
            Log::debug('发送mint消息时候发现tx对应的club的discord社区是空,tx_hash:'.$tx->tx_hash);
        }

        $msg = sprintf(
            '[Sale reminder]  A new Sale transaction was found with the wallet address: %s , price : %f ETH , view here: https://etherscan.io/tx/%s',
            $tx->to_address,
            $tx->price,
            $tx->tx_hash,
        );

        $tx->club->discord_guild->sendMessage($msg);

        $msg = sprintf(
            '[Sale reminder]  A new Sale transaction was found with the wallet address: %s , price : %f ETH , total rayalty income is : %f ETH, view here: https://etherscan.io/tx/%s',
            $tx->to_address,
            $tx->price,
            $tx->club->total_rayalty_income,
            $tx->tx_hash,
        );

        $tx->club->discord_guild->sendAdminMessage($msg);

        return  true;
    }


    /**
     * Handle the post "created" event.
     *
     * @return void
     */
    public function created(Tx $tx)
    {
        Log::info('Tx Observer 监听到创建了条目:'.json_encode($tx->toArray()));

        /*
        *   1.transfer事件
        *       1.1 检查卖出者是否还持有NFT，如果不再持有，则踢出这个用户
        *       1.2 检查买入者是否已经加入社区，如果没有加入的话，把他升级到Member用户
        *   2.mint 事件
        *       2.1 mint事件累加用户购买时候付款的金额到CLUB表
        *   3.sale 事件
        *       3.1 sale时候按照分账比例，累计分账金额到CLUB表   
        */


        if (!$tx->club) {
            Log::info('执行的时候没有找到对应的club，直接返回');
            return;
        }

        switch($tx->tx_type) {

            case 'transfer':
                ///调用NFT的异步检查程序
                event(new CheckNftHolderEvent($tx->contract_address,$tx->from_address));
                event(new CheckNftHolderEvent($tx->contract_address,$tx->to_address));
                break;

            case 'mint':
                $tx->club->total_mint_income = bcadd($tx->club->total_mint_income,$tx->price,18);
                $tx->club->save();

                $this->sendMintMessage($tx);

                ///调用NFT的异步检查程序
                Log::debug('调用CheckNftHolderEvent事件');
                event(new CheckNftHolderEvent($tx->contract_address,$tx->to_address));
                break; 

            case 'sale':
                $tx->club->total_rayalty_income = bcadd($tx->club->total_rayalty_income,$tx->rayalty,18);
                $tx->club->save();

                $this->sendSaleMessage($tx);
                break;
        }

        
    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function updated(Tx $tx)
    {
        //
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Tx  $order
     * @return void
     */
    public function deleted(Tx $tx)
    {

    }

    /**
     * Handle the post "restored" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function restored(Tx $tx)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Post  $order
     * @return void
     */
    public function forceDeleted(Tx $order)
    {
        //
    }



}
