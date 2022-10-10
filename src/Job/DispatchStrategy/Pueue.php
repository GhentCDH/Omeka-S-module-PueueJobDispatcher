<?php
namespace PueueJobDispatcher\Job\DispatchStrategy;

use Omeka\Job\DispatchStrategy\StrategyInterface;
use Omeka\Job\Exception;
use Omeka\Entity\Job;
use Omeka\Stdlib\Cli;
use Laminas\Log\Logger;

class Pueue implements StrategyInterface
{
    /**
     * @var Cli
     */
    protected Cli $cli;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var string
     */
    protected string $basePath;

    /**
     * @var string
     */
    protected string $serverUrl;

    /**
     * @var string|null
     */
    protected ?string $phpPath;

    /**
     * @var string|null
     */
    protected ?string $pueuePath;

    /**
     * @var string|null
     */
    private ?string $group;

    /**
     *
     */
    protected array $commands = [];

    /**
     * Create the PHP-CLI-based job dispatch strategy.
     *
     * @param Cli $cli CLI service
     * @param string $basePath Base URL for the installation
     * @param string|null $phpPath Path to the PHP CLI
     */
    public function __construct(Cli $cli, Logger $logger, string $basePath, string $serverUrl, ?string $phpPath = null, ?string $pueuePath = null, ?string $group = null)
    {
        $this->cli = $cli;
        $this->logger = $logger;
        $this->basePath = $basePath;
        $this->serverUrl = $serverUrl;
        $this->phpPath = $phpPath;
        $this->pueuePath = $pueuePath;
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return void
     */
    public function setGroup(string $group)
    {
        $this->group = $group;
    }

    /**
     * Perform the job in the background.
     *
     * Jobs may need access to variables that are impossible to derive from
     * outside a web context. Here we pass the variables via shell arguments.
     * The perform-job script then sets them to the PHP-CLI context.
     *
     * @todo Pass the server URL, or compents required to set one
     * @see \Laminas\View\Helper\BasePath
     * @see \Laminas\View\Helper\ServerUrl
     *
     * {@inheritDoc}
     */
    public function send(Job $job)
    {
        if ($this->phpPath) {
            $phpPath = $this->cli->validateCommand($this->phpPath);
            if (false === $phpPath) {
                throw new Exception\RuntimeException('PHP-CLI error: invalid PHP path.');
            }
        } else {
            $phpPath = $this->cli->getCommandPath('php');
            if (false === $phpPath) {
                throw new Exception\RuntimeException('PHP-CLI error: cannot determine path to PHP.');
            }
        }

        if ($this->pueuePath) {
            $pueuePath = $this->cli->validateCommand($this->pueuePath);
            if (false === $pueuePath) {
                throw new Exception\RuntimeException('Pueue client error: invalid client path.');
            }
        } else {
            $pueuePath = $this->cli->getCommandPath('pueue');
            if (false === $pueuePath) {
                throw new Exception\RuntimeException('Pueue client error: cannot determine path to pueue.');
            }
        }

        $script = OMEKA_PATH . '/application/data/scripts/perform-job.php';

        $jobCommand = sprintf(
            '%s %s --job-id %s --base-path %s --server-url %s',
            escapeshellcmd($phpPath),
            escapeshellarg($script),
            escapeshellarg($job->getId()),
            escapeshellarg($this->basePath),
            escapeshellarg($this->serverUrl)
        );

        $cliCommand = sprintf(
            '%s add %s "%s"',
            escapeshellcmd($pueuePath),
            $this->group ? " -g ".escapeshellarg($this->group) : "",
            $jobCommand
        );

        $status = $this->cli->execute(sprintf('%s > /dev/null 2>&1 &', $cliCommand));
        if ($status === false) {
            throw new Exception\RuntimeException('Pueue error: job script failed to execute.');
        }
    }
}
