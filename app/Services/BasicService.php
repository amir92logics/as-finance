<?php

namespace App\Services;


use App\Jobs\DistributeBonus;
use App\Models\Deposit;
use App\Models\InvestHistory;
use App\Models\InvestmentPlan;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectInvestment;
use App\Models\Transaction;
use App\Traits\Notify;

class BasicService
{
    use Notify;

    public function setEnv($value)
    {
        $envPath = base_path('.env');
        $env = file($envPath);
        foreach ($env as $env_key => $env_value) {
            $entry = explode("=", $env_value, 2);
            $env[$env_key] = array_key_exists($entry[0], $value) ? $entry[0] . "=" . $value[$entry[0]] . "\n" : $env_value;
        }
        $fp = fopen($envPath, 'w');
        fwrite($fp, implode($env));
        fclose($fp);
    }

    public function preparePaymentUpgradation($deposit)
    {

        try {
            if ($deposit->depositable_type == Deposit::class){

                if ($deposit->status == 0 || $deposit->status == 2) {
                     $this->deposit($deposit);
                }
            }elseif ($deposit->depositable_type == InvestmentPlan::class){
                  $this->plan($deposit);

            }elseif ($deposit->depositable_type == Project::class){
                 $this->project($deposit);

            }elseif ($deposit->depositable_type == Order::class && $deposit->depositable_id){
               return $this->order($deposit);
            }

            return false;

        } catch (\Exception $e) {
            return false;
        }
    }


    public function deposit($deposit)
    {
        $deposit->status = 1;
        $deposit->save();

        if ($deposit->user){
            $user = $deposit->user;
            $user->balance += $deposit->payable_amount_in_base_currency;
            $user->total_deposit += $deposit->payable_amount_in_base_currency;
            $user->save();

            $amount = getAmount($deposit->base_currency_charge);
            $transaction =  $this->makeTransaction($user,$deposit->payable_amount_in_base_currency,$amount,'+',$deposit->trx_id ,'Deposit Via ' . optional($deposit->gateway)->name,$deposit->depositable_type,'wallet');
            $deposit->transactional()->save($transaction);
            if (basicControl()->deposit_commission && $user->referral_id){
                DistributeBonus::dispatch($user, $deposit->payable_amount_in_base_currency, 'deposit', $deposit);
            }

            $params = [
                'amount' => currencyPosition($deposit->payable_amount_in_base_currency),
                'transaction' => $deposit->trx_id,
            ];

            $action = [
                "link" => route('user.fund.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.fund.index');
            $this->sendMailSms($deposit->user, 'ADD_FUND_USER_USER', $params);
            $this->userPushNotification($deposit->user, 'ADD_FUND_USER_USER', $params, $action);
            $this->userFirebasePushNotification($deposit->user, 'ADD_FUND_USER_USER', $params, $firebaseAction);

            $params = [
                'username' => optional($deposit->user)->username,
                'amount' => currencyPosition($deposit->payable_amount_in_base_currency),
                'transaction' => $deposit->trx_id,
            ];
            $actionAdmin = [
                "name" => optional($deposit->user)->firstname . ' ' . optional($deposit->user)->lastname,
                "image" => getFile(optional($deposit->user)->image_driver, optional($deposit->user)->image),
                "link" => route('admin.payment.log'),
                "icon" => "fas fa-ticket-alt text-white"
            ];

            $firebaseAction = route('admin.payment.log');
            $this->adminMail('ADD_FUND_USER_ADMIN', $params);
            $this->adminPushNotification('ADD_FUND_USER_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('ADD_FUND_USER_ADMIN', $params, $firebaseAction);
        }


        return true;
    }

    public function plan($deposit)
    {

        $deposit->status = 1;
        $deposit->save();

        $plan = InvestmentPlan::findOrFail($deposit->depositable_id);

        if ($deposit->gateway){
            $remarks = 'Investment Via ' .  optional($deposit->gateway)->name;
        }else{
            $remarks = 'Investment from Wallet';
        }

        $amount = $deposit->payable_amount_in_base_currency;

        $user = $deposit->user;
        $user->total_invest += $amount;
        $user->plan_invest += $amount;
        $user->save();

        $profit = $plan->Profit($amount);
        $trx_type = '-';
        $trx_id = $deposit->trx_id;

        $charge = getAmount($deposit->base_currency_charge);
        $transactional_type = $deposit->depositable_type;

        $transaction = $this->makeTransaction($user,$amount,$charge,$trx_type,$trx_id,$remarks,$transactional_type,null);

        $plan->transactional()->save($transaction);

        $this->makeInvest($user,$plan,$profit,$trx_id,$amount);

        if (basicControl()->investment_commission && $user->referral_id){
            DistributeBonus::dispatch($user, $amount, 'invest', $plan);
        }
        return true;
    }

    public function project($deposit)
    {
        $invest = ProjectInvestment::with('project.details')->findOrFail($deposit->depositable_id);

        if ($invest && $deposit->user){

            $project = $invest->project;
            $project->available_units -= $invest->unit;
            $project->save();

            $deposit->depositable_id = $project->id;
            $deposit->status = 1;
            $deposit->save();

            $invest->payment_status = 1;
            $invest->trx =  $deposit->trx_id;
            $invest->save();

            if ($deposit->gateway){
                $remarks = 'Investment Via ' .  optional($deposit->gateway)->name;
            }else{
                $remarks = 'Investment from Wallet';
            }

            $amount = $deposit->payable_amount_in_base_currency;
            $user = $deposit->user;
            $user->total_invest += $amount;
            $user->project_invest += $amount;
            $user->save();

            $trx_type = '-';
            $trx_id = $deposit->trx_id;
            $charge = getAmount($deposit->base_currency_charge);
            $transactional_type = $deposit->depositable_type;
            $transaction = $this->makeTransaction($user,$amount,$charge,$trx_type,$trx_id,$remarks,$transactional_type , null);
            $project->transactional()->save($transaction);

            if (basicControl()->investment_commission && $user->referral_id){
                DistributeBonus::dispatch($user, $amount, 'invest', $project);
            }

            $totalUnit =  $invest->unit;
            $perUnitPrice = $invest->per_unit_price;
            $this->ProjectInvestNotify($user,$project,$totalUnit,$perUnitPrice);

            return  true;
        }
    }

    public function order($deposit)
    {
        $order = Order::findOrFail($deposit->depositable_id);
        if ($order){
            $transaction = new Transaction();
            $transaction->user_id = $deposit->user?$deposit->user->id:-15;
            $transaction->amount = $deposit->payable_amount_in_base_currency;
            $transaction->charge = getAmount($deposit->base_currency_charge);
            $transaction->trx_type = '-';
            $transaction->trx_id = $deposit->trx_id;
            $transaction->balance = optional($order->user)->balance??null;
            $transaction->remarks = 'Order Payment Via '. optional($deposit->gateway)->name;
            $order->transactional()->save($transaction);
            $order->payment_status = 1;
            $order->gateway_id = optional($deposit->gateway)->id;
            $order->transaction_id = $transaction->trx_id;
            $order->save();
            $deposit->status = 1;
            $deposit->save();

            $params = [
                'order_number' => $order->order_number,
                'trx_id' => $deposit->trx_id,
                'username' => $deposit->user->username??'Guest User',
                'payment_amount' => currencyPosition($deposit->payable_amount_in_base_currency),
                'payment_method' => optional($deposit->gateway)->name,
                'datetime' => dateTime($deposit->created_at),
            ];

            $action = [
                "link" => "#",
                "icon" => "fa-regular fa-bell"
            ];

            $firebaseAction = "#";

            if ($deposit->user){
                $this->sendMailSms($deposit->user, 'PAYMENT_CONFIRMATION', $params);
                $this->userPushNotification($deposit->user, 'PAYMENT_CONFIRMATION', $params, $action);
                $this->userFirebasePushNotification($deposit->user, 'PAYMENT_CONFIRMATION', $params, $firebaseAction);
            }

            if ( $deposit->user){
                $actionAdmin = [
                    "name" => optional($deposit->user)->firstname . ' ' . optional($deposit->user)->lastname,
                    "image" => getFile(optional($deposit->user)->image_driver, optional($deposit->user)->image),
                    "link" => route('admin.transaction'),
                    "icon" => "fa-regular fa-bell"
                ];
            }else{
                $actionAdmin = [
                    "name" =>'Guest User',
                    "image" => getFile('local', 'image.avif'),
                    "link" => route('admin.transaction'),
                    "icon" => "fa-regular fa-bell"
                ];
            }

            $firebaseAction = route('admin.transaction');
            $this->adminMail('PAYMENT_CONFIRMATION_ADMIN', $params);
            $this->adminPushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $firebaseAction);
            return $order;
        }
    }

    public function makeProjectInvest($user,$project,$amount,$unit)
    {
        $invest = new ProjectInvestment();
        $invest->user_id = $user->id;
        $invest->project_id = $project->id;
        $invest->per_unit_price = $amount;
        $invest->unit = $unit;
        $invest->return = $project->getProfit($unit,$amount);
        if ($project->number_of_return_has_unlimited){
            $invest->is_life_time = true;
        }else{
            $invest->number_of_return = $project->number_of_return;
        }
       $invest->return_period = $project->return_period;
        $invest->return_period_type =  $project->return_period_type;
        $invest->next_return = $project->maturity();
        $invest->capital_back = $project->capital_back;
        $invest->project_expiry_date = $project->projectExpiry()??null;
        $invest->status = 1;
        if(!$project->projectExpiry()){
            $invest->project_period_is_lifetime = true;
        }
        $invest->payment_status = 0;
        $invest->save();

        return $invest;
    }

    public  function makeTransaction($user, $amount, $charge, $trx_type = null, $trx_id = null, $remarks = null,$transactional_type = null, $walletType = null)
    {
        $walletType = strtolower($walletType);
        $transaction = new Transaction();
        $transaction->user_id =$user->id;
        $transaction->amount = $amount;
        $transaction->charge =$charge;
        $transaction->trx_type =$trx_type;
        if($trx_id != null){
            $transaction->trx_id =$trx_id;
        }
        $transaction->remarks = $remarks;
        $transaction->transactional_type = $transactional_type;
        $transaction->wallet_type = $walletType;
        if ($walletType && $walletType === 'profit'){
            $transaction->balance =  $user->profit_balance;
        }else{
            $transaction->balance =  $user->balance;
        }
        $transaction->save();

        return $transaction;
    }


    public function ProjectInvestNotify($user , $project ,$totalUnit ,$perUnitPrice)
    {
        $params = [
            'username' => $user->username,
            'project_name' =>optional($project->details)->title,
            'total_unit' => $totalUnit,
            'unit_price' =>currencyPosition(getAmount($perUnitPrice)) ,
            'amount' => currencyPosition(getAmount($totalUnit*$perUnitPrice))
        ];

        $action = [
            "name" => $user->firstname . ' ' . $user->lastname,
            "image" => getFile($user->image_driver, $user->image),
            "link" => route('admin.project.investment'),
            "icon" => "fas fa-ticket-alt text-white"
        ];
        $this->adminPushNotification('PROJECT_INVEST',$params,$action);
        $this->adminFirebasePushNotification('PROJECT_INVEST',$params,$action);
        $this->adminMail('PROJECT_INVEST',$params);

        $action2 = [
            "link" => route('user.project.investment'),
            "icon" => "fa-regular fa-circle-check text-success"
        ];
        $firebaseAction = route('user.project.investment');
        $this->sendMailSms($user, 'PROJECT_INVEST_USER', $params);
        $this->userPushNotification($user, 'PROJECT_INVEST_USER', $params, $action2);
        $this->userFirebasePushNotification($user, 'PROJECT_INVEST_USER', $params, $firebaseAction);
        return true;
    }

    public  function makeInvest($user,$plan,$profit,$trx = null,$amount)
    {

        try {
            $invest = new InvestHistory();
            $invest->user_id =  $user->id;
            $invest->plan_id = $plan->id;
            $invest->invest_amount = $amount;
            $invest->profit = $profit;
            if ($plan->number_of_profit_return){
                $invest->number_of_return = $plan->number_of_profit_return;
            }else{
                $invest->is_life_time = true;
            }
            $invest->status = 1;
            $invest->return_period = $plan->return_period;
            $invest->return_period_type = $plan->return_period_type;
            $invest->next_return = $plan->maturity();
            $invest->capital_back = $plan->capital_back;
            $invest->plan_expiry_date = $plan->plan_expiry_date;
            $invest->plan_expiry_date = $plan->planExpiry()??null;
            if (!$plan->planExpiry()){
                $invest->plan_period_is_lifetime = true;
            }
            $invest->trx = $trx;
            $invest->save();

            $params = [
                'username' => $user->username,
                'plan_name' => $plan->plan_name,
                'amount' => currencyPosition(getAmount($amount)),
            ];

            $action = [
                "name" => $user->firstname . ' ' . $user->lastname,
                "image" => getFile($user->image_driver, $user->image),
                "link" => route('admin.invest.history'),
                "icon" => "fas fa-ticket-alt text-white"
            ];
            $this->adminPushNotification('PLAN_INVEST',$params,$action);
            $this->adminFirebasePushNotification('PLAN_INVEST',$params,$action);
            $this->adminMail('PLAN_INVEST',$params);
            $action2 = [
                "link" => route('user.plan.investment'),
                "icon" => "fa-regular fa-circle-check text-success"
            ];
            $firebaseAction = route('user.plan.investment');
            $this->sendMailSms($user, 'PLAN_INVEST_USER', $params);
            $this->userPushNotification($user, 'PLAN_INVEST_USER', $params, $action2);
            $this->userFirebasePushNotification($user, 'PLAN_INVEST_USER', $params, $firebaseAction);


            return $invest;
        }catch (\Exception $exception){
           return  false;
        }

    }
    public function cryptoQR($wallet, $amount, $crypto = null)
    {
        $varb = $wallet . "?amount=" . $amount;
        return "https://quickchart.io/chart?cht=qr&chl=$varb";
    }
}
