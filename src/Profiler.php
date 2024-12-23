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
	/** @var array<int, array<int, string>> */
	private array $values = array();
	/** @var list<string> */
	private array $title = array();
	private static ?Profiler $obj = null;
	/** @var array<string, int> */
	private array $timers = array();
	/** @var array<string, int> */
	private array $temp = array();
	private int $start;
	/** @var array<string, int> */
	private array $called = array();
	/** @var array<string, int> */
	private array $counters = array();
	private function __construct() {
		$this->start = hrtime(true);
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
	
	public static function destroy(): void {
		self::$obj = null;
	}
	/**
	 * Create instance if it does not exist. Allowed on startTimer.
	 * @return Profiler
	 */
	public static function getLazyInstance(): Profiler {
		if(self::$obj === null) {
			self::$obj = new Profiler();
		}
		return self::$obj;
	}
	
	/**
	 * Get instance, but it has to exist, throw Exception if it does not.
	 * @return Profiler
	 * @throws \RuntimeException
	 */
	public static function getExistingInstance(): Profiler {
		if(self::$obj === null) {
			throw new \RuntimeException("instance is not initialized");
		}
		return self::$obj;
	}
	
	public static function startTimer(string $id): void {
		$instance = self::getLazyInstance();
		if(isset($instance->temp[$id])) {
			throw new \RuntimeException("startTimer was called before on id '".$id."' without calling endTimer");
		}
		$instance->temp[$id] = hrtime(true);
	}
	
	public static function endTimer(string $id): void {
		$instance = self::getExistingInstance();
		if(!isset($instance->temp[$id])) {
			throw new \RuntimeException("endTimer was called before startTimer on '".$id."'");
		}
		if(!isset($instance->timers[$id])) {
			$instance->timers[$id] = 0;
		}
		if(!isset($instance->called[$id])) {
			$instance->called[$id] = 0;
		}
		$instance->called[$id]++;
		$spent = hrtime(true)-$instance->temp[$id];
		$instance->timers[$id] += $spent;
		unset($instance->temp[$id]);
	}
	
	public static function printTimers(): void {
		$timer = new Profiler();
		$table = new \TerminalTable($timer);
		$table->printTable();
	}

	public static function incrementCounter(string $id): void {
		$instance = self::getLazyInstance();
		if(!isset($instance->counters[$id])) {
			$instance->counters[$id] = 0;
		}
		$instance->counters[$id]++;
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

	private function loadTimers(): void {
		$instance = self::getExistingInstance();
		$total = hrtime(true)-$instance->start;

		$entry = array_fill(0, self::MAX, "");
		$entry[self::ENTRY] = "Time:";
		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 */
		$this->values[] = $entry;
		foreach($instance->timers as $key => $value) {
			/**
			 * @var list<string>
			 */
			$entry = array_fill(0, self::MAX, "");
			$entry[self::ENTRY] = $key;
			$entry[self::AMOUNT] = round($value/1000000000, 2);
			$entry[self::PERCENT] = round(($value/$total)*100, 2)."%";
			$entry[self::CALLED] = $instance->called[$key];
			/**
			 * @psalm-suppress InvalidPropertyAssignmentValue
			 */
			$this->values[] = $entry;
			#echo $key." ".round($value/1000000000, 2)." (".round(($value/$total)*100, 2)."%)".PHP_EOL;
		}

		$entry = array_fill(0, self::MAX, "");
		$entry[self::ENTRY] = "Total";
		$entry[self::AMOUNT] = round($total/1000000000, 2);
		$entry[self::CALLED] = array_sum($instance->called);
		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 */
		$instance->values[] = $entry;
	}

	private function loadCounters(): void {
		$instance = self::getExistingInstance();
		$total = array_sum($instance->counters);

		$entry = array_fill(0, self::MAX, "");
		$entry[self::ENTRY] = "Counters:";
		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 */
		$this->values[] = $entry;
		foreach($instance->counters as $key => $value) {
			/**
			 * @var list<string>
			 */
			$entry = array_fill(0, self::MAX, "");
			$entry[self::ENTRY] = $key;
			$entry[self::PERCENT] = round(($value/$total)*100, 2)."%";
			$entry[self::CALLED] = $value;

			/**
			 * @psalm-suppress InvalidPropertyAssignmentValue
			 */
			$this->values[] = $entry;
		}
		$entry = array_fill(0, self::MAX, "");
		$entry[self::ENTRY] = "total:";
		$entry[self::CALLED] = $total;
		/**
		 * @psalm-suppress InvalidPropertyAssignmentValue
		 */
		$this->values[] = $entry;
	}

	public function load(): void {
		$this->values = array();
		$this->loadTimers();
		$this->loadCounters();
		#echo "Total: ".round($total/1000000000,2).PHP_EOL;
	}
	
	public static function getInitTime(): int {
		$instance = self::getExistingInstance();
	return $instance->start;
	}
	
	public static function getTimers(): array {
		$instance = self::getExistingInstance();
	return $instance->timers;
	}
	
	public static function getCalled(): array {
		$instance = self::getExistingInstance();
	return $instance->called;
	}

	public static function getCounters(): array {
		$instance = self::getExistingInstance();
		return $instance->counters;
	}
}