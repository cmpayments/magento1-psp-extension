<?php

/**
 * The purpose of this model is to lock and unlock based on a database table.
 * This can be useful when you do not want two processes to edit the same data at one moment in time.
 * A shutdown function is registered to make sure the lock is released when the PHP process crashes.
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Locking extends Mage_Core_Model_Abstract
{
    /* @var string requested locking code */
    protected $_lockCode = null;
    /* @var string identifier for current process */
    protected $_processCode = null;
    /* @var Varien_Db_Adapter_Pdo_Mysql */
    protected $_dbAdapter = null;
    /* @var string */
    protected $_lockTable = null;
    /* @var string */
    protected $_lockTableQ = null;
    /* @var string */
    protected $_lockInfo = '';
    /* @var Comaxx_CmPayments_Helper_Data */
    protected $_helper;

    /* @var integer */
    protected $_lockTimeoutSec = 50;     // Seconds. Can be set in config.xml
    /* @var float */
    protected $_tryWaitSec = 0.5;        // Seconds, can be < 1. Can be set in config.xml
    /* @var integer after the stealLock time a lock entry can be taken by another (used to fix locks that are not freed) */
    protected $_stealLockSec = 5000;     // Seconds. Can be set in config.xml
    /* @var integer */
    protected $_cleanupTimeSec = 500000; // Seconds. Can be set in config.xml
    /* @var bool */
    protected $_locked = false;
    /* @var bool */
    protected $_initOK = false;

    /**
     * Constructor, prepares everything for locking, but does not do the lock itself.
     *
     * @param $lock_code A string to identify what you want to lock, for example 'order_1223123'
     *
     * @return Comaxx_CmPayments_Model_Locking|false
     */
    public function __construct($lock_code)
    {
        $this->_helper = Mage::helper('cmpayments');
        if (empty($lock_code)) {
            $this->_helper->log('Empty lock code given', Zend_Log::WARN);

            return false;
        }

        $this->_lockCode    = $lock_code;
        $this->_processCode = uniqid(get_class(), true);

        // @var $oCoreResource Mage_Core_Model_Resource 
        $oCoreResource = Mage::getSingleton('core/resource');
        // prepare database access and info to use during locking
        $this->_dbAdapter  = $oCoreResource->getConnection('core_write');
        $this->_lockTable  = $oCoreResource->getTableName('cmpayments_lock');
        $this->_lockTableQ = $this->_dbAdapter->quoteIdentifier($this->_lockTable);
        $this->_lockInfo   = 'lock_code="' . $this->_lockCode . '" process_code="' . $this->_processCode . '"';

        //check if the table to update is present
        $tableExistsSql = $this->_dbAdapter->quoteInto("SHOW TABLE STATUS LIKE ?", $this->_lockTable);
        if (! $this->_dbAdapter->fetchRow($tableExistsSql)) {
            $this->_helper->log(
                'Lock table "' . $this->_lockTable . '" does not exist. Locking is not possible. ' . $this->_lockInfo,
                Zend_Log::ERR
            );

            return false;
        }

        //retrieve config values if present, otherwise use defaults
        $config_helper = Mage::helper('cmpayments/config');

        $config_val            = intval($config_helper->getItem('locking/lock_timeout_sec'));
        $this->_lockTimeoutSec = ( ! empty($config_val)) ? $config_val : $this->_lockTimeoutSec;

        $config_val        = floatval($config_helper->getItem('locking/try_wait_sec'));
        $this->_tryWaitSec = ( ! empty($config_val)) ? $config_val : $this->_tryWaitSec;

        $config_val          = intval($config_helper->getItem('locking/steal_loc_sec'));
        $this->_stealLockSec = ( ! empty($config_val)) ? $config_val : $this->_stealLockSec;

        $config_val            = intval($config_helper->getItem('locking/cleanup_time_sec'));
        $this->_cleanupTimeSec = ( ! empty($config_val)) ? $config_val : $this->_cleanupTimeSec;

        //register shutdown so it is called during shutdown
        register_shutdown_function(array($this, 'shutdown'));

        //register that initializing went well
        $this->_initOK = true;

        return $this;
    }

    /**
     * Do the actual lock.
     * This function is blocking until either the lock is acquired or 10 seconds have passed and no lock is acquired.
     *
     * @return bool True if successful
     */
    public function lock()
    {
        if (! $this->initCheck()) {
            return false;
        }

        //determine how many tries there are left
        $tries = ceil($this->_lockTimeoutSec / $this->_tryWaitSec);

        // If lock does not yet exist we can insert it.
        $result = $this->_insertLock();

        // If insert was not successful we will try to update a number of times
        while ($tries and ! $result) {
            $tries--;
            //attempt to take a lock that is freed or that has been inactive for a configured period
            $result = $this->_updateLock();
            if (! $result) {
                usleep(ceil($this->_tryWaitSec * 1000 * 1000));
            }
        }

        if ($result) {
            $this->_locked = true;
            $this->_helper->log('Lock acquired. ' . $this->_lockInfo, Zend_Log::DEBUG);
        } else {
            $this->_helper->log('Lock acquire failed. ' . $this->_lockInfo, Zend_Log::WARN);
        }

        return $result;
    }

    /**
     * Try to get the lock by insert. Succeeds if the lock record does not exist yet.
     *
     * @return bool True if lock acquired.
     */
    private function _insertLock()
    {
        if (! $this->initCheck()) {
            return false;
        }

        $nowTime = time();
        $this->_helper->log('Trying to get lock by insert. ' . $this->_lockInfo, Zend_Log::DEBUG);
        //try to insert new row into table
        $insertSQL    = 'INSERT IGNORE INTO ' . $this->_lockTableQ . ' ';
        $insertSQL    .= 'SET `lock_code`=?, `process_code`=?, `lock_time`=? ';
        $insertResult = $this->_dbAdapter->query(
            $insertSQL, array(
            $this->_lockCode,
            $this->_processCode,
            $nowTime,
            )
        );
        $result       = (1 == $insertResult->rowCount());

        return $result;
    }

    /**
     * Try to get the lock by update. Succeeds if the lock record existed but was free or older than StealLockSec.
     *
     * @return bool True if lock acquired.
     */
    private function _updateLock()
    {
        if (! $this->initCheck()) {
            return false;
        }

        $nowTime = time();
        //determine steal lock time, if entry is old enough the row can be taken over by current process
        $iStealLockTime = $nowTime - $this->_stealLockSec;
        $this->_helper->log('Trying to get lock by update. ' . $this->_lockInfo, Zend_Log::DEBUG);
        $lockSQL      = 'UPDATE ' . $this->_lockTableQ . ' SET `process_code`=?, lock_time=? ';
        $lockSQL      .= 'WHERE `lock_code`=? ';
        $lockSQL      .= 'AND ( `process_code` IS NULL OR `lock_time` < ? ) ';
        $lockSQL      .= 'LIMIT 1';
        $updateResult = $this->_dbAdapter->query(
            $lockSQL, array(
            $this->_processCode,
            $nowTime,
            $this->_lockCode,
            $iStealLockTime,
            )
        );
        $result       = (1 == $updateResult->rowCount());

        return $result;
    }

    /**
     * Unlock the lock
     *
     * @return bool True if successful.
     */
    public function unlock()
    {
        if (! $this->initCheck()) {
            return false;
        }

        $this->_helper->log('Trying to free lock. ' . $this->_lockInfo, Zend_Log::DEBUG);
        //release lock by clearing the process code field
        $unlockSQL    = 'UPDATE ' . $this->_lockTableQ . ' SET `process_code`=NULL ';
        $unlockSQL    .= 'WHERE `lock_code` = ? ';
        $unlockSQL    .= 'AND `process_code` = ? ';
        $unlockSQL    .= 'LIMIT 1';
        $unlockResult = $this->_dbAdapter->query($unlockSQL, array($this->_lockCode, $this->_processCode));
        $result       = (1 == $unlockResult->rowCount());
        if ($result) {
            $this->_locked = false;
            $this->_helper->log('Lock freed. ' . $this->_lockInfo, Zend_Log::DEBUG);
        } else {
            $this->_helper->log('Freeing lock failed. ' . $this->_lockInfo, Zend_Log::WARN);
        }

        // Randomly on on 100 hits we clean up the lock table
        // When there are more orders the number of cleanups scales automatically.
        // Prevents flooding the lock table without depending on cronjobs and without a constant cleanup load
        if (77 == rand(1, 100)) {
            $this->_maintenance();
        }

        return $result;
    }

    /**
     * Get the Lock code set when creating the instance of this class.
     *
     * @return string|null
     */
    public function getLockCode()
    {
        if (! $this->initCheck()) {
            return false;
        }

        return $this->_lockCode;
    }

    /**
     * Cleanup old lock records.
     * We only clean up unlocked records, because if there are "hanging" locks
     * we want to be able to see it afterwards.
     *
     * @return bool|int Number of removed rows
     */
    private function _maintenance()
    {
        if (! $this->initCheck()) {
            return false;
        }

        $cleanupTime = time() - $this->_cleanupTimeSec;
        $this->_helper->log('Performing maintenance, removing locks older than ' . $cleanupTime, Zend_Log::DEBUG);
        //clean entries that are freed (have no processcode) or are inactive for configured time
        $cleanupSQL    = 'DELETE FROM ' . $this->_lockTableQ . ' ';
        $cleanupSQL    .= 'WHERE `lock_time` < ? ';
        $cleanupSQL    .= 'AND process_code IS NULL ';
        $cleanupResult = $this->_dbAdapter->query($cleanupSQL, $cleanupTime);
        $result        = $cleanupResult->rowCount();
        $this->_helper->log('Maintenance performed, ' . $result . ' rows removed.', Zend_Log::DEBUG);

        return $result;
    }

    public function shutdown()
    {
        if (! $this->initCheck()) {
            return false;
        }

        if ($this->_locked) {
            $this->_helper->log('Still locked on shutdown, so unlocking. ' . $this->_lockInfo, Zend_Log::WARN);
            $this->unlock();
        }
    }

    /**
     * Function to check if the Lock object was able to initialize successful
     * during the construction.
     *
     * @return bool
     */
    public function initCheck()
    {
        if (! $this->_initOK) {
            $this->_helper->log(
                'Object is not initialed correctly, no locking possible. ' . $this->_lockInfo,
                Zend_Log::ERR
            );
        }

        return $this->_initOK;
    }

    /**
     * Create a lock object, and try to get lock.
     * This function blocks until the lock is aquired.
     *
     * @param string $orderNr the order increment id
     *
     * @return Comaxx_CmPayments_Model_Locking|False Returns locking object if lock is acquired otherwise False
     */
    public static function getLock($lockName)
    {
        $lockInfo = 'pid [' . getmypid() . '] lock [' . $lockName . ']';
        $helper   = Mage::helper('cmpayments');

        $helper->log('Creating lock object. ' . $lockInfo);
        $locking = Mage::getModel('cmpayments/locking', $lockName);

        //check if locking model successfully initialized
        if ($locking && $locking instanceof Comaxx_CmPayments_Model_Locking && $locking->initCheck()) {
            $helper->log('Trying to lock object. ' . $lockInfo);
            // Get the actual lock, this is blocking until lock is available.
            if (! $locking->lock()) {
                //cant get lock within timeout range
                $helper->log('Acquiring lock unsuccessful. ' . $lockInfo, Zend_Log::ERR);
                $locking = false;
            } else {
                //lock acquired
                $helper->log('Lock acquired. ' . $lockInfo);
            }
        } else {
            $helper->log('Creating lock object failed. ' . $lockInfo, Zend_Log::WARN);
            $locking = false;
        }

        return $locking;
    }

    /**
     * Release a lock object.
     *
     * @param Comaxx_CmPayments_Model_Locking $lock Locking object
     *
     * @return void
     */
    public static function releaseLock(Comaxx_CmPayments_Model_Locking $lock)
    {
        $helper = Mage::helper('cmpayments');

        // Release lock
        if ($lock && $lock instanceof Comaxx_CmPayments_Model_Locking && $lock->initCheck()) {
            $lockInfo = 'pid [' . getmypid() . '] lock [' . $lock->getLockCode() . ']';
            $helper->log('Trying to unlock object. ' . $lockInfo);
            $lock->unlock();
            $helper->log('Unlock performed. ' . $lockInfo);
        } else {
            $lockInfo = 'pid [' . getmypid() . ']';
            $helper->log('No lock, so no unlocking needed. ' . $lockInfo);
        }
    }
}