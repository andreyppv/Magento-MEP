<?php

class   Flagbit_MEP_Model_Cron_CheckUrl
{

    protected $_checkedProfiles = array();

    protected $_threads = array();

    protected $_timeStart = 0;
    protected $_timeEnd = 0;

    public function run()
    {

        $this->_timeStart = microtime(true);

        $this->importFiles();

        $urls = Mage::getModel('mep/urls')->getCollection();
        $urls->addFieldToFilter('last_test_date', array('lt' => date('Y-m-d H:i:s', strtotime(Mage::getModel('core/date')->gmtDate() . ' -1 week'))));

        $index = 0;
        $limitProducts = 10;
        $maxThreads = 5;

        $size = $urls->getSize();

        Mage::log($size, null, 'urlchecker.log');

        while(true){
            $index++;
            $this->_threads[$index] = new Flagbit_MEP_Model_Thread( array($this, '_checkUrls') );
            $this->_threads[$index]->start($index, $limitProducts, $urls);

            while( count($this->_threads) >= $maxThreads ) {
                $this->_cleanUpThreads();
            }
            $this->_cleanUpThreads();

            if($index >= $size/$limitProducts){
                break;
            }
        }
        // wait for all the threads to finish
        while( !empty( $this->_threads ) ) {
            $this->_cleanUpThreads();
        }

        $this->_timeEnd = microtime(true);

        $this->updateJobs();
    }

    function importFiles() {
        $profiles = Mage::getModel('mep/profile')->getCollection();
        $profiles->addFieldToFilter('status', 1);

        foreach ($profiles as $profile) {
            $path = Mage::getConfig()->getOptions()->getBaseDir() . DS . $profile->getFilepath() . DS . 'url_to_check_' . $profile->getId() . '.csv';
            if (file_exists($path)) {
                rename($path, $path . '.lock');

                $urls = Mage::getModel('mep/urls')->getCollection();
                $urls->addFieldToFilter('profile', $profile->getId());
                foreach($urls as $url) {
                    try {
                        $url->delete();
                    } catch(Exception $e) {
                        Mage::log("Url #".$url->getId()." could not be removed: ".$e->getMessage(), null, 'urlchecker.log');
                    }
                }
                $csv = new Varien_File_Csv();
                $data = $csv->getData($path . '.lock');
                foreach($data as $row) {
                    $urls = Mage::getModel('mep/urls')->getCollection();
                    $urls->addFieldToFilter('url', $row[0]);
                    $urls->addFieldToFilter('type', $row[1]);
                    $urls->addFieldToFilter('profile', $profile->getId());
                    $urls->load();
                    $url = $urls->getFirstItem();
                    if (count($url->getData()) <= 0) {
                        $url->setUrl($row[0]);
                        $url->setProfile($profile->getId());
                        $url->setType($row[1]);
                        $url->setLast_test_date(date('Y-m-d H:i:s', 0));
                    }
                    $url->save();
                }

                unlink($path . '.lock');
            }
        }
    }

    function _checkUrls($offsetProducts, $limitProducts, $urls) {
        /**
         * IMPORTANT TO PREVENT MySql to go away
         */
        $core_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var Varien_Db_Adapter_Pdo_Mysql $core_read */
        $core_read->closeConnection();
        $core_read->getConnection();

        $urls->setPageSize($limitProducts)->setCurPage($offsetProducts);
        Mage::log($offsetProducts . ': ' . (string)$urls->load()->getSelect(), null, 'urlchecker.log');
        $str = '';
        foreach ($urls as $url) {
            $code = '510';
            $count = 0;
            do {
                $success = true;
                try {
                    $client = new Varien_Http_Client($url->getUrl());
                    $req = $client->request('GET');
                    if (!empty($req)) {
                        $code = $req->getStatus();
                    }
                } catch (Zend_Http_Client_Exception $e) {
                    if($count < 3) $success = false;
                    $count++;
                }
            } while (!$success);

            $url->setApache_code($code);
            $url->setLast_test_date(date('Y-m-d H:i:s'));
            if($code < 400 && $code > 0) {
                $url->setAvailable(1);
            }
            else {
                $url->setAvailable(0);
            }
            $url->save();
            $checkedProfiles = $this->_checkedProfiles;
            if(!in_array($url->getProfile(), $checkedProfiles)) $checkedProfiles[] = $url->getProfile();
            $this->_checkedProfiles = $checkedProfiles;

            $str .= $url->getId() . ' - ';
        }
        Mage::log($offsetProducts . ': ' . $str, null, 'urlchecker.log');
    }

    function updateJobs() {
        Mage::log($this->_checkedProfiles, null, 'urlchecker.log');
        foreach ($this->_checkedProfiles as $profile) {
            $urls = Mage::getModel('mep/urls')->getCollection();
            $urls->addFieldToFilter('available', 0);
            $urls->addFieldToFilter('profile', $profile);
            $job = Mage::getModel('mep/urls_jobs')->load($profile, 'profile');
            if (count($job->getData()) <= 0) {
                $job->setProfile($profile);
                $job->setBad($urls->count());
                $job->setExecuted(date('Y-m-d H:i:s'));
                $job->setTime(($this->_timeEnd - $this->_timeStart) * 1000);
            } else {
                $job->setBad($urls->count());
                $job->setExecuted(date('Y-m-d H:i:s'));
                $job->setTime(($this->_timeEnd - $this->_timeStart) * 1000);
            }
            $job->save();
        }
    }


    /**
     * clean up finished threads
     */
    protected function _cleanUpThreads()
    {
        foreach( $this->_threads as $index => $thread ) {
            if( ! $thread->isAlive() ) {
                unset( $this->_threads[$index] );
            }
        }
        // let the CPU do its work
        sleep( 1 );
    }

}