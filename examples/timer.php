#!/usr/bin/env php
<?php
namespace plibv4\profiler;
require __DIR__."/../vendor/autoload.php";
Profiler::startTimer("sleep");
Profiler::startTimer("longsleep");
sleep(5);
Profiler::endTimer("longsleep");
Profiler::startTimer("shortsleep");
sleep(1);
Profiler::endTimer("shortsleep");
Profiler::startTimer("shortsleep");
sleep(1);
Profiler::endTimer("shortsleep");
Profiler::startTimer("shortsleep");
sleep(1);
Profiler::endTimer("shortsleep");
Profiler::startTimer("longsleep");
sleep(5);
Profiler::endTimer("longsleep");
Profiler::EndTimer("sleep");
Profiler::printTimers();

echo PHP_EOL."Clearing profiler:".PHP_EOL.PHP_EOL;

Profiler::clear();
Profiler::printTimers();