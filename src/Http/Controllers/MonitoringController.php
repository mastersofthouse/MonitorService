<?php

namespace SoftHouse\MonitoringService\Http\Controllers;

use Illuminate\Http\JsonResponse;
use SoftHouse\MonitoringService\EntryType;
use SoftHouse\MonitoringService\Http\Resources\MonitoringResource;
use SoftHouse\MonitoringService\Monitoring;

class MonitoringController extends Controller
{
    public function commands($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::COMMAND, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function events($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::EVENT, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function exception($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::EXCEPTION, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function gate($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::GATE, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function queue($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::QUEUE, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function request($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::REQUEST, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function schedule($id = null): JsonResponse
    {
        try{
            $data = Monitoring::resource(Monitoring::get(EntryType::SCHEDULE, $id));
            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }

    public function loggly($id = null): JsonResponse
    {
        try{
            if(is_null($id)){
                $data = Monitoring::resource(Monitoring::get(EntryType::LOGGLY));
            }else{
                $data = Monitoring::resourceBatch(Monitoring::get(EntryType::LOGGLY, $id));
            }

            return  response()->json(["success" => true, "data" => $data]);
        }catch (\Exception $exception){
            return  response()->json(["success" => false], 500);
        }
    }
}
