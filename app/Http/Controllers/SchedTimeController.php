<?php

namespace App\Http\Controllers;

use App\Models\SchedTime;
use App\Models\Shoptable;
use App\Models\TableBooking;
use App\Models\Tablesched;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchedTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return date('YYYY-MM-DD', strtotime($request->date));

        $schedtime= new SchedTime();
        $day= $request->day;
        $istoday= $request->istoday;
        //Find if the day is today
        //If it is today, then we return the timeslots existing in the database
        

        if($istoday==true){
          

            $tables= Shoptable::where('capacity',$request->capacity)
            ->orWhere('capacity',($request->capacity+1))
            ->first();
            if($tables){
                    $tablebookings= TableBooking::where('date',$request->date)->get();
                    $schedtimes= SchedTime::where('booked',0)->get();
                    
                   return $schedtimes;
            }
            else{
                return response()->json(['Error'=>'No tables available']);


            }



            //Return timeslots from the DB with the given slot

        }
        else{
            // $tablescheds= Tablesched::where('shop_id',$request->shop_id)->where('day',$request->day)->first();  
            // return $schedtime->generateTimeSlots ($tablescheds->opening,$tablescheds->closing,$tablescheds->seating_time,$request->table_id);

   
        //Fetch tables
        $tables= Shoptable::where('capacity',$request->capacity)->orWhere('capacity',($request->capacity+1))->first();
    $ts=  $schedtime->generateTimeSlots ('10:00:00','12:00:00',15,$tables->id);
    $testarray= array();
  
        if($tables){
                   //Generate Time Slots for that ID
              foreach( $ts as $timeslot){
                //   return $timeslot;
                    //Find if any prev bookings have been made
                    $booking= TableBooking::where('date',date('YYYY-MM-DD', strtotime($request->date)))->where('table_id',$timeslot['table_id'])->where('preftime',$timeslot['from'])->count();
                    if($booking>0){
                       
                        $timeslot['booked']="1";
                       
                        
                        array_push($testarray,$timeslot);

                    }
                    else{
                        $timeslot['booked']="0";
                      
                        array_push($testarray,$timeslot);
                    }

              }
              return $testarray;
        }
        else{
            return response()->json(['Error'=>'No tables available']);
        }
    }

 
    }



    //API to delete the old time slots and generate a new one(along with assigning the users with a new time slot)
    public function autogenerateslots()
    {
         SchedTime::truncate();
         $schedtime = new SchedTime();
         $day = Carbon::now()->format('l');
         
         $tablescheds= TableSched::where('day',$day)->get();
         $arr= array();
         foreach($tablescheds as $tablesched){
            
             $tables=   Shoptable::where('shop_id',$tablesched->shop_id)->get();
           
             foreach($tables as $table){

                $tablearr= $schedtime->generateTimeSlots ($tablesched->opening,$tablesched->closing,$tablesched->seating_time,$table->id);
                array_push($arr, $tablearr);
             }
    

         }
        SchedTime::insert($arr[0]);
       
    }
}
