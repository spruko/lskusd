<?php

namespace laravelLara\lskusd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use laravelLara\lskusd\utils\RequirementChecker;

class RequirementController extends Controller
{
    protected $requirements;

    public function __construct(RequirementChecker $checker)
    {
        $this->requirements = $checker;
    }


    public function index(){
        $requirements = $this->requirements->check(
            config('installer.requirements.requirements')
        );

        $phpSupportInfo = $this->requirements->checkPHPversion(
            config('installer.requirements.core.minPhpVersion')
        );

        return view('installer.requirement', compact('requirements', 'phpSupportInfo'));

    }
}
