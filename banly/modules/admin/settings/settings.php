<?php

namespace IPS\banlyapi\modules\admin\settings;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _settings extends \IPS\Dispatcher\Controller
{
	protected function manage()
	{
		\IPS\banlyapi\Application::syncWidgetRow();

		$form = new \IPS\Helpers\Form( 'banly_settings' );
		$form->addHeader( 'banly_settings_header' );
		$form->add( new \IPS\Helpers\Form\Text( 'banly_api_key', \IPS\Settings::i()->banly_api_key, TRUE, array(
			'placeholder' => 'X-Banly-Plugin-Key',
		) ) );

		if ( $values = $form->values() )
		{
			$form->saveAsSettings( $values );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=banlyapi&module=settings&controller=settings' ), 'saved' );
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'banly_settings_title' );
		\IPS\Output::i()->output = (string) $form;
	}
}
