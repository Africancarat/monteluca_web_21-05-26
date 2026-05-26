<?php

namespace App\Http\Controllers\Back;

use App\{
    Models\Order,
    Models\PromoCode,
    Models\TrackOrder,
    Http\Controllers\Controller
};
use App\Helpers\SmsHelper;
use App\Models\Notification;
use App\Services\OrderInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     * Constructor Method.
     *
     * Setting Authentication
     *
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
      
        if($request->type){
            if($request->start_date && $request->end_date){
                $datas = $start_date = Carbon::parse($request->start_date);
                $end_date = Carbon::parse($request->end_date);
                $datas = Order::latest('id')->whereOrderStatus($request->type)->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
            }else{
                $datas = Order::latest('id')->whereOrderStatus($request->type)->get();
            }
            
        }else{
            if($request->start_date && $request->end_date){
                $datas = $start_date = Carbon::parse($request->start_date);
                $end_date = Carbon::parse($request->end_date);
                $datas = Order::latest('id')->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
            }else{
                $datas = Order::latest('id')->get();
            }
        }
        return view('back.order.index',compact('datas'));
    }

    
    public function edit($id)
    {
        
        $order = Order::findOrFail($id);
        return view('back.order.edit', compact('order'));
    }

    

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Check if order_id is available
        if (Order::where('transaction_number', $request->transaction_number)->where('id', '!=', $id)->exists()) {
            return redirect()->route('back.order.index')->withErrors(__('Order ID already exists.'));
        }

        $order->update($request->all());
        return redirect()->route('back.order.index')->withSuccess(__('Order Updated Successfully.'));
    }

    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function invoice($id)
    {
        $order = Order::findOrfail($id);
        $cart = json_decode($order->cart, true);
        return view('back.order.invoice',compact('order','cart'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printOrder($id)
    {
        $order = Order::findOrfail($id);
        $cart = json_decode($order->cart, true);
        return view('back.order.print',compact('order','cart'));
    }


    /**
     * Change the status for editing the specified resource.
     *
     * @param  int  $id
     * @param  string  $field
     * @param  string  $value
     * @return \Illuminate\Http\Response
     */
    public function status($id,$field,$value)
    {

        $order = Order::find($id);
        if($field == 'payment_status'){
            if($order['payment_status'] == 'Paid'){
                return redirect()->route('back.order.index')->withErrors(__('Order is already paid.'));
            }
        }
        if($field == 'order_status'){
            if($order['order_status'] == 'Delivered'){
                return redirect()->route('back.order.index')->withErrors(__('Order is already Delivered.'));
            }
        }

        $previousStatus = $order->order_status;
        $previousPayment = $order->payment_status;

        $order->update([$field => $value]);

        $inventory = app(OrderInventoryService::class);

        if ($field === 'order_status'
            && $value === 'Canceled'
            && $previousStatus !== 'Canceled') {
            // COD / bank decrement stock on placement while still Unpaid — must restock on cancel too.
            $inventory->restockOrderIfDecremented($order, $previousPayment);
        }

        if ($field === 'payment_status'
            && $value !== 'Paid'
            && $previousPayment === 'Paid'
            && $order->order_status !== 'Canceled') {
            $inventory->restockOrderIfDecremented($order->fresh(), $previousPayment);
        }
        if($order->payment_status == 'Paid'){
            $this->setPromoCode($order);
        }
        $this->setTrackOrder($order);
        
        $sms = new SmsHelper();
        $user_number = $order->user->phone;
        if($user_number){
            $sms->SendSms($user_number,"'order_status'",$order->transaction_number);
        }
       
        return redirect()->route('back.order.index')->withSuccess(__('Status Updated Successfully.'));
    }

    /**
     * Custom Function
     */
//    public function setTrackOrder($order)
//    {
//
//        if($order->order_status == 'In Progress'){
//            if(!TrackOrder::whereOrderId($order->id)->whereTitle('In Progress')->exists()){
//                TrackOrder::create([
//                    'title' => 'In Progress',
//                    'order_id' => $order->id
//                ]);
//            }
//        }
//        if($order->order_status == 'Canceled'){
//            if(!TrackOrder::whereOrderId($order->id)->whereTitle('Canceled')->exists()){
//
//                if(!TrackOrder::whereOrderId($order->id)->whereTitle('In Progress')->exists()){
//                    TrackOrder::create([
//                        'title' => 'In Progress',
//                        'order_id' => $order->id
//                    ]);
//                }
//                if(!TrackOrder::whereOrderId($order->id)->whereTitle('Delivered')->exists()){
//                    TrackOrder::create([
//                        'title' => 'Delivered',
//                        'order_id' => $order->id
//                    ]);
//                }
//
//                if(!TrackOrder::whereOrderId($order->id)->whereTitle('Canceled')->exists()){
//                    TrackOrder::create([
//                        'title' => 'Canceled',
//                        'order_id' => $order->id
//                    ]);
//                }
//
//                if($order->order_status == 'Manufacturing'){
//                    TrackOrder::firstOrCreate([
//                        'title'=>'Manufacturing',
//                        'order_id'=>$order->id
//                    ]);
//                }
//
//                if($order->order_status == 'Ready to Ship'){
//                    TrackOrder::firstOrCreate([
//                        'title'=>'Ready to Ship',
//                        'order_id'=>$order->id
//                    ]);
//                }
//
//                if($order->order_status == 'Shipped'){
//                    TrackOrder::firstOrCreate([
//                        'title'=>'Shipped',
//                        'order_id'=>$order->id
//                    ]);
//                }
//
//                if($order->order_status == 'Refunded'){
//                    TrackOrder::firstOrCreate([
//                        'title'=>'Refunded',
//                        'order_id'=>$order->id
//                    ]);
//                }
//
//            }
//        }
//
//        if($order->order_status == 'Delivered'){
//
//            if(!TrackOrder::whereOrderId($order->id)->whereTitle('In Progress')->exists()){
//                TrackOrder::create([
//                    'title' => 'In Progress',
//                    'order_id' => $order->id
//                ]);
//            }
//
//            if(!TrackOrder::whereOrderId($order->id)->whereTitle('Delivered')->exists()){
//                TrackOrder::create([
//                    'title' => 'Delivered',
//                    'order_id' => $order->id
//                ]);
//            }
//        }
//    }
    public function setTrackOrder($order)
    {

        $statuses = [
            'In Progress',
            'Manufacturing',
            'Ready to Ship',
            'Shipped',
            'Delivered',
            'Refunded',
            'Canceled'
        ];


        foreach ($statuses as $status) {

            if ($order->order_status == $status) {

                TrackOrder::firstOrCreate([
                    'order_id' => $order->id,
                    'title'    => $status
                ]);

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure previous stages exist
        |--------------------------------------------------------------------------
        */

        if ($order->order_status == 'Delivered') {

            TrackOrder::firstOrCreate([
                'title' => 'In Progress',
                'order_id' => $order->id
            ]);

            TrackOrder::firstOrCreate([
                'title' => 'Manufacturing',
                'order_id' => $order->id
            ]);

            TrackOrder::firstOrCreate([
                'title' => 'Ready to Ship',
                'order_id' => $order->id
            ]);

            TrackOrder::firstOrCreate([
                'title' => 'Shipped',
                'order_id' => $order->id
            ]);

            TrackOrder::firstOrCreate([
                'title' => 'Delivered',
                'order_id' => $order->id
            ]);
        }


        if ($order->order_status == 'Canceled') {

            TrackOrder::firstOrCreate([
                'title' => 'Canceled',
                'order_id' => $order->id
            ]);

        }


        if ($order->order_status == 'Refunded') {

            TrackOrder::firstOrCreate([
                'title' => 'Refunded',
                'order_id' => $order->id
            ]);

        }

    }

    public function setPromoCode($order)
    {

        $discount = json_decode($order->discount, true);
        if ($discount != null && ! empty($discount['code']['id'])) {
            $code = PromoCode::find($discount['code']['id']);
            if ($code) {
                $code->no_of_times--;
                $code->update();
            }
        }
    }


    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $order->tranaction->delete();
        if(Notification::where('order_id',$id)->exists()){
            Notification::where('order_id',$id)->delete();
        }
        if(count($order->tracks_data)>0){
            foreach($order->tracks_data as $track){
                $track->delete();
            }
        }
        $order->delete();
        return redirect()->back()->withSuccess(__('Order Deleted Successfully.'));
    }

}
