<?php
class WpOGP {
	function __construct($admins, $defaultImgUrl, $descSize=100) {
		//FacebookユーザＩＤ
		$this->admins = $admins;
		//デフォルトの画像URL（トップページやアーカイブページで使用）
		$this->defaultImgUrl = $defaultImgUrl;
		//概要説明のサイズ
		$this->descSize = $descSize;
	}
	function get(){
		global $post;
		//サイト情報を設定
		$desc = get_bloginfo('description');
		$title = get_bloginfo('name');
		$url = get_bloginfo('url');
		//個別ページの場合、個別の記事情報を設定
		if (is_singular()){
			$desc = strip_tags($post->post_excerpt ? $post->post_excerpt : $post->post_content);
			$desc = mb_substr($desc, 0, $this->descSize);
			$title = $post->post_title;
			$url = get_permalink($post->ID);
		}
		//表示画像をカスタムフィールド、アイキャッチ、本文画像の優先順位で決定
		$imgUrl = $this->defaultImgUrl;
		if (is_singular()){
			$eyecatch = post_custom('eyecatch');
			if($eyecatch != ''){
				$imgUrl = $eyecatch;
			}
			else
			if (has_post_thumbnail()){
				$image = wp_get_attachment_image_src(
					get_post_thumbnail_id(),
					'full'
				);
				$imgUrl = $image[0];
			}
			else
			if ( preg_match( '/<img.*?src=(["\'])(.+?)\1.*?>/i',
					$post->post_content, $matchText ) && !is_archive()) {
				$imgUrl = $matchText[2];
			}
		}
		//ピカサの画像の場合は画像サイズを800pxに設定
		if(preg_match( '/\.googleusercontent\./i', $imgUrl)){
			$imgUrl = str_replace('/s144/', '/s800/', $imgUrl);
			$imgUrl = str_replace('/s288/', '/s800/', $imgUrl);
		}
		//マークアップを返す
		ob_start();
		$args = array(
			'fb:admins' => $this->admins,
			'og:locale' => 'ja_JP',
			'og:type' => 'blog',
			'og:site_name' => esc_attr(get_bloginfo('name')),
			'og:description' => esc_attr($desc),
			'og:title' => esc_attr($title),
			'og:url' => esc_url($url),
			'og:image' => esc_url($imgUrl)
		);
		foreach($args as $key => $value){
			printf('<meta name="%1$s" content="%2$s" />'."\n", $key, $value);
		}
		return ob_get_clean();
	}
}
?>
