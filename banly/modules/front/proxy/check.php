<?php

namespace IPS\banlyapi\modules\front\proxy;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _check extends \IPS\Dispatcher\Controller
{
	protected function manage()
	{
		try
		{
			\IPS\Session::i()->csrfCheck();

			$q = '';
			if ( isset( \IPS\Request::i()->q ) )
			{
				$q = trim( (string) \IPS\Request::i()->q );
			}

			$result = \IPS\banlyapi\ApiClient::summary( $q );
		}
		catch ( \Throwable $e )
		{
			\IPS\Output::i()->json( array(
				'ok' => false,
				'error' => 'upstream',
				'message' => 'Nie udało się sprawdzić. Spróbuj później.',
			) );
			return;
		}

		if ( empty( $result['ok'] ) )
		{
			$error = isset( $result['error'] ) ? (string) $result['error'] : 'error';
			\IPS\Output::i()->json( array(
				'ok' => false,
				'error' => $error,
				'message' => $this->publicErrorMessage( $error ),
			) );
			return;
		}

		\IPS\Output::i()->json( array(
			'ok' => true,
			'q' => $result['q'],
			'personaName' => isset( $result['personaName'] ) ? (string) $result['personaName'] : '',
			'avatarUrl' => isset( $result['avatarUrl'] ) ? (string) $result['avatarUrl'] : '',
			'activeNetworks' => (int) $result['activeNetworks'],
			'detailsUrl' => $result['detailsUrl'],
		) );
	}

	protected function publicErrorMessage( $error )
	{
		switch ( $error )
		{
			case 'empty':
				return 'Podaj SteamID.';
			case 'config':
				return 'Widget Banly nie jest skonfigurowany (brak klucza API).';
			case 'upstream':
				return 'Nie udało się sprawdzić. Spróbuj później.';
			default:
				return $error !== '' ? $error : 'Nie udało się sprawdzić. Spróbuj później.';
		}
	}
}
