<?php

namespace IPS\banlyapi;

class _Application extends \IPS\Application
{
	protected function get__icon()
	{
		return 'shield';
	}

	public function acpMenu()
	{
		static::syncWidgetRow();
		$menu = parent::acpMenu();

		if ( \IPS\Dispatcher::hasInstance() AND \IPS\Dispatcher::i() instanceof \IPS\Dispatcher\Admin )
		{
			$marker = '/* banly-acp-icon */';
			if ( \mb_strpos( (string) \IPS\Output::i()->headCss, $marker ) === FALSE )
			{
				$file = \IPS\ROOT_PATH . '/applications/' . $this->directory . '/interface/favicon.png';
				if ( is_file( $file ) )
				{
					$icon = 'data:image/png;base64,' . base64_encode( (string) file_get_contents( $file ) );
					\IPS\Output::i()->headCss .= $marker . '[data-tab="tab_banlyapi"] .acpAppList_icon i{display:none!important}[data-tab="tab_banlyapi"] .acpAppList_icon{background:transparent url("' . $icon . '") center/28px 28px no-repeat;width:35px;height:35px;display:inline-block}';
				}
			}
		}

		return $menu;
	}

	public function install()
	{
		parent::install();
		static::syncWidgetRow();
	}

	public static function syncWidgetRow()
	{
		$where = array( 'app=? AND `key`=?', 'banlyapi', 'banCheck' );
		$base = array(
			'app' => 'banlyapi',
			'key' => 'banCheck',
			'class' => 'IPS\\banlyapi\\widgets\\banCheck',
			'restrict' => json_encode( array( 'sidebar', 'cms' ) ),
			'default_area' => 'sidebar',
			'allow_reuse' => 1,
			'menu_style' => 'menu',
			'embeddable' => 1,
		);

		foreach ( array( $base + array( 'plugin' => NULL ), $base ) as $data )
		{
			try
			{
				$count = 0;
				try
				{
					$count = (int) \IPS\Db::i()->select( 'COUNT(*)', 'core_widgets', $where )->first();
				}
				catch ( \Exception $e ) {}

				if ( $count )
				{
					\IPS\Db::i()->update( 'core_widgets', $data, $where );
				}
				else
				{
					\IPS\Db::i()->insert( 'core_widgets', $data );
				}

				try
				{
					unset( \IPS\Data\Store::i()->widgets );
				}
				catch ( \Exception $e ) {}

				return;
			}
			catch ( \Exception $e ) {}
		}
	}
}
