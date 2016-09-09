<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Deer\QueueMonitoring;

interface QueueMonitoringInterface
{   
    /**
     * Initialize QueueMonitoring
     * 
     * @param string $server DSN-style redis://user:pass@host:port/db?option1=val1&option2=val2
     * @param mixed $database
     */
    public function init($server, $database = null);
    
    /**
     * Refresh
     */
    public function refresh();
    
    /**
     * Display monitoring information
     */
    public function display();
    
    /**
     * Parse a DSN string, which can have one of the following formats:
     *
     * - host:port
     * - redis://user:pass@host:port/db?option1=val1&option2=val2
     * - tcp://user:pass@host:port/db?option1=val1&option2=val2
     *
     * Note: the 'user' part of the DSN is not used.
     *
     * @param string $dsn A DSN string
     * @return array An array of DSN compotnents, with 'false' values for any unknown components. e.g.
     *               [host, port, db, user, pass, options]
     */
    public static function parseDsn($dsn);
}