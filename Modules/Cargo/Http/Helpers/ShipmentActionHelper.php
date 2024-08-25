<?php

namespace Modules\Cargo\Http\Helpers;

use Modules\Cargo\Entities\Mission;
use Modules\Cargo\Entities\Shipment;
use Modules\Acl\Repositories\AclRepository;
use Modules\Cargo\Http\Controllers\ShipmentController;
use Modules\Cargo\Entities\Client;
use Modules\Cargo\Entities\PlanFee;

class ShipmentActionHelper{

    private $actions;
	public function __construct() {
		$this->actions = array();
	}

    public function get($status,$type=null)
    {
        $shipmentController = new ShipmentController(new AclRepository);
        if($status == Shipment::REQUESTED_STATUS)
        {
            $shipmentController->getFinalStatutExternColi();
            if($type == Shipment::DROPOFF)
            {
                return $this->requested();
            }elseif($type == Shipment::PICKUP)
            {
                return $this->requestedPickup();
            }

        }elseif($status == Shipment::SAVED_STATUS)
        {
            $shipmentController->getFinalStatutExternColi();
            if($type == Shipment::DROPOFF)
            {
                return $this->saved();
            }elseif($type == Shipment::PICKUP)
            {
                return $this->savedPickup();
            }

        }elseif($status == Shipment::APPROVED_STATUS)
        {
            $shipmentController->getFinalStatutExternColi();
            return $this->accepted();
        }elseif($status == Shipment::CAPTAIN_ASSIGNED_STATUS)
        {
            $shipmentController->getFinalStatutExternColi();
            return $this->assigned();
        }elseif($status == Shipment::CLOSED_STATUS)
        {
            return $this->closed();
        }elseif($status == Shipment::RETURNED_STATUS)
        {
            return $this->returned();
        }elseif($status == Shipment::RETURNED_STOCK)
        {
            return $this->returned_stock();
        }elseif($status == Shipment::DELIVERED_STATUS)
        {
            return $this->deliverd();
        }elseif($status == Shipment::ALERT_STATUS)
        {
            $shipmentController->getFinalStatutExternColi();
            return $this->alert();
        }elseif($status == Shipment::DRAFT_STATUS)
        {
            return $this->draft();
        }
        else
        {
            return $this->default();
        }
    }
    static public function permission_info()
    {
        return [
            [
                "text"=> __('cargo::view.approve_shipment_action'),
                "permissions"=>1031,
            ],
            [
                "text"=> __('cargo::view.refuse_shipment_action'),
                "permissions"=>1032,
            ],
            [
                "text"=> __('cargo::view.create_pickup_mission'),
                "permissions"=>1033,
            ],
            [
                "text"=> __('cargo::view.return_shipment_action'),
                "permissions"=>1034,
            ],
            [
                "text"=> __('cargo::view.create_return_mission'),
                "permissions"=>1035,
            ],
            [
                "text"=> __('cargo::view.assign_to_driver'),
                "permissions"=>1036,
            ],
            [
                "text"=> __('cargo::view.create_supply_mission'),
                "permissions"=>1040,
            ],
            [
                "text"=> __('cargo::view.create_transfer_mission'),
                "permissions"=>1200,
            ]
        ];
    }
    private function saved()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.approve');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::APPROVED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['permissions'] = 'approve-shipment-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.print_barcodes');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-print';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.print.stickers');
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = "print stickers";
            $this->actions[count($this->actions)-1]['permissions'] = 'print-barcodes';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;



            return $this->actions;
    }

    private function savedPickup()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_pickup_mission');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.pickup.mission',['type'=>Mission::PICKUP_TYPE]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = 'openPickupMissionModel(this,event)';
            $this->actions[count($this->actions)-1]['permissions'] = 'create-pickup-mission';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.print_barcodes');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-print';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.print.stickers');
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = "print stickers";
            $this->actions[count($this->actions)-1]['permissions'] = 'print-barcodes';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;



            return $this->actions;
    }

    private function requested()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.approve');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::APPROVED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['permissions'] = 'approve-shipment-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['index'] = true;

            return $this->actions;
    }

    private function assigned()
    {
        // $this->actions[count($this->actions)] = array();
        // $this->actions[count($this->actions)-1]['title'] = __('cargo::view.return');
        // $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        // $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::RETURNED_STATUS]);
        // $this->actions[count($this->actions)-1]['method'] = 'POST';
        // $this->actions[count($this->actions)-1]['permissions'] = 1034;
        // $this->actions[count($this->actions)-1]['type'] = 1;
        // $this->actions[count($this->actions)-1]['index'] = true;

        return $this->actions;
    }

    private function returned()
    {
        $this->actions[count($this->actions)] = array();
        $this->actions[count($this->actions)-1]['title'] = __('cargo::view.to_returned_stock');
        $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::RETURNED_STOCK]);
        $this->actions[count($this->actions)-1]['method'] = 'POST';
        $this->actions[count($this->actions)-1]['permissions'] = 'to-returned-stock-action';
        $this->actions[count($this->actions)-1]['user_role'] = [1,3];
        $this->actions[count($this->actions)-1]['type'] = 1;
        $this->actions[count($this->actions)-1]['index'] = true;

        return $this->actions;
    }

    public function returned_stock()
    {
        $this->actions[count($this->actions)] = array();
        $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_return_mission');
        $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.return.mission',['type'=>Mission::RETURN_TYPE]);
        $this->actions[count($this->actions)-1]['method'] = 'POST';
        $this->actions[count($this->actions)-1]['js_function_caller'] = 'openPickupMissionModel(this,event)';
        $this->actions[count($this->actions)-1]['type'] = 1;
        $this->actions[count($this->actions)-1]['permissions'] = 'create-return-mission';
        $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
        $this->actions[count($this->actions)-1]['index'] = true;

        $this->actions[count($this->actions)] = array();
        $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_delivery_mission');
        $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.delivery.mission',['type'=>Mission::DELIVERY_TYPE]);
        $this->actions[count($this->actions)-1]['method'] = 'POST';
        $this->actions[count($this->actions)-1]['js_function_caller'] = 'openAssignShipmentCaptainModel(this,event)';
        $this->actions[count($this->actions)-1]['type'] = 1;
        $this->actions[count($this->actions)-1]['permissions'] = 'create-delivery-mission';
        $this->actions[count($this->actions)-1]['user_role'] = [1,3];
        $this->actions[count($this->actions)-1]['index'] = true;

        return $this->actions;
    }

    private function requestedPickup()
    {

        // $this->actions[count($this->actions)] = array();
        // $this->actions[count($this->actions)-1]['title'] = __('cargo::view.approve');
        // $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        // $this->actions[count($this->actions)-1]['permissions'] = 'approve-shipment-action';
        // $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::APPROVED_STATUS]);
        // $this->actions[count($this->actions)-1]['method'] = 'POST';
        // $this->actions[count($this->actions)-1]['type'] = 1;
        // $this->actions[count($this->actions)-1]['index'] = true;

        // $this->actions[count($this->actions)] = array();
        // $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
        // $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
        // $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
        // $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
        // $this->actions[count($this->actions)-1]['method'] = 'POST';
        // $this->actions[count($this->actions)-1]['type'] = 1;
        // $this->actions[count($this->actions)-1]['index'] = true;



        return $this->actions;
    }

    private function accepted()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_delivery_mission');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.delivery.mission',['type'=>Mission::DELIVERY_TYPE]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = 'openAssignShipmentCaptainModel(this,event)';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['permissions'] = 'create-delivery-mission';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_return_mission');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.return.mission',['type'=>Mission::RETURN_TYPE]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = 'openPickupMissionModel(this,event)';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['permissions'] = 'create-return-mission';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.transfer_to_branch');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.transfer.mission',['type'=>Mission::TRANSFER_TYPE]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = 'openTransferShipmentCaptainModel(this,event)';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['permissions'] = 'transfer-to-branch-action';
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;

            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
            $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;
            return $this->actions;
    }
    private function closed()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.approve');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['permissions'] = 'approve-shipment-action';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::APPROVED_STATUS]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['user_role'] = [1,3];
            $this->actions[count($this->actions)-1]['index'] = true;
            return $this->actions;
    }
    private function deliverd()
    {
            $this->actions[count($this->actions)] = array();
            $this->actions[count($this->actions)-1]['title'] = __('cargo::view.create_supply_mission');
            $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
            $this->actions[count($this->actions)-1]['url'] = route('shipments.action.create.supply.mission',['type'=>Mission::SUPPLY_TYPE]);
            $this->actions[count($this->actions)-1]['method'] = 'POST';
            $this->actions[count($this->actions)-1]['js_function_caller'] = 'openPickupMissionModel(this,event)';
            $this->actions[count($this->actions)-1]['permissions'] = 'create-supply-mission';
            $this->actions[count($this->actions)-1]['type'] = 1;
            $this->actions[count($this->actions)-1]['user_role'] = [1,3,4];
            $this->actions[count($this->actions)-1]['index'] = true;
            return $this->actions;
    }

    private function alert()
    {
        $this->actions[count($this->actions)] = array();
        $this->actions[count($this->actions)-1]['title'] = __('cargo::view.close');
        $this->actions[count($this->actions)-1]['icon'] = 'fa fa-trash';
        $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
        $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::CLOSED_STATUS]);
        $this->actions[count($this->actions)-1]['method'] = 'POST';
        $this->actions[count($this->actions)-1]['type'] = 1;
        $this->actions[count($this->actions)-1]['user_role'] = [1,3];
        $this->actions[count($this->actions)-1]['index'] = true;
        return $this->actions;
    }

    private function draft()
    {
        $this->actions[count($this->actions)] = array();
        $this->actions[count($this->actions)-1]['title'] = __('cargo::view.save');
        $this->actions[count($this->actions)-1]['icon'] = 'fa fa-check';
        $this->actions[count($this->actions)-1]['url'] = route('shipments.action',['to'=>Shipment::SAVED_STATUS]);
        $this->actions[count($this->actions)-1]['method'] = 'POST';
        $this->actions[count($this->actions)-1]['permissions'] = 'close-shipment-action';
        $this->actions[count($this->actions)-1]['type'] = 1;
        $this->actions[count($this->actions)-1]['user_role'] = [4];
        $this->actions[count($this->actions)-1]['index'] = true;

        return $this->actions;
    }
    private function default()
    {

            return $this->actions;
    }

    public static function getClosestMatch($inputName,$client_id,$state_id)
    {
        // Fetch all names from the database
        //  $names = DB::table('areas')->pluck('name')->toArray();
        $plan = Client::find($client_id);
        $areas = PlanFee::with(['areas' => function ($query) {
            $query
            ->where('desk_fee',">","0")
            ->where('active',"1")
            ->orderBy('area_id');
        }])
        ->where('state_id',(string) $state_id)
        ->where('plan_id',(string) $plan->plan_id)
        ->where('active',"1")->first();

        // Set an initial minimum distance and closest match
        $minDistance = null;
        $closestMatch = null;
        //dd($areas['areas'][0]['area']['name']);
        // Loop through each name in the database
        foreach ($areas['areas'] as $area) {
           // dd($area->name);
            // Calculate the Levenshtein distance between the input and the current name
            $distance = levenshtein($inputName, $area['area']->name);

            // Check if this distance is the smallest found so far
            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $closestMatch = $area['area']->name;
            }
        }
        return $closestMatch;
    }
}
