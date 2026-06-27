<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:process')->dailyAt('00:01');