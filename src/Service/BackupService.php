<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Service;

use Inescoin\Helper\BlockHelper;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Model\Block as BlockModel;

class BackupService
{
	protected $prefix;
	protected $destination;
	protected $folder;
	protected $fileName;
	protected $esService;
	protected $gzName;
	protected $force;

	protected $executionTimeStart;

	public function __construct($prefix, $fileName, $force = false)
	{
		$this->executionTimeStart = microtime(true);

		$this->prefix = $prefix;

		$this->_initFilename($fileName);

		$time = time();
		$this->folder = getcwd() . '/inescoin-blockchain-' . $time;
		if (!is_dir($this->folder)) {
			mkdir($this->folder, 0777);
		}

		$this->destination =  $this->folder . $this->fileName;
		$this->gzName = $this->destination . '.tar.gz';

		$this->force = $force;

		$this->blockchainManager = new BlockchainManager($prefix);

		$this->_checkIfGzExists();
	}

	public function export()
	{
		$lastBlock = $this->blockchainManager->getBlock()->last();

		if (null === $lastBlock) {
		    echo '[Blockchain] Last block is null' . PHP_EOL;
		    exit();
		}

		echo '[Blockchain] Start Scan... 1' . PHP_EOL;
		$range = 1000;
		$rangePosition = 1;
		$fromBlockHeight = 0;
		$topBlockHeight = $lastBlock->getHeight();

		$page = 1;
		$position = 1;
		$splitSize = 1000;
		$totalPages = $range > $topBlockHeight ? 1 : ceil($topBlockHeight / $range);

		$toBlockHeight = $fromBlockHeight + $range;
		if ($toBlockHeight > $topBlockHeight) {
		    $toBlockHeight = $topBlockHeight;
		}

		$previousBlock = null;
		file_put_contents($this->destination . ".$page.json", '[' . PHP_EOL);
		while(!empty($blocks = $this->blockchainManager->getBlock()->range($fromBlockHeight, $range))) {
		    if ($toBlockHeight === $fromBlockHeight && $topBlockHeight === $toBlockHeight) {
		        break;
		    }

		    var_dump('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');
		    $_blocks = [];
		    $contents = '';

		    foreach ($blocks as $block) {
		        if (null !== $previousBlock) {
		            if ($previousBlock->getHeight() === $block->getHeight()) {
		                var_dump('[Blockchain] $previousBlock->getHeight === $block->getHeight');
		                continue;
		            }

		            if (!$previousBlock->isNextValid($block)) {
		                var_dump('[Blockchain] [error] $previousBlock->isNextValid');
		                exit();
		            }
		        }

		        $previousBlock = $block;
		        $compressedBlock = BlockHelper::compress($block->getDataAsArray());
		        $height = $block->getHeight();
		        $contents .= json_encode($compressedBlock);

		        if ($position % $splitSize === 0) {
		            $contents .= PHP_EOL;
		            file_put_contents($this->destination . ".$page.json", $contents, FILE_APPEND | LOCK_EX);
		            file_put_contents($this->destination . ".$page.json", ']' . PHP_EOL, FILE_APPEND | LOCK_EX);
		            $contents = '';

		            $page++;
		            var_dump('     -> Finsished -> ' . $this->destination . ".$page.json");
		            file_put_contents($this->destination . ".$page.json", '[' . PHP_EOL);
		        }

		        if ($contents !== '') {
		            $contents .= ',' . PHP_EOL;
		        }

		        $position++;
		    }

		    if (!empty($contents) || $rangePosition == $totalPages) {
		        $contents = substr($contents, 0, -1);

		        if ($rangePosition == $totalPages) {
		            $contents = substr($contents, 0, -1);
		        }
		    }

		    var_dump("------------------------------------------> totalPages: [$rangePosition|$totalPages]");
		    file_put_contents($this->destination . ".$page.json", $contents, FILE_APPEND | LOCK_EX);

		    $fromBlockHeight = $toBlockHeight;
		    $toBlockHeight = $toBlockHeight + $range;
		    if ($toBlockHeight > $topBlockHeight) {
		        $toBlockHeight = $topBlockHeight;
		    }

		    $rangePosition++;
		}

		if ($rangePosition === $totalPages) {
		    $contents = substr($contents, 0, -1);
		}

		file_put_contents($this->destination . ".$page.json", ']' , FILE_APPEND | LOCK_EX);

		$dzfile = $this->destination . '.tar';
		$pharData = new \PharData($dzfile);
		$pharData->buildFromDirectory($this->folder);

		for ($i = 1; $i <= $page; $i++) {
		    //unlink($this->destination . ".$i.json");
		}

		$pharData->setMetadata(['page' => $page]);

		$pharData->compress(\Phar::GZ);
		@unlink($dzfile);


		$endTime = microtime(true);
        $execTime = ($endTime - $this->executionTimeStart);

        echo ''. PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '   Exported file -> ' . $dzfile . '.gz' . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '!! finish !! => Execution time: ' . $execTime .' sec' . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
	}

	public function import()
	{
		$this->blockchainManager->dropTables();

		$gzFile = getcwd() . $this->fileName;
		if (!file_exists($gzFile)) {
		    die("'{$gzFile}' not found" . PHP_EOL);
		}

		echo "{$gzFile} loaded, start scan..." . PHP_EOL;


		$backupPhar = new \PharData($gzFile, \FilesystemIterator::UNIX_PATHS);

		echo "Check file format [tar]..." . PHP_EOL;

		if ($backupPhar->isFileFormat(\Phar::TAR)) {
			echo "    - Valid" . PHP_EOL;
		} else {
			throw new \Exception("Invalid file format", 1);
		}

		echo "Check compression..." . PHP_EOL;

		if ($backupPhar->isCompressed()) {
			echo "    - Valid" . PHP_EOL;
		} else {
			throw new \Exception("Invalid compression", 1);
		}

		$metadata = $backupPhar->getMetadata();

		if (!$metadata || !is_array($metadata) || !isset($metadata['page']) || !is_int($metadata['page'])) {
			throw new \Exception("Invalid metadata", 1);
		}

		$time = time();
		$this->folder = $this->folder; //getcwd() . '/inescoin-blockchain-' . $time . '/';
		$backupPhar->extractTo($this->folder);

		if ($this->force) {
			echo "Database [{$this->prefix}]: cleaned" . PHP_EOL;
			//$this->esService->resetAll(0);
		}

		echo "Import stared..." . PHP_EOL;

		$page = 1;
		$rowsCount = 0;
		$previousBlock = null;
		$test = true;
		while ($page <=  $metadata['page']) {
			$path = $this->folder . "/inescoin.$page.json";

			if (file_exists($path)) {
				echo "    - " . $path . PHP_EOL;
				$blocks = json_decode(file_get_contents($path));

				if (!is_array($blocks)) {
					throw new \Exception("Invalid data", 1);
				}

				$rows = iterator_to_array(BlockHelper::bulkDecompress($blocks));
				try {
					$rowsCount += BlockHelper::extractBlock($rows, $this->prefix);
				} catch (Exception $e) {
					echo $e->getMessage();
				}

		        // $this->esService->blockService()->bulkBlocks($_blocks);
				echo "Total: " . $rowsCount .  PHP_EOL;
		        @unlink($path);
			} else {
				echo "x => File not found: " . $path . PHP_EOL;
			}

			$page++;
			// exit();
		}

		@rmdir($this->folder);

		$endTime = microtime(true);
        $execTime = ($endTime - $this->executionTimeStart);
        echo '!! finish !! => Execution time: ' . $execTime .' sec' . PHP_EOL;
	}

	private function _initFilename($fileName)
	{
		if (substr($fileName, 0, 2) !== './' && substr($fileName, 0, 1) !== '/') {
		    $fileName = '/' . $fileName;
		} else {
		    $fileName = str_replace('./', '/', $fileName);
		}

		$this->fileName = $fileName;
	}

	private function _checkIfGzExists()
	{
		if (file_exists($this->destination . '.tar.gz')) {
		    if ($this->force) {
		        @unlink($this->gzName);
		    } else {
		        die("'{$this->gzName}' already exit" . PHP_EOL);
		    }
		}
	}
}
