<?php

use Inescoin\Entity\Block;
use Inescoin\Helper\BlockHelper;
use Inescoin\Manager\BlockchainManager;
use Monolog\Test\TestCase;

class NodeTest extends TestCase
{
	public $pid;

	public function __construct() {
		$this->killAll();

  	// 	for($i = 1; $i < 10; $i++) {
			// $command = __DIR__ . '/../../bin/inescoin-node --rpc-bind-port=808'.$i.' --p2p-bind-port=303'.$i.' --prefix=NodeTestPhpUnit'.$i.' > /dev/null &';
			// //echo $command . PHP_EOL;
			// \exec($command, $output);
  	// 	}

		parent::__construct();
	}

	public function testInescoinNode()
	{
		// \sleep(5);
		// $var = true;
		// $this->assertTrue($var);
	}

	private function killAll() {
		// \exec('ps -aux | grep inescoin-node', $output);
		// //var_dump($output);

		// $pid = function ($line) {
		// 	return (int) (array_values(
		// 		array_filter(explode(' ', $line),
		// 		function($value) {
		// 			return !empty($value);
		// 		})
		// 	))[1];
		// };

		// foreach ($output as $line) {
		// 	if (str_contains($line, 'NodeTestPhpUnit')) {
		// 		\exec('kill -9 ' . $pid($line));
		// 	}
		// }

		// \exec('rm -rf ./NodeTestPhpUnit*');
		// \exec('ps -aux | grep inescoin-node', $output2);
		//var_dump($output2);
	}

	public function __destruct() {
		$this->killAll();
	}
}
