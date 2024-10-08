<?php

namespace Esoftdream\Syloer\Libraries;

use CodeIgniter\Log\Exceptions\LogException;
use CodeIgniter\Log\Handlers\HandlerInterface;
use CodeIgniter\Log\Logger as LogLogger;

class Logger extends LogLogger
{
    protected $senderToken = '';
    protected $bugsCenter  = '';

    public function __construct() {
        parent::__construct(config(\Config\Logger::class));

        $tmSenderToken = getenv('project.telegram.senderToken') ?: (defined('TM_SENDER_TOKEN') ? TM_SENDER_TOKEN : '');
        $tmBugsCenter  = getenv('project.telegram.bugsCenter') ?: (defined('TM_BUGS_CENTER') ? TM_BUGS_CENTER : '');

        $this->senderToken = $tmSenderToken;
        $this->bugsCenter  = $tmBugsCenter;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     */
    public function log($level, $message, array $context = []): bool
    {
        if (is_numeric($level)) {
            $level = array_search((int) $level, $this->logLevels, true);
        }

        // Is the level a valid level?
        if (! array_key_exists($level, $this->logLevels)) {
            throw LogException::forInvalidLogLevel($level);
        }

        // Does the app want to log this right now?
        if (! in_array($level, $this->loggableLevels, true)) {
            return false;
        }

        // Parse our placeholders
        $message = $this->interpolate($message, $context);

        if ($this->cacheLogs) {
            $this->logCache[] = [
                'level' => $level,
                'msg'   => $message,
            ];
        }

        foreach ($this->handlerConfig as $className => $config) {
            if (! array_key_exists($className, $this->handlers)) {
                $this->handlers[$className] = new $className($config);
            }

            /**
             * @var HandlerInterface $handler
             */
            $handler = $this->handlers[$className];

            if (! $handler->canHandle($level)) {
                continue;
            }

            // If the handler returns false, then we
            // don't execute any other handlers.
            if (! $handler->setDateFormat($this->dateFormat)->handle($level, $message)) {
                break;
            }
        }

        // kirim notif ke Telegram
        $telegram = new Telegram($this->bugsCenter, $this->senderToken);
        $telegram->send(strtoupper($level) . ' in ' . ENVIRONMENT . " mode\nat " . getDomainName() . "\n```log\n" . $message . "\n```");
        
        return true;
    }
}
