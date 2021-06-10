<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*Route::get('/billing-portal', function (Request $request) {
    return auth()->user()->redirectToBillingPortal(route('billing'));
});*/



Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified', 'nonPayingCustomer'])->get('/subscribe', function () {
    logger(auth()->user()->createSetupIntent());
    return view('subscribe', [
        'intent' => auth()->user()->createSetupIntent(),
    ]);
})->name('subscribe');

Route::middleware(['auth:sanctum', 'verified', 'nonPayingCustomer'])->post('/subscribe', function (Request $request) {
    //dd($request->all());
    auth()->user()->newSubscription('cashier', $request->plan)->trialDays(14)->create($request->paymentMethod);

    return redirect('/dashboard');
})->name('subscribe.post');

Route::middleware(['auth:sanctum', 'verified', 'payingCustomer'])->get('/members', function () {

    //end_at 为null,, 可能是订阅到期了
    $cancelledSubscription = auth()->user()->subscriptions()->where('stripe_plan','price_1Izg0lEec3qTGyD3PLrBSzSH')->get()->toArray();
    $details =array();
    if(!empty($cancelledSubscription)){
        foreach ($cancelledSubscription as $item){
            $details = array(
                'name'=>$item['name'],
                'stripe_plan'=>$item['stripe_plan'],
                'quantity'=>$item['quantity'],
                'trial_ends_at'=>empty($item['trial_ends_at'])?'没有试用期':$item['trial_ends_at'],
                'ends_at'=>$item['ends_at']
            );
            break;
            //$subscriptionsDetails[]=$details;
        }
    }else{
        dd('该客户尚未订阅');
        return redirect('/subscribe');
    }

    return view('members',[
        'details' => $details
    ]);
})->name('members');


Route::middleware(['auth:sanctum', 'verified', 'payingCustomer'])->post('/members', function () {
    auth()->user()->subscription('cashier')->cancel();
    return redirect('/subscribe');
})->name('members.post');



Route::middleware(['auth:sanctum', 'verified', 'payingCustomer'])->get('/members_resume', function () {

    $cancelledSubscription = auth()->user()->subscriptions()->where('stripe_plan','price_1Izg0lEec3qTGyD3PLrBSzSH')->get()->toArray();
    $details =array();
    if(!empty($cancelledSubscription)){
        foreach ($cancelledSubscription as $item){
            $details = array(
                'name'=>$item['name'],
                'stripe_plan'=>$item['stripe_plan'],
                'quantity'=>$item['quantity'],
                'trial_ends_at'=>$item['trial_ends_at'],
                'ends_at'=>$item['ends_at']
            );
            break;
            //$subscriptionsDetails[]=$details;
        }
    }

    return view('members_resume',[
        'details' => $details
    ]);
})->name('members_resume');
Route::middleware(['auth:sanctum', 'verified', 'payingCustomer'])->post('/members_resume', function () {
    auth()->user()->subscription('cashier')->resume();
    return view('members_resume');
})->name('members_resume.post');



Route::middleware(['auth:sanctum', 'verified'])->get('/charge', function () {
    return view('charge');
})->name('charge');

Route::middleware(['auth:sanctum', 'verified'])->post('/charge', function (Request $request) {
    // dd($request->all());
    // auth()->user()->charge(1000, $request->paymentMethod);
    auth()->user()->createAsStripeCustomer();
    auth()->user()->updateDefaultPaymentMethod($request->paymentMethod);
    auth()->user()->invoiceFor('One Time Fee', 1500);

    return redirect('/dashboard');
})->name('charge.post');

Route::middleware(['auth:sanctum', 'verified'])->get('/invoices', function () {
    return view('invoices', [
        'invoices' => auth()->user()->invoices(),
    ]);
})->name('invoices');

Route::get('user/invoice/{invoice}', function (Request $request, $invoiceId) {
    return $request->user()->downloadInvoice($invoiceId, [
        'vendor' => 'Your Company',
        'product' => 'Your Product',
    ]);
});
