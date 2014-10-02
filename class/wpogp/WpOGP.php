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
		//投稿ページの場合、個別の記事情報を設定
		if (is_single() or is_page()){
			if(have_posts()): while(have_posts()): the_post();
				$desc = mb_substr(get_the_excerpt(), 0, $this->descSize);
			endwhile; endif;
			$title = get_the_title();
			$url = get_permalink();
		}
		//投稿ページ以外の場合、サイト情報を設定
		else{
			$desc = get_bloginfo('description');
			$title = get_bloginfo('name');
			$url = get_bloginfo('url');
		}
		//表示画像をカスタムフィールド、アイキャッチ、本文画像の優先順位で決定
		$imgUrl = $this->defaultImgUrl;
		if (is_single() or is_page()){
			$eyecatch = post_custom('eyecatch');
			if($eyecatch != ''){
				$imgUrl = $eyecatch;
			}
			else
			if (has_post_thumbnail()){
				$image_id = get_post_thumbnail_id();
				$image = wp_get_attachment_image_src( $image_id, 'full');
				$imgUrl = $image[0];
			}
			else
			if ( preg_match( '/<img.*?src=(["\'])(.+?)\1.*?>/i',
					$post->post_content, $matchText ) && !is_archive()) {
				$imgUrl = $matchText[2];
			}
		}
		//ピカサの画像の場合は画像サイズを400px以上に設定
		if(preg_match( '/\.googleusercontent\./i', $imgUrl)){
			$imgUrl = str_replace('/s144/', '/s400/', $imgUrl);
			$imgUrl = str_replace('/s288/', '/s400/', $imgUrl);
		}
		//マークアップを返す
		ob_start();
		echo '
			<meta property="fb:admins" content="'.$this->admins.'" />
			<meta property="og:locale" content="ja_JP">
			<meta property="og:type" content="blog">
			<meta property="og:site_name" content="'.esc_attr(get_bloginfo('name')).'">
			<meta property="og:description" content="'.esc_attr($desc).'">
			<meta property="og:title" content="'.esc_attr($title).'">
			<meta property="og:url" content="'.esc_url($url).'">
			<meta property="og:image" content="'.esc_url($imgUrl).'">
		';
		return ob_get_clean();
	}
}
?>