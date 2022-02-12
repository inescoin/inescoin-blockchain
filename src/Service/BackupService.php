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
	protected $destinationPage;
	protected $folder;
	protected $fileName;
	protected $esService;
	protected $gzName;
	protected $force;

	protected $executionTimeStart;

	public function __construct($prefix, $fileName, $force = false)
	{
		$this->executionTimeStart = microtime(true);

		$this->force = $force;
		$this->prefix = $prefix;

		$this->_initFilename($fileName);

		$this->folder = getcwd() . '/' . $prefix. '-blockchain-' . time();

		if (!is_dir($this->folder)) {
			mkdir($this->folder, 0777);
		}

		$this->destinationPage =  $this->folder . '/inescoin';
	}

	public function export()
	{
		$this->_checkIfGzExists();

		$this->blockchainManager = new BlockchainManager($this->prefix);

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
		file_put_contents($this->destinationPage . ".$page.json", '[' . PHP_EOL);
		while(!empty($blocks = $this->blockchainManager->getBlock()->range($fromBlockHeight, $range))) {
		    if ($toBlockHeight === $fromBlockHeight && $topBlockHeight === $toBlockHeight) {
		        break;
		    }

		    var_dump('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');
		    $_blocks = [];
		    $contents = '';

		    foreach ($blocks as $block) {
		    	$blockModel = new BlockModel($block->getDataAsArray());
		        if (null !== $previousBlock) {
		            if (!$previousBlock->isNextValid($blockModel)) {
		                var_dump('[Blockchain] [error] $previousBlock->isNextValid');
		                exit();
		            }
		        }

		        $previousBlock = $blockModel;
		        $compressedBlock = BlockHelper::compress($block->getDataAsArray());
		        $height = $block->getHeight();
		        $contents .= json_encode($compressedBlock);

		        if ($position % $splitSize === 0) {
		            $contents .= PHP_EOL;
		            file_put_contents($this->destinationPage . ".$page.json", $contents, FILE_APPEND | LOCK_EX);
		            file_put_contents($this->destinationPage . ".$page.json", ']' . PHP_EOL, FILE_APPEND | LOCK_EX);
		            $contents = '';

		            var_dump('     -> Finsished -> ' . $this->destinationPage . ".$page.json");
			    	var_dump("------------------------------------------> totalPages: [$rangePosition|$totalPages]");

		            $page++;
		            file_put_contents($this->destinationPage . ".$page.json", '[' . PHP_EOL);

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

		    if ($rangePosition == $totalPages) {
		    	var_dump('     -> Finsished -> ' . $this->destinationPage . ".$page.json");
			    file_put_contents($this->destinationPage . ".$page.json", $contents, FILE_APPEND | LOCK_EX);
			    var_dump("------------------------------------------> totalPages: [$rangePosition|$totalPages]");
		    }

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

		file_put_contents($this->destinationPage . ".$page.json", ']' , FILE_APPEND | LOCK_EX);

		$pharData = new \PharData($this->dzfile);
		$pharData->buildFromDirectory($this->folder);

		for ($i = 1; $i <= $page; $i++) {
		    @unlink($this->destinationPage . ".$i.json");
		}


		$pharData->setMetadata(['page' => $page]);

		$pharData->compress(\Phar::GZ);
		@unlink($this->dzfile);
		//@rmdir($this->folder);


		$endTime = microtime(true);
        $execTime = ($endTime - $this->executionTimeStart);

        echo ''. PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '   Exported file -> ' . $this->gzName . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '!! finish !! => Execution time: ' . $execTime .' sec' . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
	}

	public function import()
	{
		if (!file_exists($this->gzName)) {
	        @rmdir($this->folder);
		    die("'{$this->gzName}' not found." . PHP_EOL);
		}

		$database = getcwd() . '/' . $this->prefix . '.dbi';

		if (file_exists($database)) {
			if ($this->force) {
				if(@unlink($database)) {
					echo "{$database} removed..." . PHP_EOL;
				}
			} else {
		        @rmdir($this->folder);
			    die(PHP_EOL . "'{$database}' file already exists, use --force option to replace it." . PHP_EOL . PHP_EOL);
			}
		}


		echo "{$this->gzName} loaded, start scan..." . PHP_EOL;

		$backupPhar = new \PharData($this->gzName, \FilesystemIterator::UNIX_PATHS);

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

		(new BlockchainManager($this->prefix))->dropTables();

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
				unlink($path);

				if (!is_array($blocks)) {
					throw new \Exception("Invalid data", 1);
				}

				$rows = iterator_to_array(BlockHelper::bulkDecompress($blocks));

				try {
					$rowsCount += BlockHelper::extractBlock($rows, $this->prefix);
				} catch (Exception $e) {
					echo $e->getMessage();
				}

				echo "Total: " . $rowsCount .  PHP_EOL;
		        @unlink($path);
			} else {
				echo "x => File not found: " . $path . PHP_EOL;
			}

			$page++;
		}

		@rmdir($this->folder);

		$endTime = microtime(true);
        $execTime = ($endTime - $this->executionTimeStart);

        echo ''. PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '   Created file -> ' . $database . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
        echo '!! finish !! => Execution time: ' . $execTime .' sec' . PHP_EOL;
        echo '---------------------------------------------------------------------'. PHP_EOL;
	}

	private function _initFilename($fileName)
	{
		$this->fileName = getcwd() . '/' .$fileName;
		$this->fileName = str_replace('/./', '/', $this->fileName);
		$this->fileName = str_replace('.tar.gz', '', $this->fileName);

		$this->dzfile = $this->fileName . '.tar';
		$this->gzName = $this->dzfile.'.gz';
	}

	private function _checkIfGzExists()
	{
		if (file_exists($this->gzName)) {
		    if ($this->force) {
		        @unlink($this->gzName);
		    } else {
		        @rmdir($this->folder);
		        die(PHP_EOL . "Error => '{$this->gzName}' file already exists, use --force option to replace it." . PHP_EOL . PHP_EOL);
		    }
		}
	}
}
