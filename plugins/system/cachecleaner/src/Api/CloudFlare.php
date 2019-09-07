<?php

/*
 * Library for the KeyCDN API
 *
 * @author Tobias Moser
 * @version 0.1
 *
 */

class CloudFlare
{
	public $email;
	public $auth_key;
	public $api = 'https://api.cloudflare.com/client/v4';

	public function __construct($email, $auth_key)
	{
		$this->email    = $email;
		$this->auth_key = $auth_key;
	}

	public function purge($zone)
	{
		$zone_id = $this->getZoneId($zone);
		if ( ! $zone_id)
		{
			return 'CloudFlare-Error: could not find Zone ID for Zone: ' . $zone;
		}

		$data = [
			'purge_everything' => true,
		];

		return $this->getResponse(
			'zones/' . $zone_id . '/purge_cache',
			$data
		);
	}

	public function getZoneId($zone_name)
	{
		$zones = $this->getZones();

		foreach ($zones as $zone)
		{
			if ($zone->name == $zone_name)
			{
				return $zone->id;
			}
		}

		return false;
	}

	public function getZones()
	{
		$response = json_decode($this->getResponse('zones'));

		return !empty($response->result) ? $response->result : [];
	}

	private function getResponse($task, $data = [])
	{
		$url = $this->api . '/' . $task;

		$headers = [
			'X-Auth-Email: ' . $this->email,
			'X-Auth-Key: ' . $this->auth_key,
			'User-Agent: ' . __FILE__,
			'Content-type: application/json',
		];

		// start with curl and prepare accordingly
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		if ( ! empty($data))
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_TIMEOUT, 60);

		$json_output = curl_exec($ch);
		$curl_error  = curl_error($ch);

		curl_close($ch);

		if ( ! empty($curl_error) || empty($json_output))
		{
			return 'CloudFlare-Error: ' . $curl_error . ', Output: ' . $json_output;
		}

		return $json_output;
	}
}
