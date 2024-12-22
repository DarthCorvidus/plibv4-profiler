<?php
namespace plibv4\profiler;
/**
 * Simple Profiler.
 */
class Profiler implements \TerminalTableModel, \TerminalTableLayout {
	const START_TIMER = 1;
	const END_TIMER = 2;
	const ENTRY = 0;
	const AMOUNT = 1;
	const PERCENT = 2;
	const CALLED = 3;
	const MAX = 4;
	private array $values = array();
	private array $title = array();
	private static Profiler $obj;
	private array $timers = array();
	private array $temp = array();
	private int $all;
	private array $called = array();
	private function __construct() {
		$this->all = hrtime(true);
		$this->called = array();
		$this->timers = array();
		$this->title = array("Key", "Time", "Pct", "Called");
	}
	
	public static function init(): void {
		/**
		 * This check is marked by Psalm, but having it is a necessary 'evil',
		 * as Profiler is a Singleton.
		 * @psalm-suppress RedundantPropertyInitializationCheck
		 */
		if(isset(self::$obj)) {
			throw new \RuntimeException("called Profiler::init more than once");
		}
		self::$obj = new Profiler();
	}

	public static function clear(): void {
		self::$obj = new Profiler();
	}
	
	public static function startTimer(string $id): void {
		/**
		 * @psalm-suppress RedundantPropertyInitializationCheck
		 */
		if(!isset(self::$obj)) {
			self::$obj = new Profiler();
		}
		if(!isset(self::$obj->timers[$id])) {
			self::$obj->timers[$id] = 0;
		}
		if(!isset(self::$obj->called[$id])) {
			self::$obj->called[$id] = 0;
		}
		self::$obj->called[$id]++;
		self::$obj->temp[$id] = hrtime(true);
	}
	
	public static function endTimer(string $id): void {
		$spent = hrtime(true)-self::$obj->temp[$id];
		self::$obj->timers[$id] += $spent;
	}
	
	public static function printTimers(): void {
		$timer = new Profiler();
		$table = new \TerminalTable($timer);
		$table->printTable();
		$total = hrtime(true)-self::$obj->all;
	}

	public function getCell($col, $row): string {
		return $this->values[$row][$col];
	}

	public function getCellAttr(int $col, int $row): array {
		return array();
	}

	public function getCellBack(int $col, int $row): int {
		return 0;
	}

	public function getCellFore(int $col, int $row): int {
		return 0;
	}

	public function getCellJustify(int $col, int $row): int {
		return 0;
	}

	public function getColumns(): int {
		return self::MAX;
	}

	public function getRows(): int {
		return count($this->values);
	}

	public function getTitle(int $col): string {
		return $this->title[$col];
	}

	public function hasTitle(): bool {
		return TRUE;
	}

	public function load(): void {
		$this->values = array();
		$total = hrtime(true)-self::$obj->all;
		
		foreach(self::$obj->timers as $key => $value) {
			$entry = array_fill(0, self::MAX, "");
			$entry[self::ENTRY] = $key;
			$entry[self::AMOUNT] = round($value/1000000000, 2);
			$entry[self::PERCENT] = round(($value/$total)*100, 2)."%";
			$entry[self::CALLED] = self::$obj->called[$key];
			$this->values[] = $entry;
			#echo $key." ".round($value/1000000000, 2)." (".round(($value/$total)*100, 2)."%)".PHP_EOL;
		}
		foreach(self::$obj->called as $key => $value) {
			#echo $key." ".$value.PHP_EOL;
		}

		$entry = array_fill(0, self::MAX, "");
		$entry[self::ENTRY] = "Total";
		$entry[self::AMOUNT] = round($total/1000000000, 2);
		$entry[self::CALLED] = array_sum(self::$obj->called);
		$this->values[] = $entry;
		#echo "Total: ".round($total/1000000000,2).PHP_EOL;
		
	}

}