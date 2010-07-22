<?php

require_once(dirname(__FILE__) . '/channel.php');
require_once(dirname(__FILE__) . '/dvb/data.php');

class DVBChannel extends Channel
{
	public $original_network_id;
	public $transport_stream_id;
	public $service_id;
	public $network_id;
	public $transport_stream_name;
	
	public function __construct($onid, $tsid, $sid, $nid)
	{
		$this->kind = 'dvb';
		$this->original_network_id = $onid;
		$this->transport_stream_id = $tsid;
		$this->service_id = $sid;
		$this->nid = $nid;
		$this->rdns = RadioDNS::initWithDVB($onid, $tsid, $sid, $nid);
		$this->addSource(sprintf('dvb://%04x.%04x.%04x', $onid, $tsid, $sid), 'video/MP2T', 'self');		
		$this->uri[] = 'dns:' . $this->rdns->fqdn;
		$this->lookupURL = sprintf('/lookup/?kind=dvb&original_network_id=%04x&transport_stream_id=%04x&service_id=%04x&network_id=%04x', $onid, $tsid, $sid, $nid);
		$this->hasOtaEpg = true;
		parent::__construct();
	}
}

abstract class DVB
{
	public static function addChannelsFromSource(&$listing, $onid, $nid, $kind = 'dvb')
	{
		global $platform;
		
		foreach($platform[$kind]['onid'][$onid]['nid'][$nid]['tsid'] as $tsid => $ts)
		{
			foreach($ts['sid'] as $sid => $channel)
			{
				$chan = new DVBChannel(intval(strval($onid), 16), intval(strval($tsid), 16), intval(strval($sid), 16), intval(strval($nid), 16));
				$chan->available = true;
				$chan->callsign = $chan->name = $channel['name'];
				$chan->lcn = $channel['lcn'];
				if($channel['data'])
				{
					$chan->data = true;
					$chan->serviceClass = 'interactive';
				}
				if($channel['audio'])
				{
					$chan->audio = true;
				}
				$chan->transport_stream_name = $ts['name'];
				$listing->addChannel($chan);
			}		
		}
	}
}
