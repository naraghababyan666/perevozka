<?php

$now = \Carbon\Carbon::now();
$usr = \App\Models\Company::query()->find(1)->first();
$usr->phone_number = $now->valueOf();
$usr->save();



