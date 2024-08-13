<?php

namespace Modules\Cargo\Http\Helpers;

use Modules\Cargo\Entities\ClientShipmentLog;
use Modules\Cargo\Entities\Mission;
use Modules\Cargo\Entities\Shipment;
use Modules\Cargo\Entities\ShipmentLog;
use Modules\Cargo\Entities\ShipmentMission;
use Modules\Cargo\Entities\Transaction;
use Modules\Cargo\Entities\Client;
use Modules\Acl\Repositories\AclRepository;
use Modules\Cargo\Entities\PackageShipment;
use Modules\Cargo\Http\Controllers\ShipmentController;
use DB;
use App\Models\User;

class StatusManagerHelper{

    public function change_shipment_status($shipments,$to,$mission_id = null)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = '';
		try {
			DB::beginTransaction();

            $transaction = new TransactionHelper();
            $shipmentController = new ShipmentController(new AclRepository);
            foreach($shipments as $shipment_id)
            {

                $shipment = Shipment::find($shipment_id);
                $client   = Client::where('id',$shipment->client_id)->pluck('user_id')->first();
                $user     = User::where('id',$client)->pluck('id')->first();
                // if($shipment->status_id == $to)
                // {
                //     throw new \Exception("Out of status changer scope");
                // }
                if($shipment != null)
                {
                    $oldStatus = $shipment->status_id;
                    $oldClientStatus = $shipment->client_status;

                    //Conditions of change status
                    if($to == Shipment::REQUESTED_STATUS)
                    {
                        $shipment->client_status = Shipment::CLIENT_STATUS_READY;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_READY;
                        $log->shipment_id = $shipment->id;
                        $log->created_by = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();

                        if($shipment->getRawOriginal('type') == Shipment::PICKUP)
                        {
                            if($shipment->payment_type == Shipment::PREPAID)
                            {
                                $shipment_cost = $transaction->calculate_shipment_cost($shipment->id);
                                // $transaction->create_shipment_transaction($shipment->id,$shipment_cost,Transaction::CLIENT,$shipment->client_id,Transaction::DEBIT);
                            }
                        }

                    }elseif($to == Shipment::APPROVED_STATUS)
                    {
                        $shipment_mission = ShipmentMission::where('mission_id',$mission_id)->where('shipment_id',$shipment->id)->first();
                        if($shipment_mission){
                            $shipment_mission->delete();
                            $shipment->mission_id = null;
                            $shipment->captain_id = null;
                        }

                        $shipment->client_status = Shipment::CLIENT_STATUS_IN_PROCESSING;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_IN_PROCESSING;
                        $log->shipment_id = $shipment->id;
                        $log->created_by  = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();

                    }elseif($to == Shipment::SAVED_STATUS)
                    {
                        $shipment_mission = ShipmentMission::where('mission_id',$mission_id)->where('shipment_id',$shipment->id)->first();
                        if($shipment_mission){
                            $shipment_mission->delete();
                            $shipment->mission_id = null;
                            $shipment->captain_id = null;
                        }

                        if($shipment->track == null){
                            $PackageShipment = PackageShipment::where('shipment_id',$shipment->id)->get();
                            $result = $shipmentController->createExternColi($shipment,$PackageShipment);
                            if($result['error']){
                                $to = Shipment::DRAFT_STATUS;
                                DB::rollback();
                                $response['success'] = 0;
		                        $response['error_msg'] = 'shipment not change';
		                        return $response;
                            }
                        }

                        $shipment->captain_id = null;
                        $shipment->mission_id = null;

                    }elseif($to == Shipment::CAPTAIN_ASSIGNED_STATUS)
                    {
                        $shipment->client_status = Shipment::CLIENT_STATUS_OUT_FOR_DELIVERY;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_OUT_FOR_DELIVERY;
                        $log->shipment_id = $shipment->id;
                        $log->created_by = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();
                        if($mission_id != null)
                        {
                                    $mission = Mission::find($mission_id);
                                    $shipment->captain_id = $mission->captain_id;
                        }

                    }elseif($to == Shipment::RETURNED_STOCK)
                    {
                        $shipment->mission_id = null;
                        $shipment->captain_id = null;

                        $shipment->client_status = Shipment::CLIENT_STATUS_RETURNED_STOCK;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_RETURNED_STOCK;
                        $log->shipment_id = $shipment->id;
                        $log->created_by  = auth()->user() ? auth()->user()->id : $user;
                        $log->save();

                    }elseif($to == Shipment::RETURNED_STATUS)
                    {
                        $shipment->mission_id = null;
                        $shipment->captain_id = null;
                        $shipment->final_status = 2;
                        $shipment->situation = 'Returned';
                        $shipment->client_status = Shipment::CLIENT_STATUS_RETURNED;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_RETURNED;
                        $log->shipment_id = $shipment->id;
                        $log->created_by  = auth()->user() ? auth()->user()->id : $user;
                        $log->save();

                        $shipments_mission = Shipment::where('mission_id', $mission_id)->count();
                        if($shipments_mission == 1 || $shipments_mission == 0)
                        {
                            $mission = Mission::find($mission_id);
                            $mission->status_id = Mission::DONE_STATUS;
                            if (!$mission->save()) {
                                throw new \Exception("can't change mission status");
                            }
                        }

                    }elseif($to == Shipment::RETURNED_CLIENT_GIVEN){

                        $shipment->client_status = Shipment::CLIENT_STATUS_RETURNED_CLIENT_GIVEN;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_RETURNED_CLIENT_GIVEN;
                        $log->shipment_id = $shipment->id;
                        $log->created_by  = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();

                    }elseif($to == Shipment::DELIVERED_STATUS)
                    {
                        $shipment->client_status = Shipment::CLIENT_STATUS_DELIVERED;
                        $shipment->final_status = 1;
                        $shipment->situation = 'Delivered';
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_DELIVERED;
                        $log->shipment_id = $shipment->id;
                        $log->created_by = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();
                    }elseif($to == Shipment::SUPPLIED_STATUS)
                    {
                        $shipment->client_status = Shipment::CLIENT_STATUS_SUPPLIED;
                        $log = new ClientShipmentLog();
                        $log->from = $oldClientStatus;
                        $log->to = Shipment::CLIENT_STATUS_SUPPLIED;
                        $log->shipment_id = $shipment->id;
                        $log->created_by = auth()->user() ? auth()->user()->id : $user ;
                        $log->save();
                    }

                    $shipment->status_id = $to;
                    if(!$shipment->save())
                    {
                        throw new \Exception("can't change shipment status");
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->status_id;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user() ? auth()->user()->id : $user ;
                    $log->save();
                }else
                {
                    throw new \Exception("There is no shipment with this ID");
                }

            }
            DB::commit();
        }catch (\Exception $e) {
			//echo $e->getMessage();exit;
			DB::rollback();
			$response['success'] = 0;
			$response['error_msg'] = $e->getMessage();
		}
        return $response;
    }

    public function change_shipment_to_approved($shipments)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::APPROVED_STATUS) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    $shipment->client_status = Shipment::CLIENT_STATUS_READY;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_STATUS_READY;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    $shipment->status_id = Shipment::APPROVED_STATUS;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {

                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }
            DB::commit();
            return $response;
        }catch (\Exception $e) {
			//echo $e->getMessage();exit;
			DB::rollback();
			$response['success'] = 0;
			$response['error_msg'] = $e->getMessage();
		}
        return $response;
    }

    public function change_shipment_to_requested($shipments)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::REQUESTED_STATUS) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    $shipment->client_status = Shipment::CLIENT_STATUS_READY;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_STATUS_READY;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    $shipment->status_id = Shipment::REQUESTED_STATUS;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {

                    DB::rollback();
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }
            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }


    public function change_shipment_to_asigned($shipments, $mission_id)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::CAPTAIN_ASSIGNED_STATUS) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    $shipment->client_status = Shipment::CLIENT_STATUS_TRANSFERED;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_STATUS_TRANSFERED;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                        $shipment->captain_id = $mission->captain_id;
                    }
                    $shipment->status_id = Shipment::CAPTAIN_ASSIGNED_STATUS;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {

                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }
            DB::commit();
            return $response;
        } catch (\Exception $e) {

            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }


    public function change_shipment_to_asigned_returned($shipments, $mission_id)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::CAPTAIN_ASSIGNED_RETURNED_STATUS) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    $shipment->client_status = Shipment::CLIENT_STATUS_TRNSFER_RETURNE;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_STATUS_TRNSFER_RETURNE;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    $shipment->payment_status = Shipment::PAYMENT_RETOUR_TRANSFER;
                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                        $shipment->captain_id = $mission->captain_id;
                    }
                    $shipment->status_id = Shipment::CAPTAIN_ASSIGNED_RETURNED_STATUS;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {

                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }
            DB::commit();
            return $response;
        } catch (\Exception $e) {

            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }


    public function change_shipment_to_returned_stock($shipments, $mission_id)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::RETURNED_STOCK) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                    } else {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }
                    $shipment->client_status = Shipment::CLIENT_RETURNED_STOCK_STATUS;
                    $shipment->payment_status = Shipment::PAYMENT_RETOUR_CENTER;
                    $shipment->branch_id = $mission->to_branch_id;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_RETURNED_STOCK_STATUS;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    $shipment->mission_id = null;
                    $shipment->captain_id = null;
                    $shipment->status_id = Shipment::RETURNED_STOCK;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {

                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }
            DB::commit();
            return $response;
        } catch (\Exception $e) {

            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }



    public function change_shipment_to_recived($shipments, $mission_id)
    {
        $response = array();
		$response['success'] = 1;
		$response['error_msg'] = 'shipment not change';
        $user = auth()->user();
        try {
            DB::beginTransaction();
            //dd( DB::transactionLevel());
            foreach ($shipments as $shipment_id) {
                $shipment = Shipment::find($shipment_id);
                if ($shipment->getRawOriginal('status_id') == Shipment::RECIVED_STATUS) {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                if ($shipment != null) {
                    $oldStatus = $shipment->getRawOriginal('status_id');
                    $oldClientStatus = $shipment->getRawOriginal('client_status');
                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                    } else {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }
                    $shipment->client_status = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                    $shipment->branch_id = $mission->to_branch_id;
                    $shipment->captain_id = null;
                    $shipment->mission_id = null;
                    $log = new ClientShipmentLog();
                    $log->from = $oldClientStatus;
                    $log->to = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                    $log->shipment_id = $shipment->id;
                    $log->created_by = auth()->user()->id;
                    $log->save();
                    $shipment->status_id = Shipment::RECIVED_STATUS;

                    if (!$shipment->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                    }

                    $log = new ShipmentLog();
                    $log->from = $oldStatus;
                    $log->to = $shipment->getRawOriginal('status_id');
                    $log->shipment_id = $shipment->id;
                    $log->created_by = $user->id;
                    $log->save();
                } else {
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
            }

            DB::commit();
            return $response;
        } catch (\Exception $e) {

            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }


    public function changeContainerToCaptainAssignedStatus($containers, $mission_id = null, $captain_id = null, $from_api = false)
    {
        try {


            DB::beginTransaction();


            if ($from_api) {
                $apihelper = new ApiHelper();
                $user = $apihelper->checkUser(request());
            } else {
                $response = array();
                $response['success'] = 1;
                $response['error_msg'] = 'shipment not change';
                $user = auth()->user();
            }

            foreach ($containers as  $container_id) {
                $container = Container::find($container_id);
                if ($container->getRawOriginal('status_id') == Container::CAPTAIN_ASSIGNED_STATUS) {
                    //throw new \Exception("Out of status changer scope");
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                $oldStatus = $container->getRawOriginal('status_id');
                if ($container->type == "Returning") {
                    $container->status_id = Container::CAPTAIN_ASSIGNED_STATUS;
                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                        $container->captain_id = $mission->captain_id;
                    }
                    $log = new ContainerLog();
                    $log->from = $oldStatus;
                    $log->to = Container::CAPTAIN_ASSIGNED_STATUS;
                    $log->container_id = $container->id;
                    $log->created_by = $user->id;
                    $log->save();
                    $shipments = Shipment::whereIn('id', ContainerShipment::where('container_id', $container->id)->pluck('shipment_id'))->pluck('id');
                    foreach ($shipments as $shipment_id) {
                        $shipment = Shipment::find($shipment_id);
                        ShipmentLog::create([
                            'from' => $shipment->getRawOriginal('status_id'),
                            'to' => Shipment::CAPTAIN_ASSIGNED_RETURNED_STATUS,
                            'shipment_id' => $shipment->id
                        ]);
                        ClientShipmentLog::create([
                            'from' => $shipment->getRawOriginal,
                            'to' => Shipment::CLIENT_STATUS_TRNSFER_RETURNE,
                            'shipment_id' => $shipment->id
                        ]);
                        $shipment->status_id = Shipment::CAPTAIN_ASSIGNED_RETURNED_STATUS;
                        $shipment->client_status = Shipment::CLIENT_STATUS_TRNSFER_RETURNE;
                        $shipment->payment_status = Shipment::PAYMENT_RETOUR_TRANSFER;
                        if ($mission) {
                            $shipment->captain_id = $mission->captain_id;
                        }
                        $save = $shipment->save();
                        if (!$save) {
                            DB::rollback();
                            $response['success'] = 0;
                            return $response;
                            //throw new \Exception("Out of status changer scope");
                        }
                    }
                    $save = $container->save();
                    if (!$save) {
                        DB::rollback();
                        $response['success'] = 0;
                        return $response;
                        // throw new \Exception("Out of status changer scope");
                    }

                    //dd($shipments);
                }
                if ($container->type == "Shipping") {
                    $container->status_id = Container::CAPTAIN_ASSIGNED_STATUS;

                    if ($mission_id != null) {
                        $mission = Mission::find($mission_id);
                        $container->captain_id = $mission->captain_id;
                    }
                    $log = new ContainerLog();
                    $log->from = $oldStatus;
                    $log->to = Container::CAPTAIN_ASSIGNED_STATUS;
                    $log->container_id = $container->id;
                    $log->created_by = $user->id;
                    $log->save();
                    $shipments = Shipment::whereIn('id', ContainerShipment::where('container_id', $container->id)->pluck('shipment_id'))->pluck('id');

                    foreach ($shipments as $shipment_id) {
                        $shipment = Shipment::find($shipment_id);

                        if ($shipment->getRawOriginal('status_id') == Shipment::CAPTAIN_ASSIGNED_STATUS) {
                            DB::rollback();
                            $response['success'] = 0;
                            $response['error_msg'] = "there is a shipment inside the box is already assigned to driver";
                            return $response;
                        }
                        ShipmentLog::create([
                            'from' => $shipment->getRawOriginal('status_id'),
                            'to' => Shipment::CAPTAIN_ASSIGNED_STATUS,
                            'shipment_id' => $shipment->id
                        ]);
                        ClientShipmentLog::create([
                            'from' => $shipment->getRawOriginal,
                            'to' => Shipment::CLIENT_STATUS_TRANSFERED,
                            'shipment_id' => $shipment->id
                        ]);
                        $shipment->status_id = Shipment::CAPTAIN_ASSIGNED_STATUS;
                        $shipment->client_status = Shipment::CLIENT_STATUS_TRANSFERED;
                        if ($mission) {
                            $shipment->captain_id = $mission->captain_id;
                        }
                        if (!$shipment->save()) {
                            DB::rollback();
                            $response['success'] = 0;
                            $response['error_msg'] = 'data not saved';
                            return $response;
                            //throw new \Exception("Out of status changer scope");
                        }
                    }
                    if (!$container->save()) {
                        DB::rollback();
                        $response['success'] = 0;
                        $response['error_msg'] = 'data not saved';
                        return $response;
                        //throw new \Exception("Out of status changer scope");
                    }
                }
            }

            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }
    public function changeContainerToReceivedBranch($containers, $mission_id = null, $captain_id = null, $from_api = false)
    {
        try {
            if ($from_api) {
                $apihelper = new ApiHelper();
                $user = $apihelper->checkUser(request());
            } else {
                $response = array();
                $response['success'] = 1;
                $response['error_msg'] = 'shipment not change';
                $user = auth()->user();
            }
            DB::beginTransaction();
            foreach ($containers as  $container_id) {
                $container = Container::find($container_id);

                if ($container->getRawOriginal('status_id') == Container::RECEIVED_BRANCH) {
                    //throw new \Exception("Out of status changer scope");
                    DB::rollback();
                    $response['success'] = 0;
                    return $response;
                }
                $oldStatus = $container->getRawOriginal('status_id');
                if ($mission_id != null) {
                    $mission = Mission::find($mission_id);
                }
                if ($container->type == "Returning") {

                    $container->status_id = Container::RECEIVED_BRANCH;
                    $container->branch_id = $mission->to_branch_id;
                    $log = new ContainerLog();
                    $log->from = $oldStatus;
                    $log->to = Container::RECEIVED_BRANCH;
                    $log->container_id = $container->id;
                    $log->created_by = $user->id;
                    $shipments = Shipment::whereIn('id', ContainerShipment::where('container_id', $container->id)->pluck('shipment_id'))->pluck('id');
                    foreach ($shipments as $shipment_id) {
                        $shipment = Shipment::find($shipment_id);
                        ShipmentLog::create([
                            'from' => $shipment->getRawOriginal('status_id'),
                            'to' => Shipment::RETURNED_STOCK,
                            'shipment_id' => $shipment->id,
                            'created_by' => $user->id
                        ]);
                        ClientShipmentLog::create([
                            'from' => $shipment->getRawOriginal('client_status'),
                            'to' => Shipment::CLIENT_RETURNED_STOCK_STATUS,
                            'shipment_id' => $shipment->id,
                            'created_by' => $user->id
                        ]);
                        $shipment->status_id = Shipment::RETURNED_STOCK;
                        $shipment->client_status = Shipment::CLIENT_RETURNED_STOCK_STATUS;
                        $shipment->branch_id = $mission->to_branch_id;
                        $shipment->payment_status = Shipment::PAYMENT_RETOUR_CENTER;
                        $shipment->captain_id = null;
                        $shipment->mission_id = null;
                        $shipment->container_id = null;
                        $shipment->save();
                    }
                }
                if ($container->type == "Shipping") {
                    $container->status_id = Container::RECEIVED_BRANCH;
                    $container->branch_id = $mission->to_branch_id;
                    $container->save();
                    $log = new ContainerLog();
                    $log->from = $oldStatus;
                    $log->to = Container::RECEIVED_BRANCH;
                    $log->container_id = $container->id;
                    $log->created_by = $user->id;
                    $log->save();
                    $shipments = Shipment::whereIn('id', ContainerShipment::where('container_id', $container->id)->pluck('shipment_id'))->pluck('id');
                    foreach ($shipments as $shipment_id) {
                        $shipment = Shipment::find($shipment_id);
                        ShipmentLog::create([
                            'from' => $shipment->getRawOriginal('status_id'),
                            'to' => Shipment::RECIVED_STATUS,
                            'shipment_id' => $shipment->id,
                            'created_by' => $user->id
                        ]);
                        ClientShipmentLog::create([
                            'from' => $shipment->getRawOriginal,
                            'to' => Shipment::CLIENT_STATUS_RECEIVED_BRANCH,
                            'shipment_id' => $shipment->id,
                            'created_by' => $user->id
                        ]);
                        $shipment->status_id = Shipment::RECIVED_STATUS;
                        $shipment->client_status = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                        $shipment->branch_id = $mission->to_branch_id;
                        $shipment->captain_id = null;
                        $shipment->mission_id = null;
                        $shipment->container_id = null;
                        $shipment->save();
                    }
                }

                $container->captain_id = null;
                $container->mission_id = null;
                $container->save();
            }
            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollback();
            $response['success'] = 0;
            return $response;
        }
    }

}
