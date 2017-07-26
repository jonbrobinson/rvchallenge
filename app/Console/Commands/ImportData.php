<?php

namespace App\Console\Commands;

use App\City;
use App\Service\IdGenerator;
use App\State;
use App\User;
use Illuminate\Console\Command;

class ImportData extends Command
{
    protected $idGenerator;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data from Red Ventures Files';

    /**
     * Create a new command instance.
     *
     * @param IdGenerator $idGenerator
     */
    public function __construct(IdGenerator $idGenerator)
    {
        parent::__construct();

        $this->idGenerator = $idGenerator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set("auto_detect_line_endings", true);

        $order = ['User', 'State', 'City'];

        foreach ($order as $name) {
            $this->uploadCsvToDb($name);
        }
    }

    protected function uploadCsvToDb($name) {
        $path = $this->getCsvByName($name);
        $info = pathinfo($path);

        $handle = fopen($path, "r");

        $skip = true;
        while ($data = fgetcsv($handle)){
            if ($info['filename'] == 'City' && !$skip) {
                $this->insertCity($data);
            }

            if ($info['filename'] == 'State' && !$skip) {
                $this->insertState($data);
            }

            if ($info['filename'] == 'User' && !$skip) {
                $this->insertUser($data);
            }

            $skip = false;
        }

        fclose($handle);

    }

    protected function getCsvByName($name) {
        $storageDir = 'app/data';

        $path = storage_path($storageDir.'/'.$name.'.csv');

        return $path;
    }

    protected function insertCity($cityRow)
    {
        $city = City::create([
            'name' => $cityRow[0],
            'state_id' => $cityRow[1],
            'status' => $cityRow[2],
            'latitude' => $cityRow[3],
            'longitude' => $cityRow[4],
            'created_at' => strtotime($cityRow[6]),
            'updated_at' => strtotime($cityRow[7])
        ]);

        return $city;
    }

    protected function insertState($stateRow)
    {
        $state = State::create([
            'name' => $stateRow[0],
            'abbreviation' => $stateRow[1],
            'created_at' => strtotime($stateRow[3]),
            'updated_at' => strtotime($stateRow[4])
        ]);

        return $state;
    }

    protected function insertUser($userRow)
    {
        $state = User::create([
            'id' => $this->idGenerator->getToken(10),
            'first_name' => $userRow[0],
            'last_name' => $userRow[1],
            'access_token' => $this->idGenerator->getToken(16),
            'created_at' => strtotime($userRow[3]),
            'updated_at' => strtotime($userRow[4])
        ]);

        return $state;
    }
}
