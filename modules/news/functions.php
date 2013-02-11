<?php

/**
 * @Project NUKEVIET 3.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2012 VINADES.,JSC. All rights reserved
 * @createdate 12/31/2009 0:51
 */
if( ! defined( 'NV_SYSTEM' ) ) die( 'Stop!!!' );
if( ! in_array( $op, array( 'viewcat', 'detail' ) ) )
{
	define( 'NV_IS_MOD_NEWS', true );
}
require_once ( NV_ROOTDIR . "/modules/" . $module_file . "/global.functions.php" );

global $global_array_cat;
$global_array_cat = array();
$link_i = NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=Other";
$global_array_cat[0] = array(
	"catid" => 0,
	"parentid" => 0,
	"title" => "Other",
	"titlesite" => "",
	"alias" => "Other",
	"link" => $link_i,
	"viewcat" => "viewcat_page_new",
	"subcatid" => 0,
	"numlinks" => 3,
	"description" => "",
	"inhome" => 0,
	"keywords" => ""
);
$catid = 0;
$parentid = 0;
$alias_cat_url = isset( $array_op[0] ) ? $array_op[0] : "";
$array_mod_title = array();

$sql = "SELECT catid, parentid, title, titlesite, alias, viewcat, subcatid, numlinks, description, inhome, keywords, who_view, groups_view FROM `" . NV_PREFIXLANG . "_" . $module_data . "_cat` ORDER BY `order` ASC";
$list = nv_db_cache( $sql, 'catid', $module_name );
foreach( $list as $l )
{
	$l['alias'] = $db->unfixdb( $l['alias'] );
	$global_array_cat[$l['catid']] = $l;
	$global_array_cat[$l['catid']]['link'] = NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $l['alias'];
	if( $alias_cat_url == $l['alias'] )
	{
		$catid = $l['catid'];
		$parentid = $l['parentid'];
	}
}

//Xac dinh RSS
if( $module_info['rss'] )
{
	$rss[] = array( //
		'title' => $module_info['custom_title'], //
		'src' => NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=rss" //
	);
}

foreach( $global_array_cat as $catid_i => $array_cat_i )
{
	if( $catid_i > 0 and $array_cat_i['parentid'] == 0 )
	{
		$act = 0;
		$submenu = array();
		if( $catid_i == $catid or $catid_i == $parentid )
		{
			$act = 1;
			if( ! empty( $global_array_cat[$catid_i]['subcatid'] ) )
			{
				$array_catid = explode( ",", $global_array_cat[$catid_i]['subcatid'] );
				foreach( $array_catid as $sub_catid_i )
				{
					$array_sub_cat_i = $global_array_cat[$sub_catid_i];
					$sub_act = 0;
					if( $sub_catid_i == $catid )
					{
						$sub_act = 1;
					}
					$submenu[] = array( $array_sub_cat_i['title'], $array_sub_cat_i['link'], $sub_act );
				}
			}
		}
		$nv_vertical_menu[] = array( $array_cat_i['title'], $array_cat_i['link'], $act, 'submenu' => $submenu );
	}

	//Xac dinh RSS
	if( $catid_i and $module_info['rss'] )
	{
		$rss[] = array( //
			'title' => $module_info['custom_title'] . ' - ' . $array_cat_i['title'], //
			'src' => NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=rss/" . $array_cat_i['alias'] //
		);
	}
}
unset( $result, $catid_i, $parentid_i, $title_i, $alias_i );

$module_info['submenu'] = 0;

$page = 1;
$per_page_comment = 5;
$per_page = $module_config[$module_name]['per_page'];
$st_links = $module_config[$module_name]['st_links'];
$count_op = sizeof( $array_op );
if( ! empty( $array_op ) and $op == "main" )
{
	if( $catid == 0 )
	{
		$contents = $lang_module['nocatpage'] . $array_op[0];
		if( isset( $array_op[1] ) and substr( $array_op[1], 0, 5 ) == "page-" )
		{
			$page = intval( substr( $array_op[1], 5 ) );
		}
		elseif( ! empty( $alias_cat_url ) )
		{
			$redirect = "<meta http-equiv=\"Refresh\" content=\"3;URL=" . nv_url_rewrite( NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name, true ) . "\" />";
			nv_info_die( $lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content'] . $redirect );
		}
	}
	else
	{
		$op = "main";
		if( $count_op == 1 or substr( $array_op[1], 0, 5 ) == "page-" )
		{
			$op = "viewcat";
			if( $count_op > 1 )
			{
				$page = intval( substr( $array_op[1], 5 ) );
			}
		}
		elseif( $count_op == 2 )
		{
			$array_page = explode( "-", $array_op[1] );
			$id = intval( end( $array_page ) );
			$number = strlen( $id ) + 1;
			$alias_url = substr( $array_op[1], 0, -$number );
			if( $id > 0 and $alias_url != "" )
			{
				$op = "detail";
			}
		}
		$parentid = $catid;
		while( $parentid > 0 )
		{
			$array_cat_i = $global_array_cat[$parentid];
			$array_mod_title[] = array(
				'catid' => $parentid,
				'title' => $array_cat_i['title'],
				'link' => $array_cat_i['link']
			);
			$parentid = $array_cat_i['parentid'];
		}
		sort( $array_mod_title, SORT_NUMERIC );
	}
}

?>