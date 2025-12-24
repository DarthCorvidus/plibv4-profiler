<?php
declare(strict_types=1);
namespace plibv4\profiler;
use PHPUnit\Framework\TestCase;
final class ProfilerTest extends TestCase {
	#[\Override]
	function tearDown(): void {
		Profiler::destroy();
	}

	function testInit(): void {
		Profiler::init();
		$this->assertGreaterThan(0, Profiler::getInitTime());
	}
	
	function testInitDouble(): void {
		Profiler::init();
		$this->expectException(\RuntimeException::class);
		Profiler::init();
	}
	
	function testStartTimerOnly(): void {
		Profiler::startTimer("test");
		$this->assertArrayNotHasKey("test", Profiler::getTimers());
	}

	function testStartTimerTwice(): void {
		Profiler::startTimer("test");
		$this->expectException(\RuntimeException::class);
		Profiler::startTimer("test");
	}

	function testStartEndTimer(): void {
		Profiler::startTimer("test");
		Profiler::endTimer("test");
		$this->assertArrayHasKey("test", Profiler::getTimers());
		$this->assertArrayNotHasKey("testx", Profiler::getTimers());
		$this->assertArrayHasKey("test", Profiler::getCalled());
	}

	function testStartEndTimerTwice(): void {
		Profiler::startTimer("test");
		Profiler::endTimer("test");
		Profiler::startTimer("test");
		Profiler::endTimer("test");
		$this->assertArrayHasKey("test", Profiler::getTimers());
		$this->assertArrayNotHasKey("testx", Profiler::getTimers());
		$this->assertArrayHasKey("test", Profiler::getCalled());
	}

	function testStartEndTimerOpenTwice(): void {
		Profiler::startTimer("test");
		Profiler::endTimer("test");
		Profiler::startTimer("test");
		// make sure that an open/close call is cleared.
		$this->expectException(\RuntimeException::class);
		Profiler::startTimer("test");
	}

	function testEndBeforeStart(): void {
		Profiler::init();
		$this->expectException(\RuntimeException::class);
		Profiler::endTimer("test");
	}

	public function testIncrementCounter(): void {
		Profiler::incrementCounter("test");
		$this->assertArrayHasKey("test", Profiler::getCounters());
		$this->assertEquals(1, Profiler::getCounters()["test"]);
		Profiler::incrementCounter("test");
		$this->assertEquals(2, Profiler::getCounters()["test"]);
	}
}