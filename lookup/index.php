<?php

$fqdn = array();
$svc = null;
$info = null;
$discovery = null;

$fields = array(
	'kind' => null,
	'sid' => null,
	'suffix' => null,
	/* HD Radio */
	'tx' => null,
	'cc' => null,
	/* IP-delivered stream */
	'url' => null,
	/* Service-specific parameters */
	'bm:uris' => null,
	'bm:start' => null,
	'bm:duration' => null,
	'bm:time' => null,	
);

require_once(dirname(__FILE__) . '/common.php');
require_once(dirname(__FILE__) . '/ipstream.php');
require_once(dirname(__FILE__) . '/dvb.php');
require_once(dirname(__FILE__) . '/fm.php');
require_once(dirname(__FILE__) . '/dab.php');
require_once(dirname(__FILE__) . '/drm-amss.php');
require_once(dirname(__FILE__) . '/form.php');
require_once(dirname(__FILE__) . '/discovery.php');


switch($fields['kind'])
{
	case 'ip':
		$info = IPStream::processForm($fields);
		break;
	case 'dvb':
		$info = DVB::processForm($fields);
		break;
	case 'fm':
		$info = FM::processForm($fields);
		break;
	case 'drm':
	case 'amss':
		$info = DRM_AMSS::processForm($fields);
		break;
	case 'dab':
		$info = DAB::processForm($fields);
		break;
}

if(is_array($info))
{
	if(isset($info['fqdn'])) $fqdn = $info['fqdn'];
	if(isset($info['svc'])) $svc = $info['svc'];
}

if(is_array($fqdn))
{
	if(count($fqdn))
	{
		$fqdn = implode('.', $fqdn);
	}
	else
	{
		$fqdn = null;
	}
}

if(strlen($fqdn))
{
	$discovery = new Discovery($fqdn);
	$discovery->discover();
}

Form::prepareOutput();

require_once(dirname(__FILE__) . '/view.phtml');

function dumprecs($records)
{
	echo "\t\t\t" . '<p class="dns-records code-block">';
	foreach($records as $rr)
	{
		if(!isset($rr['type']))
		{
			continue;
		}
		echo "\t\t\t\t" . '<code class="dns-record">' . sprintf("%-40s %-6s %s %-8s\t", $rr['host'] . '.', $rr['ttl'], $rr['class'], $rr['type']);
		switch($rr['type'])
		{
			case 'A':
				echo $rr['ip'];
				break;
			case 'AAAA':
				echo $rr['ipv6'];
				break;
			case 'MX':
				echo $rr['pri'] . " " . $rr['target'];
				break;
			case 'CNAME':
			case 'NS':
			case 'DNAME':
			case 'PTR':
				echo $rr['target'] . '.';
				break;
			case 'TXT':
				echo '"' . addslashes($rr['txt']) . '"';
				break;
			case 'SRV':
				echo $rr['pri'] . " " . $rr['weight'] . " " . $rr['target'] . ". " . $rr['port'];
				break;
		}
		echo '</code><br>' . "\n";
	}
	echo "\t\t\t" . '</p>' . "\n";	
}

