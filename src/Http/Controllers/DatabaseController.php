<?php

namespace laravelLara\lskusd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use laravelLara\lskusd\utils\DatabaseManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function index(){
        $response = $this->databaseManager->migrateAndSeed();
        return redirect()->route('SprukoAppInstaller::register');
    }
}
