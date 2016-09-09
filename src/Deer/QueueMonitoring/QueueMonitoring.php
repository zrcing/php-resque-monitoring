<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Deer\QueueMonitoring;

class QueueMonitoring implements QueueMonitoringInterface
{
    /**
     * @var Credis_Client
     */
    protected $redis;
    
    protected $redisInfo;
    
    protected $data = [];
    
    public function init($server, $database = null, $timeout = null)
    {
        list($host, $port, , , $password) = self::parseDsn($server);
        $database = is_null($database)? 0: $database;
        $this->redis = new \Credis_Client($host, $port, $timeout, '', $database, $password);
    }
    
    public function refresh()
    {
        $this->redisInfo = $this->redis->info();
        
        $a = $this->redis->smembers('resque:queues');
        $this->data['redis_version'] = $this->redisInfo['redis_version'];
        $this->data['connected_clients'] = $this->redisInfo['connected_clients'];
        $this->data['resque:stat:processed'] = $this->redis->get('resque:stat:processed');
        $this->data['resque:queues'] = $this->redis->smembers('resque:queues');
        $this->data['resque:workers'] = $this->redis->smembers('resque:workers');
        $this->data['resque:queue:length'] = [];
        
        foreach ($this->data['resque:queues'] as $queue) {
            $this->data['resque:queue:length'][$queue] = $this->redis->llen('resque:queue:' . $queue);
        }
        
    }
    
    public function display()
    {
        $this->refresh();
        
        foreach ($this->data as $k => $v) {
            echo $k . ' : ';
            var_export($v);
            echo " <br /><br />\n";
        }
    }
    
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
    public static function parseDsn($dsn)
    {
        $parts = parse_url($dsn);
    
        // Check the URI scheme
        $validSchemes = array('redis', 'tcp');
        if (isset($parts['scheme']) && ! in_array($parts['scheme'], $validSchemes)) {
            throw new \InvalidArgumentException("Invalid DSN. Supported schemes are " . implode(', ', $validSchemes));
        }
    
        // Allow simple 'hostname' format, which `parse_url` treats as a path, not host.
        if ( ! isset($parts['host']) && isset($parts['path'])) {
            $parts['host'] = $parts['path'];
            unset($parts['path']);
        }
    
        // Extract the port number as an integer
        $port = isset($parts['port']) ? intval($parts['port']) : self::DEFAULT_PORT;
    
        // Get the database from the 'path' part of the URI
        $database = false;
        if (isset($parts['path'])) {
            // Strip non-digit chars from path
            $database = intval(preg_replace('/[^0-9]/', '', $parts['path']));
        }
    
        // Extract any 'user' and 'pass' values
        $user = isset($parts['user']) ? $parts['user'] : false;
        $pass = isset($parts['pass']) ? $parts['pass'] : false;
    
        // Convert the query string into an associative array
        $options = array();
        if (isset($parts['query'])) {
            // Parse the query string into an array
            parse_str($parts['query'], $options);
        }
    
        return array(
            $parts['host'],
            $port,
            $database,
            $user,
            $pass,
            $options,
        );
    }
}