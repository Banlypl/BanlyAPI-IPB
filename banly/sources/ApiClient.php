<?php

namespace IPS\banlyapi;

class _ApiClient
{
	public static function summary( $query )
	{
		$query = trim( (string) $query );
		if ( $query === '' )
		{
			return array( 'ok' => false, 'error' => 'empty' );
		}

		$key = trim( (string) \IPS\Settings::i()->banly_api_key );
		if ( $key === '' )
		{
			return array( 'ok' => false, 'error' => 'config' );
		}

		$url = 'https://banly.pl/api/v2/widget?' . http_build_query( array( 'q' => $query ) );
		$fetched = static::fetchJson( $url, $key );
		if ( $fetched === NULL )
		{
			return array( 'ok' => false, 'error' => 'upstream' );
		}

		$httpStatus = (int) $fetched['status'];
		$data = $fetched['data'];
		if ( !is_array( $data ) )
		{
			return array( 'ok' => false, 'error' => 'upstream' );
		}

		if ( $httpStatus >= 400 )
		{
			$message = '';
			if ( isset( $data['error'] ) && is_string( $data['error'] ) )
			{
				$message = $data['error'];
			}
			elseif ( isset( $data['detail'] ) && is_string( $data['detail'] ) )
			{
				$message = $data['detail'];
			}
			return array(
				'ok' => false,
				'error' => $message !== '' ? $message : 'upstream',
			);
		}

		return static::summarizePayload( $data, $query );
	}

	/** Ciche curl, bez IPS Http (bez system logow). */
	protected static function fetchJson( $url, $key )
	{
		if ( !\function_exists( 'curl_init' ) )
		{
			return NULL;
		}

		$ch = curl_init( $url );
		if ( $ch === FALSE )
		{
			return NULL;
		}

		curl_setopt_array( $ch, array(
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_CONNECTTIMEOUT => 8,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_HTTPHEADER => array(
				'Accept: application/json',
				'X-Banly-Plugin-Key: ' . $key,
			),
			CURLOPT_USERAGENT => 'Banly-IPS-Widget/1.0.6',
		) );

		$raw = curl_exec( $ch );
		$status = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$errno = curl_errno( $ch );
		curl_close( $ch );

		if ( $errno || $raw === FALSE || $raw === '' )
		{
			return NULL;
		}

		$data = json_decode( (string) $raw, TRUE );
		return array(
			'status' => $status > 0 ? $status : 0,
			'data' => $data,
		);
	}

	public static function summarizePayload( array $data, $fallbackQuery )
	{
		$activeNetworks = 0;
		$networks = ( isset( $data['networks'] ) && is_array( $data['networks'] ) ) ? $data['networks'] : array();
		foreach ( $networks as $row )
		{
			if ( !is_array( $row ) )
			{
				continue;
			}
			$hint = isset( $row['unavailableHint'] ) ? trim( (string) $row['unavailableHint'] ) : '';
			if ( $hint !== '' || !array_key_exists( 'activeBanCount', $row ) || $row['activeBanCount'] === NULL )
			{
				continue;
			}
			if ( (int) $row['activeBanCount'] > 0 )
			{
				$activeNetworks++;
			}
		}

		$profile = ( isset( $data['steamProfile'] ) && is_array( $data['steamProfile'] ) ) ? $data['steamProfile'] : array();
		$steamId64 = NULL;
		if ( isset( $profile['steamId64'] ) && preg_match( '/^\d{17,20}$/', (string) $profile['steamId64'] ) )
		{
			$steamId64 = (string) $profile['steamId64'];
		}

		$personaName = '';
		if ( isset( $profile['personaName'] ) && is_string( $profile['personaName'] ) )
		{
			$personaName = trim( $profile['personaName'] );
		}
		$avatarUrl = '';
		if ( isset( $profile['avatarUrl'] ) && is_string( $profile['avatarUrl'] ) && preg_match( '#^https://#i', trim( $profile['avatarUrl'] ) ) )
		{
			$avatarUrl = trim( $profile['avatarUrl'] );
		}

		$canonical = (string) $fallbackQuery;
		if ( isset( $profile['steamId'] ) && is_string( $profile['steamId'] ) && trim( $profile['steamId'] ) !== '' )
		{
			$canonical = trim( $profile['steamId'] );
		}
		elseif ( $steamId64 !== NULL )
		{
			$canonical = $steamId64;
		}

		$detailsUrl = 'https://banly.pl/';
		if ( isset( $data['detailsUrl'] ) && is_string( $data['detailsUrl'] ) )
		{
			$candidate = trim( $data['detailsUrl'] );
			if ( preg_match( '#^https://(?:www\.)?banly\.pl/s/[A-Za-z0-9_-]{8,64}$#', $candidate ) )
			{
				$detailsUrl = $candidate;
			}
		}

		$out = array(
			'ok' => true,
			'q' => $canonical,
			'personaName' => $personaName,
			'avatarUrl' => $avatarUrl,
			'activeNetworks' => $activeNetworks,
			'detailsUrl' => $detailsUrl,
		);
		if ( isset( $data['ecdScan'] ) && is_array( $data['ecdScan'] ) )
		{
			$out['ecdScan'] = $data['ecdScan'];
		}
		return $out;
	}
}
