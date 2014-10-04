<?php
class WpOGP {

	private $defaults = array(
		'fb:admins' => '',
		'og:locale' => 'ja_JP',
		'og:site_name' => '',
		'og:image' => '',
		'og:type' => 'blog',
		'og:description' => '',
		'og:title' => '',
		'og:url' => '',
		'defaultImage' => '',
		'descriptionSize' => 100
	);
	
	private $params;

	function extend($a, $b) {
		$r = array();
		foreach($a as $k=>$v) {
			$r[$k] = $v;
		}
		foreach($b as $k=>$v) {
			$r[$k] = $v;
		}
		return $r;
	}

	function __construct($args) {
		global $post;
		$args = $this->extend($this->defaults, $args);
		//サイト情報を設定
		$args['og:site_name'] = get_bloginfo('name');
		$args['og:description'] = get_bloginfo('description');
		$args['og:title'] = get_bloginfo('name');
		$args['og:url'] = get_bloginfo('url');
		//個別ページの場合、個別の記事情報を設定
		if (is_singular()){
			$args['og:description'] = mb_substr(strip_tags($post->post_excerpt ? $post->post_excerpt : $post->post_content), 0, $args['descriptionSize']);
			$args['og:title'] = $post->post_title;
			$args['og:url'] = get_permalink($post->ID);
		}
		//表示画像をカスタムフィールド、アイキャッチ、本文画像の優先順位で決定
		$args['og:image'] = $args['defaultImage'];
		if (is_singular()){
			$eyecatch = post_custom('eyecatch');
			if($eyecatch != ''){
				$args['og:image'] = $eyecatch;
			}
			else
			if (has_post_thumbnail()){
				$image = wp_get_attachment_image_src(
					get_post_thumbnail_id(),
					'full'
				);
				$args['og:image'] = $image[0];
			}
			else
			if ( preg_match( '/<img.*?src=(["\'])(.+?)\1.*?>/i',
					$post->post_content, $matchText ) && !is_archive()) {
				$args['og:image'] = $matchText[2];
			}
		}
		//ピカサの画像の場合は画像サイズを800pxに設定
		if(preg_match( '/\.googleusercontent\./i', $args['og:image'])){
			$args['og:image'] = str_replace('/s144/', '/s800/', $args['og:image']);
			$args['og:image'] = str_replace('/s288/', '/s800/', $args['og:image']);
		}
		$this->params = $args;
	}

	function getMeta($args=array()){
		global $post;
		$args = $this->extend($this->params, $args);
		$args['og:site_name'] = esc_attr($args['og:site_name']);
		$args['og:description'] = esc_attr($args['og:description']);
		$args['og:title'] = esc_attr($args['og:title']);
		$args['og:url'] = esc_url($args['og:url']);
		$args['og:image'] = esc_url($args['og:image']);
		//マークアップを返す
		ob_start();
		foreach($args as $key => $value){
			if(strpos($key, ':')){
				printf('<meta name="%1$s" content="%2$s" />'."\n", $key, $value);
			}
		}
		return ob_get_clean();
	}
}
?>
