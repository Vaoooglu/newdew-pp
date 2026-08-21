<?php if( get_field('gut_video_link') ):
    $img_url='https://i2.ytimg.com/vi/'.get_field('gut_video_img').'/0.jpg';
if(get_field('gut_video_date')){$date_publ=get_field('gut_video_date');} else {$date_publ=get_the_date('Y-m-d');}
    $dur_hour=='';
   if(get_field('gut_video_hour')){
       if(get_field('gut_video_hour')!=='0'){
           $dur_hour = '0'.get_field('gut_video_hour').'H';
       }
   }
    ?>
    <script type="application/ld+json">
        {"@context": "http://schema.org/","@type": "VideoObject","name": "<?=get_field('gut_video_tit');?>","description": "<? if(get_field('gut_video_desc')) {echo get_field('gut_video_desc');} else {echo get_the_title();} ?>","duration": "PT<?=$dur_hour;?><?=get_field('gut_video_min');?>M<?=get_field('gut_video_sec');?>S","thumbnailUrl": "https://i2.ytimg.com/vi/<?=get_field('gut_video_img');?>/0.jpg","embedUrl": "<?=get_field('gut_video_link');?>","uploadDate": "<?=$date_publ;?>"}
    </script>
<figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
    <div class="wp-block-embed__wrapper">
        <div class="container-lazyload preview-lazyload container-youtube">
            <a class="lazy-load-youtube preview-lazyload preview-youtube youtube_button_image_red" href="<?=get_field('gut_video_link');?>" data-video-title="<?=get_field('gut_video_tit');?>" title="Воспроизвести видео &quot;<?=get_field('gut_video_tit');?>&quot;" height="360px" width="640px" style="height: 360px; width: 640px; background-image: url(<?=$img_url;?>); background-color: rgb(0, 0, 0); background-position: center center; background-repeat: no-repeat;">
                <div aria-hidden="true" class="lazy-load-div" height="360px" width="640px" style="height: 360px; width: 640px;"></div>
                <div aria-hidden="true" class="lazy-load-info"><span class="titletext youtube"><?=get_field('gut_video_tit');?></span>
                </div>
            </a>
            <noscript>Video can’t be loaded because JavaScript is disabled: <a href="<?=get_field('gut_video_link');?>" title="<?=get_field('gut_video_tit');?>"><?=get_field('gut_video_tit');?> (<?=get_field('gut_video_link');?>)</a></noscript>
        </div>
        </div>
</figure>


<?php endif;?>

<? if( is_admin() ){?>
    <div style="display: flex;">
        <img src="<?=get_template_directory_uri();?>/images/block_formgraph.jpg" style="width: 100%;height: auto">
    </div>
<? }?>

