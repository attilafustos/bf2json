<?php

echo "Betafligh blackbox CSV to virtualGimbal json".PHP_EOL;

$input_file = "";
$output_file = "";
$frequency = 1000;//240

if(count($argv)==1)
	die("Usage: bf2json.exe -input betaflight.csv -output virtualGimbal.json");

for($i=0; $i < $argc; $i++) {
	switch($argv[$i])
	{
		case "-i":
			$input_file = $argv[$i+1];
			echo "input file: $input_file".PHP_EOL;
			$i++;
			break;
			
		case "-o":
			$output_file = $argv[$i+1];
			echo "output file: $output_file".PHP_EOL;
			$i++;
			break;
			
		default:
			break;		
	}	
}
$params = array();
$headers = array();
$headers_index = array();
$row = 0;
$gyro_frequency = 0;
$looptime = 0;
$gyro_scale = 0;

$fhi = fopen($input_file, "r");
$fho = fopen($output_file, "w");
if ($fhi !== FALSE && $fho !== FALSE) {

	fputs($fho,"{".PHP_EOL);
	fputs($fho,"    \"frequency\": $frequency,".PHP_EOL);
	fputs($fho,"    \"angular_velocity_rad_per_sec\": [".PHP_EOL);
	fputs($fho,"        [".PHP_EOL);
	$step = 0;
    while (($data = fgetcsv($fhi, 1000, ",")) !== FALSE) {
        $num = count($data);		
		if($num == 2) {
			$params[$data[0]] = $data[1];			
			continue;
		} else {
			if(count($headers) == 0) {
				$looptime = $params["looptime"];
				$gyro_frequency = 1000000 / $looptime;
				//$interval = $gyro_frequency / $frequency;
				$interval = 1000.0 / $frequency;
				$gyro_scale = floatval($params["gyroScale"]);
				echo "Gyro frequency:$gyro_frequency".PHP_EOL;
				echo "Loop time:$looptime".PHP_EOL;
				echo "Resample Interval: $interval".PHP_EOL;
				echo "Gyro Scale: $gyroScale".PHP_EOL;
				for ($c=0; $c < $num; $c++) {
					$headers[] = $data[$c];
					$headers_index[$data[$c]] = $c;
				}
				//print_r($params);
				//die();
				continue;
			}
		}
		
		$loop_iteration = $data[$headers_index["loopIteration"]];
		$gyro_x = floatval($data[$headers_index["gyroADC[0]"]]) * (pi()/180.0);
		$gyro_y = floatval($data[$headers_index["gyroADC[1]"]]) * (pi()/180.0);
		$gyro_z = floatval($data[$headers_index["gyroADC[2]"]]) * (pi()/180.0);

		//if($step==0) {
			fputs($fho,"            $gyro_x,".PHP_EOL);
			fputs($fho,"            $gyro_y,".PHP_EOL);
			fputs($fho,"            $gyro_z,".PHP_EOL);
			
			$step = floor($interval);
			echo "Loop:$loop_iteration X:$gyro_x ; Y:$gyro_y ; Z:$gyro_z".PHP_EOL;
		//} else {
		//	$step--;
		//}
		
				
		
        $row++;       
    }
	fseek($fho, -3, SEEK_CUR);
	fputs($fho,PHP_EOL);
	fputs($fho,"        ]".PHP_EOL);
	fputs($fho,"    ]".PHP_EOL);
	fputs($fho,"}".PHP_EOL);
	
	print_r($headers_index);
	
    fclose($fhi);	
	fclose($fho);
	echo "Json file written to $output_file".PHP_EOL;
}	


