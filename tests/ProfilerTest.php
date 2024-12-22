<?php
declare(strict_types=1);
namespace plibv4\profiler;
use PHPUnit\Framework\TestCase;
class ProfilerTest extends TestCase {
	function tearDown(): void {
		Profiler::destroy();
	}

	function testInit(): void {
		Profiler::init();
		$this->assertSame(true, is_int(Profiler::getInitTime()));
	}
	
	function testInitDouble(): void {
		Profiler::init();
		$this->expectException(\RuntimeException::class);
		Profiler::init();
	}
	
	function testAddTimer(): void {
		Profiler::startTimer("test");
		$this->assertArrayHasKey("test", Profiler::getTimers());
		$this->assertArrayNotHasKey("testx", Profiler::getTimers());
	}
}