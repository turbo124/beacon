<?php

namespace Turbo124\Beacon\Jobs\Database\Traits;

trait StatusVariables
{

	public function getVariables()
	{

        $db = DB::select(DB::raw("SHOW STATUS"));

        $obj = new \stdClass;

        $new_obj = collect($db)->map(function($item) use($obj){

	        $obj->{$item->Variable_name} = $item->Value;
	  
	        return $obj;

		});

    }

	public function getSlaveVariables()
	{

        $db = DB::select(DB::raw("SHOW SLAVE STATUS"));

        $obj = new \stdClass;

        $new_obj = collect($db)->map(function($item) use($obj){

	        $obj->{$item->Variable_name} = $item->Value;
	  
	        return $obj;

		});

	}

}
